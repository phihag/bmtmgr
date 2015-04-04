<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

if (isset($_GET['autocomplete']) && $_GET['autocomplete'] == 'json') {
	render_json(array_map(function($c) {
		return [
			'id' => $c->id,
			'name' => $c->name,
			'text' => '(' . $c->id . ') ' . $c->name,
		];
	}, user\User::get_all()));
	exit();
}

$u = user\check_current();
$u->require_perm('admin');

render('club_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Clubs', 'path' => 'club/']
	],
	'clubs' => user\User::get_all(),
]);