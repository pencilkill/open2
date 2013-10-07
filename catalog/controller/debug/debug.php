<?php
class ControllerDebugDebug extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public function index(){
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$this->load->model('localisation/tw_zone');

		$this->data['cities'] = $this->model_localisation_tw_zone->getCities();

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/debug/debug.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/debug/debug.tpl';
		} else {
			$this->template = 'default/template/debug/debug.tpl';
		}

		$this->response->setOutput($this->render());
	}

	public function mail() {
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$subject = 'OpenCart郵件測試-主題';
		$html = '<h1>OpenCart郵件測試</h1>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/><br/>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/>';

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

	    if($mail->IsError()){
	    	echo $mail->ErrorInfo;
	    }

		//$this->response->setOutput($output);
  	}

  	public function folder(){
  		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$folders = array(
			DIR_IMAGE,
			DIR_CACHE,
			DIR_DOWNLOAD,
			DIR_LOGS,
		);

		$results = array();

		foreach($folders as $folder){
			$results[] = array(
				$folder,
				(int)is_dir($folder),
				is_dir($folder) ? substr(sprintf('%o', fileperms($folder)), -4) : '0000',
			);
		}

		$html = '<table width="100%">';
		foreach($results as $result){
			$html .= str_replace(array('{Folder}', '{IsDir}', '{Perms}'), $result, '<tr><td>{Folder}</td><td>{IsDir}</td><td>{Perms}</td></tr>');
		}
		$html.='</table>';

		echo $html;
  	}

  	private function validate(){
  		return strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false;
  	}
}
?>