<?php

	session_start();
	
    ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);

	header('Content-Type: text/html; charset=utf-8');
	

$login = isset($_POST['p1']) ? htmlspecialchars($_POST['p1']) : '';
$password = isset($_POST['p2']) ? htmlspecialchars($_POST['p2']) : '';
// указываем что-то одно
$totp = isset($_POST['p3']) ? htmlspecialchars($_POST['p3']) : '';// пороль из приложения
$emergency = isset($_POST['p4']) ? htmlspecialchars($_POST['p4']) : ''; // аварийный

$client_id = isset($_POST['p5']) ? htmlspecialchars($_POST['p5']) : '';
$client_secret = isset($_POST['p6']) ? htmlspecialchars($_POST['p6']) : '';
$app_redirect_uri = isset($_POST['p7']) ? htmlspecialchars($_POST['p7']) : 'https://site.ru';

?>

<h2>Получение токена</h2>
<form method="post" action="">
Логин:<br><input type="text" name="p1" value="<?php echo $login; ?>"><br><br>
Пароль:<br><input type="text" name="p2" value="<?php echo $password; ?>"><br><br>

<hr>
Пароль из приложения:<br><input type="text" name="p3" value="<?php echo $totp; ?>"><br><br>
Аварийный код:<br><input type="text" name="p4" value="<?php echo $emergency; ?>"><br>
<small>указать что-то одно</small>
<hr><br><br>

client_id:<br><input type="text" name="p5" value="<?php echo $client_id; ?>"><br><br>
client_secret:<br><input type="text" name="p6" value="<?php echo $client_secret; ?>"><br><br>
app_redirect_uri:<br><input type="text" name="p7" value="<?php echo $app_redirect_uri; ?>"><br><br>

<input type="submit">
</form>


<?php

if (!isset($_POST['p1'])) {
    exit();
}

$ckfile = dirname(__FILE__).'/yandex.txt';

$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:65.0) Gecko/20100101 Firefox/65.0';

echo '<pre>';
unlink($ckfile);
/*********************/
/*********************/
/******* ШАГ 1 *******/
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://passport.yandex.ru/auth");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: passport.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));
curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
curl_close($ch);
//echo $output;

preg_match("/data-csrf=\"(.+?)\"/is",$output,$found);
if (!isset($found[1])) {
	unlink($ckfile);
    exit('no csrf');
}
$csrf = $found[1];


preg_match("/href=\"https:\/\/passport\.yandex\.ru\/restoration\/login\?process_uuid=(.+?)\"/is",$output,$found);
if (!isset($found[1])) {
	unlink($ckfile);
    exit('no process_uuid');
}
$process_uuid = $found[1];

/*********************/
/*********************/
/******* ШАГ 2 *******/
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://passport.yandex.ru/registration-validations/auth/multi_step/start");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: passport.yandex.ru',
    'Accept: application/json, text/javascript, */*; q=0.01',
    'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
    'Referer: https://passport.yandex.ru/auth',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'X-Requested-With: XMLHttpRequest',
    'Connection: keep-alive'
));

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'csrf_token' => $csrf,
	'process_uuid' => $process_uuid,
	'login' => $login
]));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch),1);
curl_close($ch);

if (!isset($output['status']) || $output['status'] != 'ok') {
	unlink($ckfile);
    exit('no status');
}
if (!isset($output['track_id'])) {
	unlink($ckfile);
    exit('no track_id');
}

$track_id = $output['track_id'];

echo 'track_id = '. $track_id .'<br>';

/*********************/
/*********************/
/******* ШАГ 3 *******/
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://passport.yandex.ru/registration-validations/auth/multi_step/commit_password");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: passport.yandex.ru',
    'Accept: application/json, text/javascript, */*; q=0.01',
    'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
    'Referer: https://passport.yandex.ru/auth/welcome',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'X-Requested-With: XMLHttpRequest',
    'Connection: keep-alive'
));

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'csrf_token' => $csrf,
	'track_id' => $track_id,
	'password' => $password
]));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch),1);
curl_close($ch);

if (!isset($output['status']) || $output['status'] != 'ok') {
	unlink($ckfile);
    exit('error auth');
}

echo 'АВТОРИЗОВАНЫ' .'<br>';



/*********************/
/*********************/
/* Открываем форму для получения CODE */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/oauth/authorize");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'client_id' => $client_id,
	'response_type' => 'code',
	'redirect_uri' => $app_redirect_uri,
	'scope' => 'account-info operation-history operation-details payment-p2p payment-shop'
]));

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
curl_close($ch);

preg_match_all("/<input.+?name=\"(.+?)\".+?value=\"(.+?)\"/is",$output,$found);

$params = [];
foreach ($found[1] as $k => $v) {
	if (substr($v, -13) == '.limit[1].sum') {
		$params[$v] = 100000;
	} else {
		$params[$v] = $found[2][$k];
	}
}
$sk = $params['sk'];
print_r($params);
echo '<br>';

/*********************/
/*********************/
/* NEXT */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/ajax/oauth2-submit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);


curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));


curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch), 1);
curl_close($ch);

print_r($output);
if ($output['status'] != "pending-auth") {
	unlink($ckfile);
    exit('status != pending-auth');
}
$authContextId = $output['authContextId'];
$operationId = $output['operationId'];

/*********************/
/*********************/
/* NEXT */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/ajax/secure-auth/init");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);


curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'authContextId' => $authContextId,
	'sk' => $sk,
	'delayedSend' => ''
]));


curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch), 1);
curl_close($ch);

print_r($output);



/*********************/
/*********************/
/* NEXT */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/ajax/secure-auth/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'authContextId' => $authContextId,
	'sk' => $sk,
	'sauthType' => 'emergency'
]));


curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch), 1);
curl_close($ch);

print_r($output);



/*********************/
/*********************/
/* ОТПРАВЛЯЕМ КОД ДЛЯ ПОУЧЕНИЯ */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/ajax/secure-auth/check");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'authContextId' => $authContextId,
	'sk' => $sk,
	'delayedSend' => 'true',
	'sauthType' => isset($totp) && $totp != '' ? 'totp' : 'emergency',
	'sauthAnswer' => isset($totp) && $totp != '' ? $totp : $emergency
]));


curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch), 1);
curl_close($ch);

print_r($output);



/*********************/
/*********************/
/* NEXT */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/ajax/oauth2-submit");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: application/json, text/javascript, */*; q=0.01',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1',
	'X-Requested-With: XMLHttpRequest'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'operationId' => $operationId,
	'sk' => $sk
]));

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch),1);
curl_close($ch);

if ($output['status'] != 'success') {
	unlink($ckfile);
    exit('Не удалось получить CODE. status != success');
}
$a = explode("=", $output['redirectUrl']);
if (!isset($a[1])) {
	unlink($ckfile);
    exit('В ссылке редиректа не нашли CODE');
}
$code = $a[1];
echo 'code = '. $code .'<br>';
echo '<font color="green">CODE ПОЛУЧЕН</font><br>';



/*********************/
/*********************/
/* Меняем CODE на TOKEN */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/oauth/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Host: money.yandex.ru',
	'Accept: application/json, text/javascript, */*; q=0.01',
	'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'Connection: keep-alive',
	'Upgrade-Insecure-Requests: 1',
	'X-Requested-With: XMLHttpRequest'
));

curl_setopt($ch,CURLOPT_USERAGENT,$user_agent);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
	'code' => $code,
	'client_id' => $client_id,
	'grant_type' => 'authorization_code',
	'redirect_uri' => $app_redirect_uri,
	'client_secret' => $client_secret
]));

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = json_decode(curl_exec($ch),1);
curl_close($ch);

if (!isset($output['access_token']) || $output['access_token'] == '') {
	unlink($ckfile);
    exit('не удалось обменять code на token');
}

$access_token = $output['access_token'];

echo 'access_token = '. $access_token .'<br>';
echo '<font color="green">TOKEN ПОЛУЧЕН</font><br>';
unlink($ckfile);
    exit();


