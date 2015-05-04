<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/utils.php';
require_once dirname(__DIR__) . '/src/email.php';

utils\csrf_protect();
utils\require_post_params(array('user'));

$u = User::find_by_input($_POST['user']);
if (!$u) {
	header('HTTP/1.1 404 Not Found');
	render('error', array(
		'title' => 'Benutzer nicht gefunden',
		'msg'=> 'Benutzer "' . $_POST['user'] . '" konnte nicht gefunden werden.')
	);
	exit();
}

$s = $GLOBALS['db']->prepare('
	INSERT INTO login_email_token (token, user_id, request_time, expiry_time, metadata_json) VALUES(?, ?, ?, ?, ?)');

$ip = $_SERVER['REMOTE_ADDR'];
$metadata = array(
	'ip' => $ip,
	'ua' => $_SERVER['HTTP_USER_AGENT']
);

$token = utils\gen_token();
$request_time = time();
$expire_time = $request_time + config\get('email_token_timeout', 24 * 60 * 60);

$s->execute(array(
	$token,
	$u->id,
	$request_time,
	$expire_time,
	json_encode($metadata)
));

$login_url = \bmtmgr\utils\absolute_url() . 'login?t=' . $token;

$m = \bmtmgr\email\send($u->email, 'mails/token_request', array(
	'name' => $u->name,
	'email' => $u->email,
	'token' => $token,
	'until' => $request_time,
	'login_url' => $login_url,
	'ip' => $ip
));

render('token_requested', array(
	'sent_emails' => array($m),
	'name' => $u->name,
	'email' => $u->email,
	'until' => $request_time,
	'token_length' => strlen($token),
	'ip' => $ip
));

