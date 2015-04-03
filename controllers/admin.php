<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/user.php';

$u = user\check_current();
$u->require_perm('admin');

render('admin', [
	'seasons' => seasons\get_all()
]);