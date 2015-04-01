<?php
namespace bmtmgr\db;

require_once __DIR__ . '/config.php';

define('DB_NEWEST_VERSION', 9);

function _init($db) {
	if (! \bmtmgr\config\get('allow_init', false)) {
		throw new \Exception('Initialization code triggered, but disabled.');
	}
	$inits = explode(';', file_get_contents(dirname(__DIR__) . '/db_init.sql'));
	if (!$inits) {
		throw new \Exception("Invalid init JSON");
	}

	$db->beginTransaction();
	foreach ($inits as $sql) {
		try {
			$db->exec($sql);
		} catch (\PDOException $e) {
			echo 'Database initialization failed (query: <code>' . htmlspecialchars($sql) . '</code>)<br />'. "\n";
			throw $e;
		}
	}
	$db->commit();
}

function connect() {
	$dsn = \str_replace('$ROOTDIR', dirname(__DIR__), \bmtmgr\config\get('db_dsn'));
	$db = new \PDO($dsn);

	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

	// Do we need to initialize?
	if (\bmtmgr\config\get('allow_init', false)) {
		try {
			$vdata = $db->query('SELECT version FROM db_version');
		} catch (\PDOException $e) {
			_init($db);
			return $db;
		}
		$version = -1;
		foreach ($vdata as $row) {
			$version = $row['version'];
		}
		if ($version < DB_NEWEST_VERSION) {
			_init($db);
		}
	}

	return $db;
}

$GLOBALS['db'] = connect();
