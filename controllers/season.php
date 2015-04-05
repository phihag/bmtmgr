<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/season.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$season = season\Season::by_id($_GET['id']);


render('season', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
	],
	'season' => $season,
]);