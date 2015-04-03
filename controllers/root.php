<?php
namespace bmtmgr;

require_once dirname(__DIR__) . '/src/install.php';
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/user.php';
require_once dirname(__DIR__) . '/src/tournament.php';

$user = user\check_current();

render('tournament_list', array(
	//'tournaments' => bmtmgr\tournament\all_current_season(),
	'user' => $user
));