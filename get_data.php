<?php

function get_data($url) {
	$ch = curl_init();
	$connect_timeout = 5;
	$resolve_timeout = 10;

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
	curl_setopt($ch, CURLOPT_TIMEOUT, $resolve_timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

?>
