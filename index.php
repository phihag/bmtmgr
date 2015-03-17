<?php

$config = json_decode(file_get_contents('config.json'), true);
require_once('db.php');
$db = db_connect($config);

