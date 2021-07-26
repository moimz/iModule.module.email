<?php
/**
 * 이 파일은 iModule 이메일모듈의 일부입니다. (https://www.imodules.io)
 *
 * 테스트메일을 전송한다.
 * 
 * @file /modules/email/process/@sendTest.php
 * @author Arzz (arzz@arzz.com)
 * @license MIT License
 * @version 3.1.0
 * @modified 2021. 7. 26.
 */
if (defined('__IM__') == false) exit;

$receiver = Request('receiver');
$templet = new stdClass();
$templet->templet = Request('templet');
$templet->templet_configs = new stdClass();
foreach ($_POST as $key=>$value) {
	if (strpos($key,'templet_configs') === 0) {
		$templet->templet_configs->{preg_replace('/^templet_configs_/','',$key)} = $value;
	}
}
$default_email = Request('default_email');
$default_name = Request('default_name');
$use_sendmail = Request('use_sendmail') ? true : false;

REQUIRE_ONCE $this->getModule()->getPath().'/classes/phpmailer/class.phpmailer.php';

$from = array($default_email,$default_name);
$body = new stdClass();
$body->subject = 'This is test email';
$body->content = 'This is test email From '.$_SERVER['HTTP_HOST'];

$PHPMailer = new PHPMailer();
$PHPMailer->pluginDir = $this->getModule()->getPath().'/classes/phpmailer';
$PHPMailer->isHTML(true);
$PHPMailer->Encoding = 'base64';
$PHPMailer->CharSet = 'UTF-8';

if ($use_sendmail === false) {
	REQUIRE_ONCE $this->getModule()->getPath().'/classes/phpmailer/class.smtp.php';
	
	$PHPMailer->IsSMTP();
	if (Request('smtp_type') != 'NONE') {
		$PHPMailer->SMTPSecure = strtolower(Request('smtp_type'));
	} else {
		$PHPMailer->SMTPAutoTLS = false;
	}
	$PHPMailer->Host = Request('smtp_server');
	$PHPMailer->Port = Request('smtp_port');
	
	if (Request('smtp_id') && Request('smtp_password')) {
		$PHPMailer->SMTPAuth = true;
		$PHPMailer->Username = Request('smtp_id');
		$PHPMailer->Password = Request('smtp_password');
	}
}

$PHPMailer->setFrom($default_email,'=?UTF-8?b?'.base64_encode($default_name).'?=');
$PHPMailer->Subject = '=?UTF-8?b?'.base64_encode($body->subject).'?=';

$result = false;
$PHPMailer->addAddress($receiver);
$PHPMailer->Body = $this->getTemplet($templet)->getContext('index',$body);

$result = $PHPMailer->send();

$results->success = $result;
?>