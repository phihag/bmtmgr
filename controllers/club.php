<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$club = User::by_id($_GET['id']);

render('club', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Vereine', 'path' => 'club/'],
		['name' => $club->name, 'path' => 'club/' . \urlencode($club->id) . '/'],
	],
	'club' => $club,
]);