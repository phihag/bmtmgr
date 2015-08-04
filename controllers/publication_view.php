<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/sftp.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['publication_id']);
$publication = Publication::by_id($_GET['publication_id']);
$tournament = $publication->get_tournament();
$disciplines = $tournament->get_disciplines();
$season = $tournament->get_season();
    
render('publication_' . $publication->ptype, [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => 'Veröffentlichung ' . $publication->id, 'path' => 'publication/' . $publication->id . '/'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
	'publication' => $publication,
]);
