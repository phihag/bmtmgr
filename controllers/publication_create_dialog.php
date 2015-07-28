<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/sftp.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id', 'type']);
$publication_type = $_GET['type'];
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();
    
switch ($publication_type) {
case 'sftp':
	render('publication_create_dialog_sftp', [
		'user' => $u,
		'breadcrumbs' => [
			['name' => 'Ligen', 'path' => 'season/'],
			['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
			['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
			['name' => 'Veröffentlichung erstellen ...', 'path' => 't/' . $tournament->id . '/publication_create_dialog?type=' . urlencode($publication_type)],
		],
		'season' => $season,
		'tournament' => $tournament,
		'publication_type' => $publication_type,
	]);
	break;
default:
	throw new \Exception('Invalid publication type');
}