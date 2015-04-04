<?php
namespace bmtmgr;

class Model {
	protected static function from_row($row) {
		return new static($row);
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
		$sql = "UPDATE $table SET ";
		$sql .= \implode(', ', \array_map(function($k) {
			return $table . '.' . $k . '=?';
		}, array_keys($values)));
		$sql .= " WHERE $table.id=?;";

		$s = $GLOBALS['db']->prepare($sql);
		$s->execute(\array_merge(\array_values($values), [$this->id]));
	}

	public static function get_all($add_sql='', $add_params=[], $add_tables=[]) {
		$all_keys = \array_filter(
			\array_keys(\get_class_vars(get_called_class())),
			function($k) {
				return !\bmtmgr\utils\startswith($k, '_');
			});
		$tname = static::table_name();
		$sql = 'SELECT ';
		$sql .= \implode(', ', \array_map(function($k) use ($tname) {
			return "$tname.$k AS $k";
		}, $all_keys));
		$sql .= ' FROM ' . implode(', ', array_merge([$tname], $add_tables)). ' ';
		$sql .= $add_sql;
		$sql .= ';';

		$s = $GLOBALS['db']->prepare($sql);
		$s->execute($add_params);

		// php cannot just use static::from_row
		$from_row = get_called_class() . '::from_row';
		return \array_map($from_row, $s->fetchAll());
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
}