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

// These should be autoloaded later
require_once \dirname(__DIR__) . '/models/discipline.php';
require_once \dirname(__DIR__) . '/models/entry.php';
require_once \dirname(__DIR__) . '/models/player.php';
require_once \dirname(__DIR__) . '/models/season.php';
require_once \dirname(__DIR__) . '/models/tournament.php';
require_once \dirname(__DIR__) . '/models/user.php';
require_once \dirname(__DIR__) . '/models/publication.php';


\date_default_timezone_set(config\get('timezone'));
