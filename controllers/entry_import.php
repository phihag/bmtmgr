<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';
require_once dirname(__DIR__) . '/src/import.php';


utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
utils\require_post_params(['text']);
$tournament = Tournament::by_id($_GET['tournament_id']);
$season = $tournament->get_season();

$text = \trim($_POST['text']);
$autocreate = \array_key_exists('autocreate', $_POST);


Model::beginTransaction();
list($new_entries, $unmatched_lines) = \bmtmgr\import\import_text($tournament, $text, $autocreate);
Model::commit();

render('entry_import_result', [
	'user' => $u,
	'breadcrumbs' => [
		['name' => 'Ligen', 'path' => 'season/'],
		['name' => $season->name, 'path' => 'season/' . $season->id . '/'],
		['name' => $tournament->name, 'path' => 't/' . $tournament->id . '/'],
		['name' => 'Importieren', 'path' => 't/' . $tournament->id . '/dialog_import'],
	],
	'season' => $season,
	'tournament' => $tournament,
	'new_entries' => $new_entries,
	'unmatched_lines' => $unmatched_lines,
	'autocreate' => $autocreate,
]);