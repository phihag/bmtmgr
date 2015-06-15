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

utils\require_post_params(['player', 'player_club']);
$player = $season->get_player_by_input($_POST['player']);
$player_club = User::find_by_input($_POST['player_club']);

$partner = null;
$partner_club = null;
if (isset($_POST['partner'])) {
	$partner = $season->get_player_by_input($_POST['partner']);
	$partner_club = User::find_by_input($_POST['partner_club']);
}

$email = isset($_POST['email']) ? $_POST['email'] : null;
$seeding = isset($_POST['seeding']) ? $_POST['seeding'] : null;

$entry->player_id = $player->id;
$entry->player_club_id = $player_club->id;
$entry->partner_id = $partner ? $partner->id : null;
$entry->partner_club_id = $partner ? $partner_club->id : null;
$entry->email = $email;
$entry->seeding = $seeding;
$entry->updated_time = \time();
$entry->save();

render_ajax('d/' . $discipline->id . '/', [
	'entry' => $entry,
]);
