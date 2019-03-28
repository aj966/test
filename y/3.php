<?php

    ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	header('Content-Type: text/html; charset=utf-8');

	if (!isset($_POST['token'], $_POST['link'], $_POST['params'])) {
		exit('error params');
	}
	
	$token = $_POST['token'];
	$link  = urldecode($_POST['link']);
	$params = json_decode(urldecode($_POST['params']));

	// - - - - - - -
	
	$url = 'https://money.yandex.ru'. $link;
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
		"Authorization: Bearer ". $token
	));
	curl_setopt ($curl, CURLOPT_USERAGENT, 'Yandex.Money.SDK/PHP');
	curl_setopt ($curl, CURLOPT_POST, 1);
	$query = http_build_query($params);
	curl_setopt ($curl, CURLOPT_POSTFIELDS, $query);
	curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, 0);
	curl_setopt ($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt ($curl, CURLOPT_SSL_VERIFYHOST, 2);
	$body = curl_exec ($curl);
	curl_close ($curl);

	echo json_encode([
		'success' => 1, 
		'response' => json_decode($body)
	], JSON_NUMERIC_CHECK);
	exit();
	
