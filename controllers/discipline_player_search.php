<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id', 'gender', 'term']);
$player_rows = Discipline::suggest_player_rows_with_clubs_by_id(
	$_GET['discipline_id'], $_GET['term'], $_GET['gender']);

render_json([
	'players' => $player_rows,
]);
