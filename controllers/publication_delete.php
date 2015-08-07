<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['publication_id']);
$publication = Publication::by_id($_GET['publication_id']);
$tournament = $publication->get_tournament();

$publication->delete();
TODO