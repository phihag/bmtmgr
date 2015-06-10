<?php
namespace bmtmgr;
require_once dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['entry_id']);
$entry = Entry::by_id($_GET['entry_id']);
$discipline = $entry->get_discipline();

TODO