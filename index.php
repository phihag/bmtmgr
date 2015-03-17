<?php
require_once('phpsrc/db.php');

$config = json_decode(file_get_contents('config.json'), true);
$db = db_connect($config);

