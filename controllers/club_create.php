<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_post_params(['name']);
$textid = \preg_replace('/[^a-z]+/', '', strtolower($_POST['name']));
assert(\preg_match('/^[a-z]+$/', $textid));
try {
	$club = Club::create($textid, $_POST['name'], null);
	$club->save();
} catch (utils\DuplicateEntryException $e) {
	render_ajax_error(
		'Der Verein "' . $_POST['name'] . '" existiert bereits'
	);
	exit();
}

render_ajax('club/' . $club->id . '/', [
	'club' => $club
]);
