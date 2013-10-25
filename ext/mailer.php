<?php
require_once __DIR__ . '/Mail/class.phpmailer.php';
class Mailer extends PHPMailer{
	//
	public function __construct($exceptions = false){
		parent::__construct($exceptions);
	}
}
?>