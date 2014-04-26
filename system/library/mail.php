<?php
/**
 * This class is not opencart original mail class
 *
 * All the mail scene have been modified in this original opencart system
 *
 * Notice: the sender email host should be matched with the smtp host to avoid smtp host refusing
 * Specified the Sender before using SetFrom method in this case please
 * Reading SetFrom method to get more about Sender and Replyto proprities
 *
 * @author Sam <mail.song.de.qiang@gmail.com>
 * @example
 *
	$mail = new Mail();

	$mail->SetFrom('cmd.dos@hotmail.com', $this->config->get('config_name'));
	$mail->AddAddresses('mail.song.de.qiang@gmail.com');
    $mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
    $mail->MsgHTML(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
    $mail->Send();
 */

class Mail{
	private $driver;

	// Including files in runtime
	public function __construct(){
		if(is_file(DIR_EXT.'/mailer.php')){
			require_once DIR_EXT.'/mailer.php';
		}else{
			throw new Exception('Can not load mailer !');
		}

		if(! class_exists('Mailer', false)){
			throw new Exception('Can not load mailer !');
		}
		// Using clone without changing orignal object
		$config = clone Registry::instance()->get('config');

		$this->driver = new Mailer(false);

		//
		$this->driver->UseSendmailOptions = false;
		//
		$this->driver->CharSet = 'UTF-8';
		$this->driver->IsSMTP();
		$this->driver->SMTPAuth = true;
		$this->driver->SMTPKeepAlive = true;
		//...
		$this->driver->IsHTML(true);

		$this->driver->Host = $config->get('config_smtp_host');
		$this->driver->Username = $config->get('config_smtp_username');
		$this->driver->Password = $config->get('config_smtp_password');
		$this->driver->Port = $config->get('config_smtp_port');
		$this->driver->Timeout = $config->get('config_smtp_timeout');

		$this->driver->Sender = $config->get('config_smtp_username');
	}
	// Working as it was without any checking
	public function __set($property, $value){
		$this->driver->$property = $value;
	}
	// Working as it was without any checking
	public function __get($property){
		return $this->driver->$property;
	}
	// Working as it was without any checking
	public function __call($method,$args){
		$class = $this->driver;

		if(method_exists($this, $method)){
			$class = $this;
		}

		$ref = new ReflectionMethod($class, $method);

		return $ref->invokeArgs($class, $args);
	}
}
?>