<?php
require_once __DIR__ . '/Mail/class.phpmailer.php';
class Mailer extends PHPMailer{
	//
	public function __construct($exceptions = false){
		parent::__construct($exceptions);

		//
		$this->UseSendmailOptions = false;
		//
		$this->CharSet = 'UTF-8';
		$this->IsSMTP();
		$this->SMTPAuth = true;
		$this->SMTPKeepAlive = true;
		//...
		$this->IsHTML(true);
	}
}
?>