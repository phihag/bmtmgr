<?php
namespace bmtmgr\email;

function _format_headers($headers) {
	$preferences = array(
    	'input-charset' => 'utf-8',
    	'output-charset' => 'UTF-8',
    	'line-length' => 76,
    	'line-break-chars' => "\n",
    	'scheme' => 'Q'
	);

	return \implode("\r\n", \array_map(function($k, $v) {
		return \iconv_mime_encode($k, $v, $preferences);
	}, \array_keys($headers), $headers));
}

function send($to, $template, $data) {
	$fulltext = \bmtmgr\get_rendered($template, $data);
	$firstline_end = strpos($fulltext, "\n");
	$subject = substr($fulltext, 0, $firstline_end);
	$body_html = trim(substr($fulltext, $firstline_end));
	$from = \bmtmgr\config\get('mail_from');
	$from_name = 'Badminton-Turnier-Manager';

	if (\bmtmgr\config\get('mail_debug', false)) {
		return array(
			'from' => $from_name . ' <' . $from . '>',
			'to' => $to,
			'subject' => $subject,
			'body_html' => $body_html
		);
	} else {
		$headers = array(
			'From' => $from_name . ' <' . $from . '>',
			'To' => $to,
			'Subject' => $subject,
			'Content-Type' => 'text/html; charset="utf-8"',
			'Content-Transfer-Encoding' => 'quoted-printable'
		);

		echo "TODO: Call mail();!!!";
		// mail();
		return null;
	}
}
