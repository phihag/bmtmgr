<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['club_id', 'season_id']);
utils\require_post_params(['firstname', 'lastname', 'gender']);

$season = Season::by_id($_GET['season_id']);
$club = Club::by_id($_GET['club_id']);

$name = \sprintf('%s, %s', $_POST['lastname'], $_POST['firstname']);
$textid = \str_replace(' ', '_', $club->name . '-' . $_POST['firstname'] . ' ' . $_POST['lastname']);

try {
	$player = Player::create($season->id, $club->id, $textid, $name, $_POST['gender']);
	$player->save();
} catch (utils\DuplicateEntryException $e) {
	render_ajax_error(
		sprintf('Ein Spieler mit der Id "%s" existiert bereits', $textid)
	);
	exit();
}

render_ajax('season/' . $season->id . '/club/' . $club->id . '/', [
	'player' => $player,
]);
