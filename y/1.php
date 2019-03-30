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

$email = isset($_POST['p5']) ? htmlspecialchars($_POST['p5']) : 'xxx@yandex.ru';
$app_name = isset($_POST['p6']) ? htmlspecialchars($_POST['p6']) : 'NAME';
$app_site_url = isset($_POST['p7']) ? htmlspecialchars($_POST['p7']) : 'https://site.ru';
$app_redirect_uri = isset($_POST['p8']) ? htmlspecialchars($_POST['p8']) : 'https://site.ru';


?>
<h2>Создание приложения</h2>
<form method="post" action="">
Логин:<br><input type="text" name="p1" value="<?php echo $login; ?>"><br><br>
Пароль:<br><input type="text" name="p2" value="<?php echo $password; ?>"><br><br>

<hr>
Пароль из приложения:<br><input type="text" name="p3" value="<?php echo $totp; ?>"><br><br>
Аварийный код:<br><input type="text" name="p4" value="<?php echo $emergency; ?>"><br>
<small>указать что-то одно</small>
<hr><br><br>

Email:<br><input type="text" name="p5" value="<?php echo $email; ?>"><br><br>
app_name:<br><input type="text" name="p6" value="<?php echo $app_name; ?>"><br><br>
app_site_url:<br><input type="text" name="p7" value="<?php echo $app_site_url; ?>"><br><br>
app_redirect_uri:<br><input type="text" name="p8" value="<?php echo $app_redirect_uri; ?>"><br><br>

<input type="submit">
</form>


<?php

if (!isset($_POST['p1'])) {
    exit();
}

$ckfile = 'yandex.txt';

$user_agent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:65.0) Gecko/20100101 Firefox/65.0';

echo '<pre>';
if (file_exists($ckfile)) {
  unlink($ckfile);
}
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
curl_setopt($ch, CURLOPT_HEADER, 1);

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
echo 'output111:<br>';
var_dump($output);
echo '<br><br>';
$output = json_decode($output,1);
curl_close($ch);

echo 'output:<br>';
var_dump($output);
echo '<br><br>';

echo 'params:<br>';
echo '<pre>'. print_r([
	'csrf_token' => $csrf,
	'process_uuid' => $process_uuid,
	'login' => $login
],1);

exit();

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
/* ОТКРЫВАЕМ СТРАНИЦУ ДЛЯ ПОЛУЧЕНИЯ ДОСТУПА К СОЗДАНИЮ ПРИЛОЖЕНИЯ */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/sign.xml?retpath=https%3A%2F%2Fmoney.yandex.ru%2Fmyservices%2Fnew.xml");
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

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
curl_close($ch);

preg_match("/id=\"check-sauth-context-id\".+?value=\"(.+?)\"/is",$output,$found);

if (!isset($found[1])) {
	unlink($ckfile);
    exit('no check-sauth-context-id');
}

$check_id = $found[1];

echo 'check_id = '. $check_id .'<br>';



/*********************/
/*********************/
/* Отправляем аварийный код для получения доступа */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/sign.xml?retpath=https%3A%2F%2Fmoney.yandex.ru%2Fmyservices%2Fnew.xml");
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
	'checkpay' => 1,
	'check-sauth-context-id' => $check_id,
	'retpath' => 'https://money.yandex.ru/myservices/new.xml',
	'isSignFormSubmit' => 'true',
	'emergency-code' => isset($emergency) && $emergency != '' ? $emergency : '',
	'totp-response' => isset($totp) && $totp != '' ? $totp : '',
	'secureparam7' => 'true'
]));

curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
curl_close($ch);

preg_match("/id=\"sk\".+?value=\"(.+?)\"/is",$output,$found);

if (!isset($found[1])) {
	unlink($ckfile);
    exit('no sk');
}

$sk = $found[1];

echo 'sk = '. $sk .'<br>';



/*********************/
/*********************/
/* Создаем приложение */
/*********************/
/*********************/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://money.yandex.ru/myservices/new.xml");
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
	'mform' => 1,
	'sk' => $sk,
	'isCreateServiceSubmit' => 'true',
	'app_name' => $app_name,
	'app_site_url' => $app_site_url,
	'email' => $email,
	'app_redirect_uri' => $app_redirect_uri,
	'my_file' => '',
	'auth_type' => 'PASSWORD'
]));


curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
$output = curl_exec($ch);
curl_close($ch);

preg_match("/Идентификатор приложения:<\/b><br>(.+?)<\/p>/is",$output,$found);
if (!isset($found[1])) {
	unlink($ckfile);
    exit('no client_id');
}
$client_id = $found[1];

echo 'client_id = '. $client_id .'<br>';

preg_match("/OAuth2.+?<\/a>:<\/b><br>(.+?)<\/p>/is",$output,$found);
if (!isset($found[1])) {
	unlink($ckfile);
    exit('no client_secret');
}
$client_secret = $found[1];

echo 'client_secret = '. $client_secret .'<br>';

echo 'ПРИЛОЖЕНИЕ СОЗДАНО<br>';

