<?php
namespace bmtmgr;

// All modules can assume access the following modules and the timezone being set correctly.

require_once __DIR__ . '/utils.php';
require_once __DIR__ . '/model.php';
require_once __DIR__ . '/config.php';
config\Config::load();
require_once __DIR__ . '/db.php';
Model::connect();
require_once __DIR__ . '/user.php';

\date_default_timezone_set(config\get('timezone'));
