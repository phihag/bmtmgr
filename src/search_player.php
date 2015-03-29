<?php

TODO: actually use our config

$search_url = $config['tournament_server'] . '/find.aspx?a=2&id=2';
$data = array(
	'__EVENTTARGET'=> 'btnSearch',
	'tbxSearchQuery'=> $_GET['q'],
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ),
);
$context  = stream_context_create($options);
$webpage = file_get_contents($search_url, false, $context);

$matches = array();
preg_match('/(?s)<tbody>(.*)<\/tbody>/', $webpage, $matches);
$tbody = $matches[1];

preg_match_all(
	'/<td><a href="(?P<details_url>[^"]*)"(?:\s*[a-z-]+="[^"]*")*>(?P<lastname>[^,]+?),\s*(?P<firstname>.+?)<\/a><\/td><td>(?P<id>.+?)<\/td>/', $tbody, $matches, PREG_SET_ORDER);

$res = array_map(
	function($player_match) {
		return array(
			'firstname' => $player_match['firstname'],
			'lastname' => $player_match['lastname'],
			'id' => $player_match['id']);
	}, $matches);
echo json_encode($res);