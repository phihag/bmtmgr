<?php
namespace bmtmgr\install;

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/config.php';

define('LIB_ROOT', dirname(__DIR__) . '/libs/');

function get_libs() {
	return [
		[
			'url' => 'http://code.jquery.com/jquery-1.11.2.min.js',
			'symlink' => 'jquery.min.js',
		],
		['url' => 'http://cdn.craig.is/js/mousetrap/mousetrap.min.js'],
		[
			'url' => 'http://jqueryui.com/resources/download/jquery-ui-1.11.4.zip',
			'symlink' => 'jquery-ui'],
		['url' => 'https://github.com/bobthecow/mustache.php.git'],
		['url' => 'git://github.com/propelorm/Propel2']
	];
}

if (\bmtmgr\config\get('allow_install', false)) {
	if (isset($argv)) {
		if ((count($argv) == 1) || $argv[1] == 'install') {
			install_libs();
		} elseif ($argv[1] == 'clean') {
			clean();
		} else {
			die('Usage: ' . $argv[0] . ' install|clean' . "\n");
		}
	} else {
		install_libs();
	}
}

function rm_rf($fn) {
	if (! \bmtmgr\utils\startswith($fn, LIB_ROOT)) {
		throw new \Exception('Invalid rm of ' . $fn);
	}
	if (\strpos('..', $fn) !== false) {
		throw new \Exception('Invalid rm of ' . $fn);
	}
	if (\is_file($fn)) {
		\unlink($fn);
	} elseif (\is_dir($fn)) {
		foreach (scandir($fn) as $c) {
			if ($c == '.' || $c == '..') {
				continue;
			}
			rm_rf($fn . '/' . $c);
		}
		\rmdir($fn);
	}
}


function detect_props(&$lib) {
	$url = $lib['url'];
	if (\bmtmgr\utils\startswith($url, 'git:')) {
		$lib['type'] = 'git';
		$lib['detected_name'] = \bmtmgr\utils\url_basename($url);
	} elseif (\bmtmgr\utils\endswith($url, '.git')) {
		$lib['type'] = 'git';
		$lib['detected_name'] = \bmtmgr\utils\strip_ext(\bmtmgr\utils\url_basename($url));
	} elseif (\bmtmgr\utils\endswith($url, '.zip')) {
		$lib['type'] = 'zip';
		$lib['detected_name'] = \bmtmgr\utils\strip_ext(\bmtmgr\utils\url_basename($url));
	} else {
		$lib['type'] = 'file';
		$lib['detected_name'] = \bmtmgr\utils\url_basename($url);
	}
	if (!array_key_exists('name', $lib)) {
		$lib['name'] = $lib['detected_name'];
	}
	$lib['fn'] = LIB_ROOT . $lib['name'];
}

function run($cmd, $package) {
	\exec($cmd . ' 2>&1', $output, $return_var);
	if ($return_var != 0) {
		\header('HTTP/1.1 500 Internal Server Error');
		\header('Content-Type: text/plain; charset=utf-8');
		echo 'Error when installing ' . $name . ":\n";
		echo \implode("\n", $output);
		exit();
	}
}

function download($url, $fn) {
	\file_put_contents($fn . '.part', \fopen($url, 'r'));
	\rename($fn . '.part', $fn);
}

function install_libs() {
	$libs = get_libs();
	$lock_fn = LIB_ROOT . 'install.lock';
	$lock_f = \fopen($lock_fn, 'w');
	if (! \flock($lock_f, LOCK_EX | LOCK_NB)) {
		\header('HTTP/1.1 500 Internal Server Error');
		\header('Content-Type: text/plain; charset=utf-8');
		echo 'Installation already running.' . "\n";
		exit();
	}

	foreach ($libs as $lib) {
		detect_props($lib);
		$url = $lib['url'];
		$fn = $lib['fn'];
		if (\file_exists($fn)) {
			continue;
		}

		switch ($lib['type']) {
		case 'git':
			run('giit clone -q ' . \escapeshellarg($url) . ' ' . \escapeshellarg($fn), \basename($fn));
			break;
		case 'file':
			download($url, $fn);
			break;
		case 'zip':
			download($url, $fn . '.zip');
			run('unzip ' . \escapeshellarg($fn . '.zip') . ' -d ' . \escapeshellarg(LIB_ROOT), \basename($fn));
			break;
		}

		if (\array_key_exists('symlink', $lib)) {
			\symlink(\basename($fn), LIB_ROOT . $lib['symlink']);
		}
	}

	\flock($lock_f, LOCK_UN);
	\fclose($lock_f);
}

function clean() {
	$lock_fn = LIB_ROOT . 'install.lock';
	$lock_f = \fopen($lock_fn, 'w');
	if (! \flock($lock_f, LOCK_EX | LOCK_NB)) {
		echo 'Waiting for currently running installation to finish ...';
		\flock($lock_f, LOCK_EX);
		echo "\n";
	}

	foreach (get_libs() as $lib) {
		detect_props($lib);	
		if (\array_key_exists('symlink', $lib)) {
			@\unlink(LIB_ROOT . $lib['symlink']);
		}
		rm_rf($lib['fn']);
		@\unlink($lib['fn'] . '.part');
		if ($lib['type'] == 'zip') {
			@\unlink($lib['fn'] . '.zip');
			@\unlink($lib['fn'] . '.zip.part');
		}
	}

	\flock($lock_f, LOCK_UN);
	\fclose($lock_f);
	\unlink($lock_fn);
}