<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
\bmtmgr\user\delete_session();

header('Location: ' . utils\root_path());
