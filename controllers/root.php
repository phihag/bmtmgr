<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/install.php';
require_once dirname(__DIR__) . '/src/common.php';

$user = user\get_current();

render('root', array(
	'tournaments' => Tournament::get_all_public(),
	'user' => $user,
));