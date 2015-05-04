<?php
namespace bmtmgr;

// All modules can assume access the following modules and the timezone being set correctly.

require_once \dirname(__DIR__) . '/src/utils.php';
require_once \dirname(__DIR__) . '/src/model.php';
require_once \dirname(__DIR__) . '/src/config.php';
\bmtmgr\config\Config::load(__DIR__ . '/testconfig.json');
require_once \dirname(__DIR__) . '/src/db.php';
require_once \dirname(__DIR__) . '/src/user.php';

// These should be autoloaded later
require_once \dirname(__DIR__) . '/models/discipline.php';
require_once \dirname(__DIR__) . '/models/entry.php';
require_once \dirname(__DIR__) . '/models/player.php';
require_once \dirname(__DIR__) . '/models/season.php';
require_once \dirname(__DIR__) . '/models/tournament.php';
require_once \dirname(__DIR__) . '/models/user.php';

\date_default_timezone_set(\bmtmgr\config\get('timezone'));
