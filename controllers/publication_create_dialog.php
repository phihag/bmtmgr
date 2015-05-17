<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id', 'type']);
$publication_type = $_GET['type'];
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();
    
switch ($publication_type) {
case 'sftp':
	$config = array(
	    'digest_alg' => 'sha512',
	    'private_key_bits' => 4096,
	    'private_key_type' => OPENSSL_KEYTYPE_RSA,
	);
	$key = openssl_pkey_new($config);

	render('publication_create_dialog_sftp', [
		'user' => $u,
		'breadcrumbs' => [
			['name' => 'Ligen', 'path' => 'season/'],
			['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
			['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
			['name' => 'Veröffentlichung erstellen ...', 'path' => 't/' . $tournament->id . '/publication_create_dialog'],
		],
		'season' => $season,
		'tournament' => $tournament,
		'publication_type' => $publication_type,
		'public_key' => $public_key_line,
	]);
	break;
default:
	throw new \Exception('Invalid publication type');
}