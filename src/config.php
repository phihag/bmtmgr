<?php
namespace bmtmgr\config;

class Config {
	private static $config;

	static function get($key, $default='__nodefault__') {
		if (isset(self::$config[$key])) {
			return self::$config[$key];
		}

		if ($default === '__nodefault__') {
			throw new \Exception('missing configuration key "' . $key . '"');
		}

		return $default;		
	}

	static function load() {
		self::$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);
	}
}
Config::load();

function get($key, $default='__nodefault__') {
	return Config::get($key, $default);
}