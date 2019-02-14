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
					'balance' => $a[0]['balance']['amount']
				], JSON_NUMERIC_CHECK);
				exit();
			}
			echo json_encode([]);
			exit();
		break;
		case 'getTransaction':
			if (!isset($_POST['txid'])) {
				exit('getTransaction not txid');
			}
			$res = $api->call('/payment-history/v2/transactions/'. intval($_POST['txid'])));
			echo json_encode($res, JSON_NUMERIC_CHECK);
			exit();
		break;
		case 'getPayments':
			if (!isset($_POST['params'])) {
				exit('getPayments not params');
			}
			$params = urldecode($_POST['params']);
			$res = $api->call("/payment-history/v2/persons/". $phone ."/payments", json_decode($params));
			echo json_encode($res, JSON_NUMERIC_CHECK);
			exit();
		break;
	}
	exit('error type');


// $params = urldecode($_GET['token']);
