<?php
namespace bmtmgr;

class Model {
	public $_is_new = true;

	public static function from_row($row, $_is_new=false) {
		return new static($row, $_is_new);
	}

	protected static function from_prefixed_row($row, $prefix) {
		$plain_row = [];
		foreach ($row as $k=>$v) {
			if (\bmtmgr\utils\startswith($k, $prefix)) {
				$plain_k = \substr($k, \strlen($prefix));
				$plain_row[$plain_k] = $v;
			}
		}

		return static::from_row($plain_row);
	}

	protected static function table_name() {
		return \strtolower((new \ReflectionClass(get_called_class()))->getShortName());
	}

	public function save() {
		$values = \bmtmgr\utils\array_filter_keys(
			\get_object_vars($this), function($k) {
				return $k != 'id' && !\bmtmgr\utils\startswith($k, '_');
			});
		$table = static::table_name();

		if ($this->_is_new === true) {
			if ($this->id) {
				$values['id'] = $this->id;
			}

			$sql = "INSERT INTO $table (";
			$sql .= \implode(', ', array_keys($values));
			$sql .= ') VALUES (';
			$sql .= \implode(', ', array_fill(0, count($values), '?'));
			$sql .= ');';

			$s = $GLOBALS['db']->prepare($sql);
			try {
				$s->execute(\array_values($values));
			} catch (\PDOException $pe) {
				if ($pe->getCode() == '23000') {
					throw new \bmtmgr\utils\DuplicateEntryException($pe->getMessage());
				} else {
					throw $pe;
				}
			}

			if (!$this->id) {
				$this->id = $GLOBALS['db']->lastInsertId();
			}
			$this->_is_new = false;
		} elseif ($this->_is_new === false) {
			$sql = "UPDATE $table SET ";
			$sql .= \implode(', ', \array_map(function($k) {
			       return $k . '=?';
			}, array_keys($values)));
			$sql .= " WHERE id=?;";

			$s = $GLOBALS['db']->prepare($sql);
			$s->execute(\array_merge(\array_values($values), [$this->id]));
		} else {
			throw new \Exception('Internal programming error: Trying to save an ephemeral object ' . $this);
		}
	}

	public static function all_fields_str($prefixed=false) {
		$all_keys = \array_filter(
			\array_keys(\get_class_vars(\get_called_class())),
			function($k) {
				return !\bmtmgr\utils\startswith($k, '_');
			});
		$tname = static::table_name();

		return \implode(', ', \array_map(function($k) use ($tname, $prefixed) {
			return "$tname.$k" . ($prefixed ? " AS ${tname}_$k" : " AS $k");
		}, $all_keys));
	}

	public static function get_all($add_sql='', $add_params=[], $add_tables=[], $add_fields='', $creation_callback=null) {
		$sql = 'SELECT ';
		$sql .= static::all_fields_str();
		if ($add_fields) {
			$sql .= ',' . $add_fields;
		}
		$sql .= ' FROM ' . implode(', ', array_merge([static::table_name()], $add_tables)). ' ';
		$sql .= $add_sql;
		$sql .= ';';

		$rows = self::sql($sql, $add_params);

		if ($creation_callback === null) {
			// php cannot just use static::from_row
			$creation_callback = get_called_class() . '::from_row';
		}

		return \array_map($creation_callback, $rows);
	}

	public static function sql($sql, $params) {
		$s = static::prepare($sql);
		$s->execute($params);
		return $s->fetchAll();
	}

	public static function prepare($sql) {
		return $GLOBALS['db']->prepare($sql);
	}

	public static function get_dict($add_sql='', $add_params=[], $add_tables=[], $add_fields='', $creation_callback=null) {
		$data = static::get_all($add_sql, $add_params, $add_tables, $add_fields, $creation_callback);
		$res = [];
		foreach ($data as $d) {
			$res[$d->id] = $d;
		}
		return $res;
	}

	public static function fetch_optional($add_sql='', $add_params=[], $add_tables=[]) {
		$res = static::get_all($add_sql, $add_params, $add_tables);
		if (\count($res) != 1) {
			return null;
		}
		return $res[0];
	}

	public static function fetch_one($add_sql='', $add_params=[], $add_tables=[]) {
		$res = static::get_all($add_sql, $add_params, $add_tables);
		if (\count($res) != 1) {
			throw new \Exception('Expected exactly one item, got ' . \count($res));
		}
		return $res[0];
	}

	public static function by_id($id) {
		return static::fetch_one('WHERE ' . static::table_name() . '.id=?', [$id]);
	}

	public static function by_id_optional($id) {
		return static::fetch_optional('WHERE ' . static::table_name() . '.id=?', [$id]);
	}

	public static function connect() {
		$GLOBALS['db'] = \bmtmgr\db\connect();
	}

	public static function beginTransaction() {
		$GLOBALS['db']->beginTransaction();
	}

	public static function commit() {
		$GLOBALS['db']->commit();
	}

	public static function rollBack() {
		$GLOBALS['db']->rollBack();
	}

	public function delete() {
		$s = $GLOBALS['db']->prepare('DELETE FROM ' . static::table_name() . ' WHERE id=:id');
		$s->execute([':id' => $this->id]);
	}

	protected static function _fetch_all_rows($sql, $params) {
		$s = $GLOBALS['db']->prepare($sql);
		$s->execute($params);
		return $s->fetchAll();
	}
}
