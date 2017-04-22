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

	public static function load($fn=null) {
		if ($fn === null) {
			$fn = __DIR__ . '/../config.json';
		}
		self::$config = \json_decode(\file_get_contents($fn), true);
	}
}

function get($key, $default='__nodefault__') {
	return Config::get($key, $default);
}