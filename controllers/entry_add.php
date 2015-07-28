<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id']);
$discipline = Discipline::by_id($_GET['discipline_id']);
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();

$player = null;
$player_club = null;
$partner = null;
$partner_club = null;
if (!empty($_POST['player'])) {
	$player = $season->get_player_by_input($_POST['player']);
	if (!empty($_POST['player_club'])) {
		$player_club = $season->get_club_by_input($_POST['player_club']);
	}
}
if (!empty($_POST['partner'])) {
	$partner = $season->get_player_by_input($_POST['partner']);
	if (!empty($_POST['partner_club'])) {
		$partner_club = $season->get_club_by_input($_POST['partner_club']);
	}
}

$email = isset($_POST['email']) ? $_POST['email'] : null;
$seeding = isset($_POST['seeding']) ? $_POST['seeding'] : null;
$memo = isset($_POST['memo']) ? $_POST['memo'] : null;

try {
	$discipline->check_entry($player, $partner);
} catch(utils\InvalidEntryException $iee) {
	render_ajax_error(
		'Meldung kann nicht angenommen werden: ' . $iee->getMessage()
	);
	exit();
}

$entry = Entry::create(
	$discipline, $player, $player_club, $partner, $partner_club, $email, $seeding, $memo);
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
