<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['season_id']);
$season = Season::by_id($_GET['season_id']);
$clubs = User::get_all('ORDER BY name ASC');

render('club_list', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . urlencode($season->id) . '/'],
		['name' => 'Alle Vereine', 'path' => 'season/' . urlencode($season->id) . '/club/'],
	],
	'season' => $season,
	'clubs' => $clubs,
]);