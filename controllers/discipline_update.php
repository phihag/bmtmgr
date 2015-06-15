<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['discipline_id']);
utils\require_post_params(['note', 'capacity']);
$discipline = Discipline::by_id($_GET['discipline_id']);

$discipline->capacity = ($_POST['capacity'] !== '') ? \intval($_POST['capacity']) : null;
$discipline->note = $_POST['note'];
$discipline->save();

render_ajax('d/' . $discipline->id . '/', [
	'discipline' => $discipline,
]);
