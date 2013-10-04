<?php
class ControllerDebugDebug extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public function index() {
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$to = array('cmd.dos@hotmail.com','mail.song.de.qiang@gmail.com');
		$sender = '千點距';
		$subject = 'OpenCart郵件測試-主題';
		$html = '<h1>OpenCart郵件測試</h1>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/><br/>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/>';

		$mail = new Mail();

		$mail->Host = $this->config->get('config_smtp_host');
		$mail->Username = $this->config->get('config_smtp_username');
		$mail->Password = $this->config->get('config_smtp_password');
		$mail->Port = $this->config->get('config_smtp_port');
		$mail->Timeout = $this->config->get('config_smtp_timeout');


		$mail->SetFrom($this->config->get('config_smtp_username'), $this->config->get('config_name'));
		$mail->ClearReplyTos();
		$mail->AddReplyTo($this->config->get('config_email'), $this->config->get('config_name'));
		foreach($to as $addr){
			$mail->AddAddress($addr);
		}
	    $mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
	    $mail->MsgHTML(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
	    $mail->Send();

	    if($mail->IsError()){
	    	echo $mail->ErrorInfo;
	    }

		//$this->response->setOutput($output);
  	}

  	private function validate(){
  		return strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false;
  	}
}
?>