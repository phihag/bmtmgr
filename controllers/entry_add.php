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

$players = [];
$player_clubs = [];
for ($i = 0;$i < 100;$i++) {
	if (!empty($_POST['player' . $i])) {
		$players[] = $season->get_player_by_input($_POST['player' . $i]);
		if (!empty($_POST['player' . $i . '_club'])) {
			$player_clubs[] = $season->get_club_by_input($_POST['player' . $i . '_club']);
		}
	}
}


$entry_name = isset($_POST['entry_name']) ? $_POST['entry_name'] : null;
$email = isset($_POST['email']) ? $_POST['email'] : null;
$seeding = isset($_POST['seeding']) ? $_POST['seeding'] : null;
$memo = isset($_POST['memo']) ? $_POST['memo'] : null;

try {
	$discipline->check_entry($players);
} catch(utils\InvalidEntryException $iee) {
	render_ajax_error(
		'Meldung kann nicht angenommen werden: ' . $iee->getMessage()
	);
	exit();
}

$entry = Entry::create(
	$discipline, $entry_name, $players, $player_clubs, $email, $seeding, $memo);
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
