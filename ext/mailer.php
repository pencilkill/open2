<?php
require_once __DIR__ . '/Mail/class.phpmailer.php';
class Mailer extends PHPMailer{
	//
	public function __construct($exceptions = false){
		parent::__construct($exceptions);
	}

	public function AddAddresses($addresses, $delimiter = ','){
		$addresses = is_array($addresses) ? $addresses : explode($delimiter, $addresses);

		if($addresses){
			foreach($addresses as $key => $val){
				$address = $key;
				$name = $val;

				if(is_numeric($key)){
					$address = $val;
					$name = '';		// PHPMailer default value
				}

				if(trim($address)){
					$this->AddAddress($address, $name);
				}
			}
		}
	}
}
?>