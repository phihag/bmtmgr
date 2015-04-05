<?php
namespace bmtmgr;

// All modules can assume access the following modules and the timezone being set correctly.

require_once \dirname(__DIR__) . '/src/utils.php';
require_once \dirname(__DIR__) . '/src/model.php';
require_once \dirname(__DIR__) . '/src/config.php';
\bmtmgr\config\Config::load(__DIR__ . '/testconfig.json');
require_once \dirname(__DIR__) . '/src/db.php';
require_once \dirname(__DIR__) . '/src/user.php';

\date_default_timezone_set(\bmtmgr\config\get('timezone'));
