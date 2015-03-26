<?php

require_once __DIR__ . '/utils.php';
require dirname(__DIR__) . '/libs/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views/partials'),
));

function render($template_id, $data) {
	$mustache = $GLOBALS['mustache'];
	$data['csrf_field'] = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '" />';
	$content = $mustache->render($template_id, $data);
	$data['content'] = $content;
	$data['root_path'] = $GLOBALS['root_path'];

	echo $mustache->render('scaffold', $data);
}