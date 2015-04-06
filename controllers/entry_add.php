<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id']);
utils\require_post_params(['player']);
$discipline = Discipline::by_id($_GET['discipline_id']);
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();

$player = $season->get_player_by_input($_POST['player']);
$partner = null;
if (isset($_POST['partner'])) {
	$partner = $season->get_player_by_input($_POST['partner']);
}

try {
	$discipline->check_entry($player, $partner);
} catch(utils\InvalidEntryException $iee) {
	render_ajax_error(
		'Meldung kann nicht angenommen werden: ' . $iee->getMessage()
	);
	exit();
}

$entry = Entry::create($discipline, $player, $partner);
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
