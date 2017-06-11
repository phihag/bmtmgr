<?php
namespace bmtmgr;

require_once \dirname(__DIR__) . '/src/common.php';

function _curl_req($ch, $url) {
	\curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
	\curl_setopt($ch, \CURLOPT_HEADER, true);
	\curl_setopt($ch, \CURLOPT_URL, $url);

	$result = \curl_exec($ch);
	list($headers_str, $content) = \explode("\r\n\r\n", $result, 2);

	$cookies = [];
	foreach (explode("\r\n", $headers_str) as $hdr_str) {
		$split = explode(':', $hdr_str, 2);
		if (\count($split) !== 2) {
			continue;
		}
		list($k, $v) = $split;
		if (\strtolower($k) !== 'set-cookie') {
			continue;
		}
		$cookie = \explode(';', \trim($v), 2)[0];
		\array_push($cookies, $cookie);
	}

	$cookie_str = \implode('; ', $cookies);
	return [$cookie_str, $content];
}


utils\csrf_protect();
$u = user\check_current();
$u->require_perm('admin');

utils\require_get_params(['season_id']);
$season = Season::by_id($_GET['season_id']);

\assert($season->name === 'Ligen NRW 2015-16');
$season_digits = '1516';


$ch = \curl_init();

// Login form
list($cookie_str, $html) = _curl_req($ch, 'http://www.badminton-bax.de/index.php/bax-portal');
\preg_match_all(
	'/<input\s+type="hidden"\s+name="(?P<key>[^"]+)"\s+value="(?P<val>[^"]+)"/',
	$html, $hidden_field_matches, \PREG_SET_ORDER);
\curl_setopt($ch, \CURLOPT_COOKIE, $cookie_str);

// Log in
$data = [
	'username' => 'Spieler',
	'password' => 'Bax2020',
	'Submit' => '',
	'remember' => '',
];
foreach ($hidden_field_matches as $m) {
	$data[$m['key']] = $m['val'];
}
\curl_setopt($ch, \CURLOPT_POST, 1);
\curl_setopt($ch, \CURLOPT_POSTFIELDS, \http_build_query($data));
list($cookie_str, $html) = _curl_req($ch, 'http://www.badminton-bax.de/index.php/bax-portal');

\curl_setopt($ch, \CURLOPT_COOKIE, $cookie_str);
\curl_setopt($ch, \CURLOPT_HEADER, false);
\curl_setopt($ch, \CURLOPT_POST, false);

$FIELDS = [
	'e' => 'baxe',
	'd' => 'baxd',
	'x' => 'baxm',
];
$count = 100000;

Model::beginTransaction();
$pcount = 0;
foreach (['m', 'f'] as $gender) {
	foreach ($FIELDS as $online_discipline=>$db_key) {
		$bax_url = 'http://www.badminton-bax.de/index.php/bax-portal/bax-rang?saisons=' . $season_digits . '&disziplin=' . $online_discipline . '&sex=' . $gender . '&jgang_von=1905&jgang_bis=2028&check_id=on&check_pos=on&check_jahrgang=on&check_verein=on&check_alt=on&check_niveau=on&check_erfolg=on&zeig_rang=&von_pos=1&um_anz=' . $count . '&auswahl=d&auswahl2=s';

		\curl_setopt($ch, \CURLOPT_URL, $bax_url);
		$result = \curl_exec($ch);

		$s = $GLOBALS['db']->prepare('UPDATE player SET ' . $db_key . '=? WHERE season_id=? AND textid=?');
		preg_match_all(
			'/^\s*<td align=\'right\'>&nbsp;([0-9]+-[0-9]+)&nbsp;<\/td>.*?<td align=\'center\'><b>([0-9]+)<\/b><\/td>/ms',
			$result, $matches, \PREG_SET_ORDER);
		foreach ($matches as $m) {
			$s->execute([\intval($m[2]), $season->id, $m[1]]);
			$pcount++;
		}
	}
}
Model::commit();

echo 'Imported ' . $pcount . ' entries.';

\curl_close ($ch);
