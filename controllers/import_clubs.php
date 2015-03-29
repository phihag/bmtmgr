<?php

require_once dirname(__DIR__) . '/src/common.php';
if (! $config['allow_init']) {
	throw new Exception('Configuration directive allow_init not set!');
}

require_once dirname(__DIR__) . '/src/user.php';
$user = check_current_user($config);
$user->check('admin');
