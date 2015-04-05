<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

render('season_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/']
	],
	'seasons' => Season::get_all(),
]);