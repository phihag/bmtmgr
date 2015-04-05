<?php
namespace bmtmgr;

require_once \dirname(__DIR__) . '/src/common.php';

utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

if (! config\get('allow_init', false)) {
	throw new \Exception('Configuration directive allow_init not set!');
}

// This should later be http://www.blv-nrw.de/blvdb/fb/blv_club_kontakte.php?id=%d
$url_pattern = 'http://localhost/bmtmgr/import/clubs/blv_club_kontakte.php%%3fid=%d';

$imported_clubs = [];
$not_found_since = 0;
Model::beginTransaction();
for ($i = 1;$i < 100000;$i++) {
	$url = \sprintf($url_pattern, $i);
	$page = \file_get_contents($url);

	$r = \preg_match('/
		<label>ClubID:<\/label><div>(?P<id>[0-9-]+)<\/div>.*
		<label>Vereinsname:<\/label><div>(?P<name>[^<]+)<\/div>.*
		<label>Email:<\/label><div><a.*?>(?P<email>[^<]+)<\/a>
		/xs', $page, $m);
	if (!$r) {
		$not_found_since++;
		if ($not_found_since > 100) {
			break;
		}
		continue;
	}
	$id = \html_entity_decode($m['id'], ENT_QUOTES | ENT_HTML5, 'utf-8');
	$name = \html_entity_decode($m['name'], ENT_QUOTES | ENT_HTML5, 'utf-8');
	$email = \html_entity_decode($m['email'], ENT_QUOTES | ENT_HTML5, 'utf-8');

	$c = User::by_id_optional($id);
	if (! $c) {
		$c = new User($id, $name, $email, ['register']);
		$c->save();
		\array_push($imported_clubs, $c);
	}
}
Model::commit();

render_ajax('club/', [
	'imported_clubs' => $imported_clubs
]);