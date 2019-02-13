<?php

	require_once 'QiwiApi.php';
	
	$phone = urldecode($_GET['phone']);
	$token = urldecode($_GET['token']);
	
	$api = new QiwiApi($phone, $token);
    
    echo json_encode($api->getBalance($phone), 1)
    exit();