<?php
namespace bmtmgr\btp_export;

require_once dirname(__DIR__) . '/libs/PHP_XLSXWriter/xlsxwriter.class.php';

function render($data) {
	$data1 = array(  
	);

	$writer = new \XLSXWriter();
	$writer->writeSheet($data1);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	// TODO content-disposition: attachment
	echo $writer->writeToString();
}