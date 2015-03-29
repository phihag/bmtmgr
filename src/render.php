<?php
namespace bmtmgr;

require_once __DIR__ . '/utils.php';
require dirname(__DIR__) . '/libs/mustache.php/src/Mustache/Autoloader.php';
\Mustache_Autoloader::register();

function _get_engine() {
	static $res = null;
	if ($res === null) {
		$res = new \Mustache_Engine(array(
			'loader' => new \Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views'),
		    'partials_loader' => new \Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views/partials'),
		));
	}
	return $res;
}

function calc_view($template_id, $data) {
	$mustache = _get_engine();

	$data['csrf_field'] = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(utils\csrf_token()) . '" />';
	return $mustache->render($template_id, $data);
}

function calc_full_view($template_id, $data) {
	$mustache = _get_engine();
	$content = calc_view($template_id, $data);

	$data['content'] = $content;
	$data['root_path'] = \bmtmgr\utils\root_path();

	return $mustache->render('scaffold', $data);
}

function render($template_id, $data) {
	echo calc_full_view($template_id, $data);
}