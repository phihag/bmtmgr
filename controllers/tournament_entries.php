<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$tournament = Tournament::by_id($_GET['id']);
$season = $tournament->get_season();
$disciplines = $tournament->get_disciplines();
foreach ($disciplines as $d) {
	$d->entries = $d->get_entry_rows();
	$d->entries_present = \count($d->entries) > 0;
	$d->_is_new = 'modified';
}

$data = [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/entries'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'disciplines' => $disciplines,
	'now_date' => \date('Y-m-d'),
];

$format = isset($_GET['format']) ? $_GET['format'] : 'html';
switch ($format) {
case 'standalone-html':
	echo get_rendered('tournament_entries_standalone', $data);
	break;
case 'text':
	echo get_rendered('tournament_entries_standalone_text', $data);
	break;
case 'html':
	render('tournament_entries', $data);
	break;
default:
	throw new \Exception('Invalid format code');
}

