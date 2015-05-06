<?php
namespace bmtmgr\btp_export;

require_once dirname(__DIR__) . '/libs/PHP_XLSXWriter/xlsxwriter.class.php';

function render($data) {
	$tournament = $data['tournament'];

	$header = [
		'SpielerID',
		'Name',
		'Vorname',
		'Verein',
		'Geschlecht',
		'Email',
		'Event',
		'PartnerID',
	];

	$data1 = array(
	);

	$writer = new \XLSXWriter();
	$writer->writeSheet($data1, 'Meldungen', $header);

	$safe_filename = \bmtmgr\utils\safe_filename($tournament->name);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $safe_filename . '.xlsx"');
	echo $writer->writeToString();
}