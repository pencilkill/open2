<?php
class ControllerDevDev extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public function __construct($registry){
		parent::__construct($registry);

		if(! $this->validate()){
			$this->forward('error/not_found');
		}
	}

	public function index(){
		// ...

		$this->template = $this->config->get('config_template') .'/template/dev/dev.tpl';

		$this->response->setOutput($this->render());
	}

	public function mail() {
		$subject = 'OpenCart郵件測試-主題';
		$html = '<h1>OpenCart郵件測試</h1>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/><br/>OpenCart郵件測試內容<br/>OpenCart郵件測試內容<br/>';

		$mail = new Mail();

		$mail->SetFrom('cmd.dos@hotmail.com', $this->config->get('config_name'));
		$mail->AddAddresses('sam@ozchamp.net');
		$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		$mail->MsgHTML(html_entity_decode($html, ENT_QUOTES, 'UTF-8'));
		$mail->Send();

		if($mail->IsError()){
			echo $mail->ErrorInfo;
		}

		//$this->response->setOutput($output);
	}
	// Checking folder perms, actually backend's common/home will check too
	public function folder(){
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

		//$this->response->setOutput($output);
	}

	public function cii(){
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			if(empty($this->request->post['cii'])){
				$this->error[] = 'Cii is required !';
			}else{
				$cii = $this->request->post['cii'];

				if(isset($this->request->post['model'])){
					$fileName  = DIR_APPLICATION . 'model/' . $cii . '.php';

					$className = 'Model' . strtr(ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $cii)), array(' ' => ''));

					if(is_file($fileName)){
						$this->error[] = $className . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['className'] = $className;
						$template->data['methods'] = array();
						if($this->request->post['model_methods']){
							$methods = explode("\n", trim(strtr($this->request->post['model_methods'], array("\r"=>''))));

							if($methods){
								foreach ($methods as $method){
									if(trim($method)=='') continue;

									$mary = explode('/', $method);

									if(! $mary) continue;

									$methodName = $mary[0];

									if(isset($mary[1]) && trim($mary[1])){
										$methodAccess = $mary[1];
									}else{
										$methodAccess = 'public';
									}

									$template->data['methods'][] = array(
	  					 				'access' => $methodAccess,
	  					 				'method' => $methodName,
									);
								}
							}
						}

						$content = $template->fetch($this->config->get('config_template').'/template/dev/template/model.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = $className . ' file is created !';
						}else{
							$this->error[] = $className . ' file is empty !';
						}
					}
				}
				if(isset($this->request->post['controller'])){
					$fileName  = DIR_APPLICATION . 'controller/' . $cii . '.php';

					$className = 'Controller' . strtr(ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $cii)), array(' ' => ''));

					if(is_file($fileName)){
						$this->error[] = $className . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['className'] = $className;
						$template->data['methods'] = array();
						if($this->request->post['controller_methods']){
							$methods = explode("\n", trim(strtr($this->request->post['controller_methods'], array("\r"=>''))));

							if($methods){
								foreach ($methods as $method){
									if(trim($method)=='') continue;

									$mary = explode('/', $method);

									if(! $mary) continue;

									$methodName = $mary[0];

									if(isset($mary[1]) && trim($mary[1])){
										$methodAccess = $mary[1];
									}else{
										$methodAccess = 'public';
									}

									$template->data['methods'][] = array(
	  					 				'access' => $methodAccess,
	  					 				'method' => $methodName,
									);
								}

							}
						}

						$content = $template->fetch($this->config->get('config_template').'/template/dev/template/controller.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = $className . ' file is created !';
						}else{
							$this->error[] = $className . '\'s content is empty !';
						}
					}
				}
				if(isset($this->request->post['language'])){
					$fileName  = DIR_LANGUAGE . $this->config->get('config_language') . '/' . $cii . '.php';

					if(is_file($fileName)){
						$this->error[] = $this->config->get('config_language') . '/' . $cii . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['texts'] = array();
						if($this->request->post['language_texts']){
							$texts = explode("\n", trim(strtr($this->request->post['language_texts'], array("\r"=>''))));

							$header_title = '';

							if($texts){
								foreach ($texts as $text){
									if(trim($text)=='') continue;

									$mary = explode(';;;', $text);

									if((! $mary) || trim($mary[0])=='') continue;

									if($mary[0] == 'heading_title'){
										$header_title = $mary[1];
										continue;
									}

									$template->data['texts'][$mary[0]] = addcslashes(isset($mary[1]) ? $mary[1] : '', '\'');
								}
							}
						}

						ksort($template->data['texts']);

						$template->data['texts'] = array_merge(array('header_title' => $header_title), $template->data['texts']);

						$content = $template->fetch($this->config->get('config_template').'/template/dev/template/language.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = strtr($fileName, array(DIR_LANGUAGE => '')) . ' file is created !';
						}else{
							$this->error[] = strtr($fileName, array(DIR_LANGUAGE => '')) . '\'s content is empty !';
						}
					}
				}
				if(isset($this->request->post['view'])){
					$fileName  = DIR_TEMPLATE . $this->config->get('config_template') . '/template/' . $cii . '.tpl';

					if(is_file($fileName)){
						$this->error[] = $this->config->get('config_template') . '/' . $cii . ' file is existed !';
					}else{
						$template = new Template();

						$template->data['view_content'] = html_entity_decode($this->request->post['view_content'], ENT_QUOTES, 'UTF-8');

						$content = $template->fetch($this->config->get('config_template').'/template/dev/template/view.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . ' file is created !';
						}else{
							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . '\'s content is empty !';
						}
					}
				}
			}
		}

		$pary = array('cii', 'model', 'model_methods', 'controller', 'controller_methods', 'language', 'language_texts', 'view', 'view_content');

		foreach($pary as $val){
			if(isset($this->request->post[$val])){
				$this->data[$val] = $this->request->post[$val];
			}else{
				$this->data[$val] = '';
			}
		}

		$this->data['error'] = implode('<br/>', $this->error);

		$this->data['action'] = $this->url->link('dev/dev/cii', '', 'SSL');

		$this->template = $this->config->get('config_template') .'/template/dev/cii.tpl';

		$this->response->setOutput($this->render());
	}

	public function bus() {
		// Using clone without modify orignal object
		$config = clone Registry::instance()->get('config');
		var_dump($config->get('config_smtp_host'));
		$config->set('config_smtp_host', 'abc');
		var_dump($config->get('config_smtp_host'));
		$config = Registry::instance()->get('config');
		var_dump($config->get('config_smtp_host'));
	}

	private function validate(){
		return strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false;
	}
}
?>