<?php
namespace bmtmgr;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

\date_default_timezone_set(config\get('timezone'));
