<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_post_params(['name', 'season_id']);
$season = Season::by_id($_POST['season_id']);

try {
	$tournament = Tournament::create($season, $_POST['name']);
	$tournament->save();
} catch (utils\DuplicateEntryException $e) {
	render_ajax_error(
		'Ein Turnier mit dem Namen "' . $_POST['name'] . '" existiert bereits'
	);
	exit();
}

render_ajax('t/' . $tournament->id . '/', [
	'season' => $season,
	'tournament' => $tournament,
]);
