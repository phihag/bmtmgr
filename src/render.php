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

function get_rendered($template_id, &$data) {
	$mustache = _get_engine();

	$data['csrf_field'] = '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(utils\csrf_token()) . '" />';
	$data['support_email_address'] = \bmtmgr\config\get('support_email_address');
	$data['root_path'] = \bmtmgr\utils\root_path();
	$data['icon_path'] = \bmtmgr\utils\root_path() . 'static/icons/';

	if (array_key_exists('user', $data)) {
		$data['is_admin'] = $data['user']->can('admin');
	}

	return $mustache->render($template_id, $data);
}

function get_rendered_full($template_id, &$data) {
	$mustache = _get_engine();
	$content = get_rendered($template_id, $data);

	if (\array_key_exists('sent_emails', $data)) {
		$data['sent_emails'] = \array_filter($data['sent_emails']);
	}

	$data['content'] = $content;

	return $mustache->render('scaffold', $data);
}

function render($template_id, $data) {
	echo get_rendered_full($template_id, $data);
}

function render_ajax($redir_path, $data) {
	$data['status'] = 'ok';
	//var_export($_SERVER);
	// TODO is this an AJAX request?
	/*header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
	*/
	header('HTTP/1.0 302 Temporary Redirect');
	header('Location: ' . \bmtmgr\utils\root_path() . $redir_path);
}

function render_ajax_error($msg) {
	$data = [
		'status' => 'error',
		'msg' => $msg,
	];
	// TODO is this an AJAX request?
	render('error', [
		'msg' => $msg,
	]);
}