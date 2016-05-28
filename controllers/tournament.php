<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\require_get_params(['id']);
$tournament = Tournament::by_id($_GET['id']);
$season = $tournament->get_season();
$disciplines = $tournament->get_disciplines();


$u = user\get_current();

if ($u) {
	$u->require_perm('admin');
	$publications = $tournament->get_publications();

	render('tournament', [
		'user' => $u,
		'breadcrumbs' => [
			['name' => 'Ligen', 'path' => 'season/'],
			['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
			['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		],
		'season' => $season,
		'tournament' => $tournament,
		'disciplines' => $disciplines,
		'publications' => $publications,
	]);
} else  {
	$aentry_config = [
		'disciplines' => \array_map(function($d) {
			return [
				'id' => $d->id,
				'name' => $d->name,
				'dtype' => $d->dtype,
			];
		}, $disciplines),
	];
	$aentry_json = json_encode($aentry_config);


	render('tournament_anonymous', [
		'breadcrumbs' => [
			['name' => 'Ligen', 'path' => 'season/'],
			['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
			['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		],
		'season' => $season,
		'tournament' => $tournament,
		'disciplines' => $disciplines,
		'aentry_json' => $aentry_json,
		'add_scripts' => [['filename' => 'aentry.js']],
	]);
}
