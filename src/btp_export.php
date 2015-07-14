<?php
namespace bmtmgr\btp_export;

require_once dirname(__DIR__) . '/libs/PHP_XLSXWriter/xlsxwriter.class.php';

function render($data) {
	$tournament = $data['tournament'];
	$disciplines = $data['disciplines'];

	$header = [
		'Event' => 'string',
		'SpielerID' => 'string',
		'Name' => 'string',
		'Vorname' => 'string',
		'Verein' => 'string',
		'Geschlecht' => 'string',
		'Email' => 'string',
		'Partner ID' => 'string',
		'Setzplatz' => 'string',
	];

	$output = [];
	foreach ($disciplines as $d) {
		$dname = $d->name;
		// Fix up discipline names
		if (\preg_match('/^H([DE]) U0?([0-9]+)$/', $dname, $m)) {
			$dname = 'J' . $m[1] . ' U' . $m[2];
		} elseif (\preg_match('/^D([DE]) U0?([0-9]+)$/', $dname, $m)) {
			$dname = 'M' . $m[1] . ' U' . $m[2];
		} elseif (\preg_match('/^(DD|DE|GD|HD|HE|MX)-?\s*([A-Z])$/', $dname, $m)) {
			$dname = $m[1] . $m[2];
		}

		$is_doubles = $d->with_partner();
		foreach ($d->entries as $er) {
			if ($er['on_waiting_list']) {
				continue;
			}
			if ($is_doubles && ($er['partner'] === NULL)) {
				continue;
			}
			\array_push($output, [
				$dname,
				$er['player']->textid,
				$er['player']->get_lastname(),
				$er['player']->get_firstname(),
				$er['player_club']->name,
				$er['player']->gender,
				$er['player']->email,
				$is_doubles ? $er['partner']->textid : '',
				$er['seeding'],
			]);
			if ($is_doubles) {
				\array_push($output, [
					$dname,
					$er['partner']->textid,
					$er['partner']->get_lastname(),
					$er['partner']->get_firstname(),
					$er['partner_club']->name,
					$er['partner']->gender,
					$er['partner']->email,
					$is_doubles ? $er['player']->textid : '',
					$er['seeding'],
				]);
			}
		}
	}


	$writer = new \XLSXWriter();
	$writer->writeSheet($output, 'Meldungen', $header);

	$safe_filename = \bmtmgr\utils\sanitize_filename($tournament->name);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $safe_filename . '.xlsx"');
	echo $writer->writeToString();
}