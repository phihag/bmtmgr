<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/sftp.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
utils\require_post_params(['type']);
$publication_type = $_POST['type'];
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();
    
switch ($publication_type) {
case 'sftp':
	utils\require_post_params(['server', 'port', 'username', 'path']);
	$publication = sftp\SFTPPublication::sftp_create(
		$tournament,
		$_POST['server'], \intval($_POST['port']), $_POST['path'], $_POST['username']);
	$publication->save();
	render_ajax('publication/' . $publication->id . '/', [
		'publication' => $publication,
	]);
	break;
default:
	throw new \Exception('Invalid publication type');
}