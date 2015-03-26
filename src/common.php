<?php
require_once __DIR__ . '/db.php';

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

if (isset($config['root_path'])) {
	$root_path = $config['root_path'];
} elseif (($p = strpos($_SERVER['PHP_SELF'], '/bmtmgr/')) !== false) {
	$root_path = substr($_SERVER['PHP_SELF'], 0, $p + strlen('/bmtmgr/'));
} else {
	$root_path = '';
}

$db = db_connect($config);
