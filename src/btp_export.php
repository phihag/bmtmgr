<?php
namespace bmtmgr\btp_export;

require_once dirname(__DIR__) . '/libs/PHP_XLSXWriter/xlsxwriter.class.php';

function render($data) {
	$tournament = $data['tournament'];
	$disciplines = $data['disciplines'];

	$header = [
		'Event' => 'string',
		'Member ID' => 'string',
		'Name' => 'string',
		'First name' => 'string',
		'Club' => 'string',
		'Club-ID' => 'string',
		'Gender' => 'string',
		'Email' => 'string',
		'Setzplatz' => 'string',
		'Team-ID' => 'string',
		'Team' => 'string',
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

		\assert($d->is_team());
		$entry_id = 0;
		foreach ($d->entries as $entry) {
			foreach ($entry['players'] as $player) {
				\array_push($output, [
					$dname,
					$player->textid,
					$player->get_lastname(),
					$player->get_firstname(),
					$player->entry_club->name,
					$player->entry_club->textid,
					$player->gender,
					$player->email,
					$entry['seeding'],
					$dname . '-' . $entry_id,
					$entry['entry_name'],
				]);
			}
			$entry_id++;
		}
	}


	$writer = new \XLSXWriter();
	$writer->writeSheet($output, 'Meldungen', $header);

	$safe_filename = \bmtmgr\utils\sanitize_filename($tournament->name);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $safe_filename . '.xlsx"');
	echo $writer->writeToString();
}