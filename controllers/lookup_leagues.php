<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();

$found = \preg_match('/id=(?P<season_internal_id>[a-zA-Z0-9-]+)/', $season->baseurl, $matches);
if (! $found) {
	throw new \Exception('Cannot interpret season base URL');
}
$season_internal_id = $matches['season_internal_id'];

$players = $tournament->get_all_players();
foreach ($players as $player) {
	if (! $player->is_official_id()) {
		continue;
	}

	$overview_url = 'http://turnier.de/find.aspx?a=8&oid=E3B0DC28-EDDE-426A-9DD9-600D26DCF680&q=' . \urlencode($player->textid) . '&id=2';
	$overview_page = \file_get_contents($overview_url);
	if (! \preg_match('/<a href="(?P<detail_path>[^"]*id=' . \preg_quote($season_internal_id) . '[^"]*)"/', $overview_page, $matches)) {
		throw new \Exception('Cannot find season information for ' . $player->name);
	}

	$detail_url = 'http://turnier.de/' . $matches['detail_path'];
	$detail_page = \file_get_contents($detail_url);

	if (! \preg_match('/player=(?P<player_internal_id>[0-9]+)/', $matches['detail_path'], $matches)) {
		throw new \Exception('Cannot find internal player ID for ' . $player->name);
	}
	$player_internal_id = $matches['player_internal_id'];

	\preg_match_all('#
		<td\s+align="right">(?P<date>[^<]+?)\s+.*?
		<a\s+href="./draw.aspx?[^"]*">[A-Z0-9-]+\s+(?P<age_group>[OU][0-9]+)(?:-[A-Za-z0-9]+)?-(?P<league>[A-Za-z0-9]+)
		.*?
		(?P<won><strong>)?<a\s+class="[^"]*"\s+href="player.aspx\?id=' . \preg_quote($season_internal_id) . '&player=' . \preg_quote($player_internal_id) . '".*?' .
		'<td><span\s+class="score">(?:<span>[0-9-]+</span>\s*)+</span></td>' .
		'#x',
		$detail_page, $lines, \PREG_SET_ORDER);

	$wins_in_league = [];
	$games_in_league = [];
	$days = [];
	foreach ($lines as $line) {
		$age_group = $line['age_group'];
		switch ($age_group) {
		case 'O19':
			$age_group = '';
			break;
		case 'U19':
			if ($line['league'] == 'Mini') {
				$age_group = 'U19-';
			} else {
				$age_group = 'J';
			}
			break;
		}
		$l = $age_group . $line['league'];

		if (! \array_key_exists($l, $games_in_league)) {
			$games_in_league[$l] = 0;
			$wins_in_league[$l] = 0;
		}
		$games_in_league[$l]++;
		if (\array_key_exists('won', $line)) {
			$wins_in_league[$l]++;
		}
		$days[$line['date']] = $l;
	}
	if (\count($games_in_league) > 0) {
		// For now, pick the league with the most games
		$league = \bmtmgr\utils\array_max_key($games_in_league);
		$player->league = $league;
		$player->winrate = $wins_in_league[$league] / $games_in_league[$league];
		$player->save();
	}
}

render_ajax('t/' . $tournament->id . '/', [
	'tournament' => $tournament,
]);
