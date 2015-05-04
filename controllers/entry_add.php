<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id']);
utils\require_post_params(['player', 'player_club']);
$discipline = Discipline::by_id($_GET['discipline_id']);
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();

$player = $season->get_player_by_input($_POST['player']);
$player_club = User::find_by_input($_POST['player_club']);
$partner = null;
$partner_club = null;
if (isset($_POST['partner'])) {
	$partner = $season->get_player_by_input($_POST['partner']);
	$partner_club = User::find_by_input($_POST['partner_club']);
}
$email = isset($_POST['email']) ? $_POST['email'] : null;

try {
	$discipline->check_entry($player, $partner);
} catch(utils\InvalidEntryException $iee) {
	render_ajax_error(
		'Meldung kann nicht angenommen werden: ' . $iee->getMessage()
	);
	exit();
}

$entry = Entry::create(
	$discipline, $player, $player_club, $partner, $partner_club, $email);
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
