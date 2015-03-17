<?php

define('DB_NEWEST_VERSION', 1);

function db_init($config, $db) {
	if (! $config['allow_init']) {
		throw new Exception('Initialization code triggered, but disabled.');
	}
	$inits = json_decode(file_get_contents('db_init.json'), true);
	$db->beginTransaction();
	foreach ($inits as $sql) {
		$db->exec($sql);
	}
	$db->commit();
}

function db_connect($config) {
	$db = new PDO($config['db_dsn']);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Do we need to initialize?
	try {
		$vdata = $db->query('SELECT version FROM db_version');
	} catch (PDOException $e) {
		db_init($config, $db);
	}
	$version = -1;
	foreach ($vdata as $row) {
		$version = $row['version'];
	}
	if ($version < DB_NEWEST_VERSION) {
		db_init($config, $db);
	}

	return $db;
}