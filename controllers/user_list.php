<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

render_json(array_map(function($u) {
	return [
		'id' => $u->id,
		'name' => $u->name,
		'text' => '(' . $u->id . ') ' . $u->name,
	];
}, User::get_all()));
