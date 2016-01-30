<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['entry_id']);
$entry = Entry::by_id($_GET['entry_id']);
$discipline = $entry->get_discipline();
$tournament = $discipline->get_tournament();
$season = $tournament->get_season();

$players = [];
$clubs = [];
for ($i = 0;$i < 100;$i++) {
	if (!empty($_POST['player' . $i])) {
		$players[] = $season->get_player_by_input($_POST['player' . $i]);
		if (!empty($_POST['player' . $i . '_club'])) {
			$clubs[] = $season->get_club_by_input($_POST['player' . $i . '_club']);
		}
	}
}

// TODO check entry

$email = isset($_POST['email']) ? $_POST['email'] : null;
$seeding = isset($_POST['seeding']) ? $_POST['seeding'] : null;
$memo = isset($_POST['memo']) ? $_POST['memo'] : null;
$entry_name = isset($_POST['entry_name']) ? $_POST['entry_name'] : null;

$entry->entry_name = $entry_name;
$entry->email = $email;
$entry->seeding = $seeding;
$entry->memo = $memo;
$entry->updated_time = \time();
$entry->_players = $players;
$entry->_clubs = $clubs;
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
