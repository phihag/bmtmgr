<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['player_id']);
$player = Player::by_id($_GET['player_id']);

utils\require_post_params(['email', 'textid']);
$player->email = $_POST['email'];
$player->textid = $_POST['textid'];
$player->save();

render_ajax('player/' . $player->id . '/', [
	'player' => $player
]);
