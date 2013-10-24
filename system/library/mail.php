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
 * @author Sam@ozchamp.net
 * @example
 *
	$mail = new Mail();

	$mail->Host = $this->config->get('config_smtp_host');
	$mail->Username = $this->config->get('config_smtp_username');
	$mail->Password = $this->config->get('config_smtp_password');
	$mail->Port = $this->config->get('config_smtp_port');
	$mail->Timeout = $this->config->get('config_smtp_timeout');

	$mail->Sender = $this->config->get('config_smtp_username');

	$mail->SetFrom('cmd.dos@hotmail.com', $this->config->get('config_name'));
	$mail->AddAddress('sam@ozchamp.net');
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

		if(! class_exists('Mailer')){
			throw new Exception('Can not load mailer !');
		}

		$this->driver = new Mailer(false);
	}
	// Working as it is without any checking
	public function __set($property, $value){
		$this->driver->$property = $value;
	}
	// Working as it is without any checking
	public function __get($property){
		return $this->driver->$property;
	}
	// Working as it is without any checking
	public function __call($method,$args){
	   return call_user_func_array(array($this->driver, $method), $args);
	}
}
?>