<?php
namespace bmtmgr\btp_export;

require_once dirname(__DIR__) . '/libs/PHP_XLSXWriter/xlsxwriter.class.php';

function render($data) {
	$tournament = $data['tournament'];
	$disciplines = $data['disciplines'];

	$is_team = $disciplines[0]->is_team();
	foreach ($disciplines as $testd) {
		\assert($is_team === $testd->is_team());
	}

	if ($is_team) {
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
	} else {
		$header = [
			'Event' => 'string',
			'SpielerID' => 'string',
			'Name' => 'string',
			'Vorname' => 'string',
			'Verein' => 'string',
			'Club ID' => 'string',
			'Geschlecht' => 'string',
			'Email' => 'string',
			'Setzplatz' => 'string',
			'Partner ID' => 'string',
		];
	}

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

		$entry_id = 0;
		foreach ($d->entries as $entry) {
			$eplayers = $entry['players'];
			$player_idx = 0;
			foreach ($eplayers as $player) {
				$row = [
					$dname,
					$player->textid,
					$player->get_lastname(),
					$player->get_firstname(),
					$player->entry_club->name,
					$player->entry_club->textid,
					$player->gender,
					$player->email,
					$entry['seeding'],
				];

				if ($is_team) {
					$row[] = $dname . '-' . $entry_id;
					$row[] = $entry['entry_name'];
				} else {
					if ($player_idx === 0) {
						if (\count($eplayers) > 1) {
							$partner_id = $eplayers[1]->textid;
						} else {
							$partner_id = '';
						}
					} else {
						$partner_id = $eplayers[0]->textid;
					}
					$row[] = $partner_id;
				}

				\array_push($output, $row);
				$player_idx++;
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