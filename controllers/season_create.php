<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/season.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_post_params(['name']);
try {
	$season = new \bmtmgr\season\Season(null, $_POST['name'], false);
	$season->save();
} catch (utils\DuplicateEntryException $e) {
	render_ajax_error(
		'Die Liga "' . $_POST['name'] . '" existiert bereits'
	);
	exit();
}

render_ajax('season/' . $season->id . '/', [
	'season' => $season
]);
