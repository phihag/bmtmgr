<?php
require_once dirname(__DIR__) . '/src/common.php';

header('Content-Type: application/json');

$res = [];
foreach ($db->query('SELECT id,name FROM user') as $club) {
	array_push($res, array(
		'id' => $club['id'],
		'name' => $club['name'],
		'text' => '(' . $club['id'] . ') ' . $club['name']));
}
echo json_encode($res);