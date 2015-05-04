<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';


$u = user\check_current();
$u->require_perm('admin');
utils\csrf_protect();
utils\require_post_params(['tournament_url']);

if (! \preg_match('#^(https?://.*?/)[a-z]+(\.aspx.*)$#', $_POST['tournament_url'], $m)) {
	render_ajax_error('Entschuldigung, die turnier-URL "' . $_POST['tournament_url'] . '" kann leider nicht bearbeitet werden.');
	exit();
}
$base_url = $m[1];
$clubs_url = $base_url . 'clubs' . $m[2];
$clubs_content = \file_get_contents($clubs_url);

if (! preg_match('#<div id="divTournamentHeader" class="header">\s*<div class="title"><h3>(.*)</h3>#', $clubs_content, $m)) {
	throw new \Exception('Cannot find season name');
}
$name = \html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'utf-8');

Model::beginTransaction();
$season = Season::fetch_optional('WHERE name=?', [$name]);
if (! $season) {
	$season = Season::create($name, false);
	$season->save();
}

$players = [];
if (!\preg_match_all(
	'#<td><a href="club\.aspx(?P<club_path>\?id=[^"]+)&club=(?P<club_num>[0-9]+)">(?P<name>[^<]+)</a></td><td class="right">(?P<id>[0-9-]+)</td>#',
	$clubs_content, $matches, PREG_SET_ORDER)) {
	throw new \Exception('Cannot find any club entries!');
}
foreach ($matches as $m) {
	$club = User::by_id_optional($m['id']);
	if (! $club) {
		$club = new User($m['id'], $m['name'], null, ['register']);
		$club->save();
	}

	$players_url = $base_url . 'clubplayers.aspx' . $m['club_path'] . '&cid=' . $m['club_num'];
	$players_page = \file_get_contents($players_url);

	$genders = ['Männer' => 'm', 'Frauen' => 'f'];
	if (! \preg_match_all('#<caption>\s*(?<gender_str>Männer|Frauen)\s*</caption><thead>(?P<table>.*?)</tbody>#s', $players_page, $player_table_m, \PREG_SET_ORDER)) {
		continue; // Some clubs don't have any players associated with them (because of merger etc.)
	}

	foreach ($player_table_m as $table_m) {
		$g = $genders[$table_m['gender_str']];
		$table = $table_m['table'];

		preg_match_all('#
			<td\s+id="playercell"><a\s+href="[^"]+">(?P<name>[^<]+)</a></td>
			<td\s+class="flagcell">(?:<img.*?/><span[^>]*>\[(?P<nationality>[A-Z]+)\]\s*</span>)?</td>
			<td>(?P<textid>[^>]+)</td>
			<td>(?P<birth_year>[0-9]{4})</td>#xs', $table, $matches, \PREG_SET_ORDER);
		foreach ($matches as $m) {
			if (Player::exists($season, $m['textid'])) {
				continue;
			}
			$p = new Player([
				'id' => null,
				'season_id' => $season->id,
				'club_id' => $club->id,
				'textid' => $m['textid'],
				'name' => $m['name'],
				'gender' => $g,
				'birth_year' => \intval($m['birth_year']),
				'nationality' => $m['nationality'],
				'email' => null,
				'phone' => null,
			], true);
			$p->save();
		}
	}
}
Model::commit();

render_ajax('season/' . urlencode($season->id) . '/', [
	'season' => $season,
]);

