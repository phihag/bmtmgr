<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['id']);
$page_user = User::by_id($_GET['id']);

render('user', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Benutzer', 'path' => 'user/'],
		['name' => $page_user->name, 'path' => 'user/' . \urlencode($page_user->id) . '/'],
	],
	'page_user' => $page_user,
	'is_me' => ($u->id === $page_user->id),
]);