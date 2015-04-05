<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$tournament = Tournament::by_id($_GET['id']);

if (isset($_POST['description'])) {
	$tournament->description = $_POST['description'];
}
$tournament->visible = isset($_POST['visible']);
$tournament->save();

render_ajax('t/' . $tournament->id . '/', [
	'tournament' => $tournament,
]);
