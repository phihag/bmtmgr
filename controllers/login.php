<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/utils.php';
require_once dirname(__DIR__) . '/src/user.php';
require_once dirname(__DIR__) . '/src/email.php';

// No CSRF protection necessary; login is harmless
utils\check_get_params(array('t'));

$u = \bmtmgr\user\find_by_token('login_email_token', $_GET['t']);
if (! $u) {
	$title = 'Ungültiges oder abgelaufenes temporäres Passwort';
	render('login', [
		'title' => $title,
		'errors' => [[
			'title' => $title,
			'message' => 'Entschuldigung, aber das temporäre Passwort ist nicht mehr gültig. Bitte fordern Sie einen neues Passwort an.'
		]]
	]);
	exit();
}

assert($u);

\bmtmgr\user\create_session($u);
header('Location: ' . utils\root_path());
