<?php

/**
 * @param null $host
 * @return mixed
 */
function webuzo_update_logo($host = null)
{
	include_once __DIR__.'/webuzo_sdk.php';
	$url = "http://$host:2004/install.php?sitename=InterServer Webuzo";
	// Set the curl parameters.
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1000);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
	if (!empty($post)) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	}
	// Get response from the server.
	$resp = curl_exec($ch);
	return $resp;
}
