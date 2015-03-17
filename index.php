<?php

$config = json_decode(file_get_contents('config.json'), true);

require('db.php');
$db = db_connect($config);
