<?php

	require_once 'QiwiApi.php';
	
	if (!isset($_POST['phone'], $_POST['token'], $_POST['link'], $_POST['params'])) {
		exit('error params');
	}
	$phone = $_POST['phone'];
	$token = $_POST['token'];
	$link  = urldecode($_POST['link']);
	$params = json_decode(urldecode($_POST['params']));

	$api = new QiwiApi($phone, $token);
   
	echo json_encode([
		'success' => 1, 
		'response' => $api->call($link, $params)
	], JSON_NUMERIC_CHECK);
	exit();
