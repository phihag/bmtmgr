<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\require_get_params(['tournament_id']);
$tournament = Tournament::by_id($_GET['tournament_id']);

die('TODO: fetch entries and render the table');
