<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['tournament_id']);
utils\require_post_params(['name', 'dtype']);
$tournament = Tournament::by_id($_GET['tournament_id']);

if ($_POST['dtype'] == 'all') {
	$specs = [
		['name' => 'HE' . $_POST['name'], 'dtype' => 'MS'],
		['name' => 'DE' . $_POST['name'], 'dtype' => 'WS'],
		['name' => 'HD' . $_POST['name'], 'dtype' => 'MD'],
		['name' => 'DD' . $_POST['name'], 'dtype' => 'WD'],
		['name' => 'MX' . $_POST['name'], 'dtype' => 'MX'],
	];
} else {
	$specs = [
		['name' => $_POST['name'], 'dtype' => $_POST['dtype']]
	];
}
Model::beginTransaction();
foreach ($specs as $spec) {
	try {
		$discipline = Discipline::create($tournament, $spec['name'], $spec['dtype']);
		$discipline->save();
	} catch (utils\DuplicateEntryException $e) {
		render_ajax_error(
			sprintf('Disziplin "%s" existiert bereits!', $spec['name'])
		);
		exit();
	}
}
Model::commit();

render_ajax('d/' . $discipline->id . '/', [
	'tournament' => $tournament,
	'discipline' => $discipline,
]);
