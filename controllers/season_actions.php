<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/season.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id', 'action']);
$season = \bmtmgr\season\by_id($_GET['id']);

switch ($_GET['action']) {
case 'hide':
case 'show':
	$season->visible = \boolval($_GET['action'] == 'show');
	var_export($season);
	$season->save();
	render_ajax('season/' . $season->id . '/', [
		'season' => $season
	]);
	break;
default:
	header('HTTP/1.1 404 Not Found');
	render('error', [
		'title' => 'Unbekannte Aktion',
		'msg' => 'Entschuldigung, wir haben eine Adresse falsch eingetragen. Die Aktion "' . $_GET['action'] . '" ist nicht implementiert.',
	]);
}
