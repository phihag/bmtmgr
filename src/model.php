<?php
namespace bmtmgr;

class Model {
	private function table_name() {
		return \strtolower((new \ReflectionClass($this))->getShortName());
	}

	public function save() {
		$values = \bmtmgr\utils\array_filter_keys(
			\get_object_vars($this), function($k) {
				return $k != 'id' && !\bmtmgr\utils\startswith($k, '_');
			});
		$sql = 'UPDATE ' . $this->table_name() . ' SET ';
		$sql .= \implode(', ', \array_map(function($k) {
			return $k . '=?';
		}, array_keys($values)));
		$sql .= ' WHERE id=?;';

		$s = $GLOBALS['db']->prepare($sql);
		$s->execute(\array_merge(\array_values($values), [$this->id]));
	}
}