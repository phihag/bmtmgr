<?php
namespace bmtmgr;

class Model {
	public $_is_new = true;

	protected static function from_row($row, $_is_new=false) {
		return new static($row, $_is_new);
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

	protected static function all_fields_str() {
		$all_keys = \array_filter(
			\array_keys(\get_class_vars(\get_called_class())),
			function($k) {
				return !\bmtmgr\utils\startswith($k, '_');
			});
		$tname = static::table_name();

		return \implode(', ', \array_map(function($k) use ($tname) {
			return "$tname.$k AS $k";
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

		$s = $GLOBALS['db']->prepare($sql);
		$s->execute($add_params);

		if ($creation_callback === null) {
			// php cannot just use static::from_row
			$creation_callback = get_called_class() . '::from_row';
		}

		return \array_map($creation_callback, $s->fetchAll());
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
