<?php
namespace bmtmgr\db;

require_once __DIR__ . '/config.php';

function _init($db) {
	if (! \bmtmgr\config\get('allow_init', false)) {
		throw new \Exception('Initialization code triggered, but disabled.');
	}
	$inits = \explode(';', \file_get_contents(\dirname(__DIR__) . '/db_init.sql'));
	if (!$inits) {
		throw new \Exception("Invalid init JSON");
	}

	$db->beginTransaction();
	foreach ($inits as $sql) {
		$sql = \trim($sql);
		if (! $sql) {
			continue;
		}
		try {
			$db->exec($sql);
		} catch (\PDOException $e) {
			throw new \Exception('Database initialization command "' . \trim($sql) . '" failed (' . $e . ')');
		}
	}
	$db->commit();
}

function connect() {
	$dsn = \str_replace('$ROOTDIR', \dirname(__DIR__), \bmtmgr\config\get('db_dsn'));
	$db = new \PDO($dsn);

	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

	// Do we need to initialize?
	if (\bmtmgr\config\get('allow_init', false)) {
		if (\bmtmgr\config\get('test_force_init', false)) {
			_init($db);
			return $db;
		}

		$init_sql = \file_get_contents(dirname(__DIR__) . '/db_init.sql');
		if (! \preg_match('/INSERT INTO db_version.*VALUES\s*\(([0-9]+)\)/', $init_sql, $matches)) {
			throw new \Exception('Cannot detect version number');
		}
		$newest_version = \intval($matches[1]);

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
		if ($version < $newest_version) {
			_init($db);
		}
	}

	return $db;
}
