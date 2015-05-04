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
	$d->entries = $d->get_entries_rows_with_verbose_players();
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

// TODO actually offer it as a download (then with other styles)
if (isset($_GET['standalone'])) {
	echo get_rendered('tournament_entries_standalone', $data);
} else {
	render('tournament_entries', $data);
}

