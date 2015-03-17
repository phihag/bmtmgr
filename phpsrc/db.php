<?php

define('DB_NEWEST_VERSION', 2);

function db_init($config, $db) {
	if (! $config['allow_init']) {
		throw new Exception('Initialization code triggered, but disabled.');
	}
	$inits = json_decode(file_get_contents('db_init.json'), true);
	if (!$inits) {
		throw new Exception("Invalid init JSON");
	}

	$db->beginTransaction();
	foreach ($inits as $sql) {
		try {
			$db->exec($sql);
		} catch (PDOException $e) {
			echo 'Database initialization failed (query: <code>' . htmlspecialchars($sql) . '</code>)<br />'. "\n";
			throw $e;
		}
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
		return $db;
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