<?php

	require_once 'QiwiApi.php';
	
	if (!isset($_POST['phone'], $_POST['token'], $_POST['type'])) {
		exit('error params');
	}
	$phone = $_POST['phone'];
	$token = $_POST['token'];
	$type = $_POST['type'];

	$api = new QiwiApi($phone, $token);
   
	switch ($type) {
		case 'getBalance':
			$a = $api->getBalance($phone);
			if (isset($a[0], $a[0]['balance'], $a[0]['balance']['amount']) {	
				echo json_encode([
					'success' =>1, 
					'balance' => $a[0]['balance']['amount']
				], JSON_NUMERIC_CHECK);
				exit();
			}
			echo json_encode([
				'success' => 0
			], JSON_NUMERIC_CHECK);
			exit();
		break;
	}
	echo json_encode($api->getBalance($phone), 1)
    
				exit();


// $params = urldecode($_GET['token']);
