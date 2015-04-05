<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

render('admin', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Administration', 'path' => 'admin/']
	],
	'seasons' => Season::get_all('ORDER BY name DESC'),
	'clubs' => User::get_all(),
]);