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


	switch ($type) {
		case 'getBalance':
			$a = $api->getBalance($phone);
			if (isset($a[0], $a[0]['balance'], $a[0]['balance']['amount']) {
				echo json_encode([
					'success' => 1, 
					'response' => $a[0]['balance']['amount']
				], JSON_NUMERIC_CHECK);
			}
			echo json_encode([]);
			exit();
		break;
		case 'getTransaction':
			if (!isset($_POST['txid'])) {
				exit('getTransaction not txid');
			}
			$res = $api->call('/payment-history/v2/transactions/'. intval($_POST['txid'])));
			echo json_encode([
				'success' => 1, 
				'response' => $res
			], JSON_NUMERIC_CHECK);
			exit();
		break;
		case 'getPayments':
			if (!isset($_POST['params'])) {
				exit('getPayments not params');
			}
			$params = urldecode($_POST['params']);
			$res = $api->call("/payment-history/v2/persons/". $phone ."/payments", json_decode($params));
			echo json_encode([
				'success' => 1, 
				'response' => $res
			], JSON_NUMERIC_CHECK);
			exit();
		break;
		case 'sendMoneyToProvider':
			if (!isset($_POST['params'], $_POST['providerId'])) {
				exit('sendMoneyToProvider not params or providerId');
			}
			$params = urldecode($_POST['params']);
			$providerId = intval($_POST['providerId']);
			$res = $api->call("sinap/api/v2/terms/". $providerId ."/payments", json_decode($params));
			echo json_encode([
				'success' => 1, 
				'response' => $res
			], JSON_NUMERIC_CHECK);
			exit();
		break;
	}
	exit('error type');


// $params = urldecode($_GET['token']);
