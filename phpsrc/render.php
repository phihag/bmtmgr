<?php

require 'libs/mustache.php/src/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	'loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views'),
    'partials_loader' => new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../views/partials'),
));

function render($template_id, $data) {
	$mustache = $GLOBALS['mustache'];
	$content = $mustache->render($template_id, $data);
	$data['content'] = $content;
	echo $mustache->render('scaffold', $data);
}