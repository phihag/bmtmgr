<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
utils\require_post_params(['name', 'dtype']);
$tournament = Tournament::by_id($_GET['tournament_id']);

try {
	$discipline = Discipline::create($tournament, $_POST['name'], $_POST['dtype']);
	$discipline->save();
} catch (utils\DuplicateEntryException $e) {
	render_ajax_error(
		'Disziplin "' . $_POST['name'] . '" existiert bereits!'
	);
	exit();
}

render_ajax('d/' . $discipline->id . '/', [
	'tournament' => $tournament,
	'discipline' => $discipline,
]);
