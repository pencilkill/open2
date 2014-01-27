<?php
class ControllerCommonForgotten extends Controller {
	protected $preload_model = array('user/user');

	private $error = array();

	public function index() {
		if ($this->user->isLogged()) {
			$this->redirect($this->url->link('common/home', '', 'SSL'));
		}

		$this->language->load('common/forgotten');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->language->load('mail/forgotten');

			$code = sha1(uniqid(mt_rand(), true));

			$this->model_user_user->editCode($this->request->post['email'], $code);

			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

			$message  = sprintf($this->language->get('text_greeting'), $this->config->get('config_name')) . "\n\n";
			$message .= sprintf($this->language->get('text_change'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->url->link('common/reset', 'code=' . $code, 'SSL') . "\n\n";
			$message .= sprintf($this->language->get('text_ip'), $this->request->server['REMOTE_ADDR']) . "\n\n";

			$mail = new Mail();

			$mail->SetFrom($this->config->get('config_email'), $this->config->get('config_name'));
		    $mail->AddAddresses($this->request->post['email']);
		    $mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		    $mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		    $mail->Send();

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('common/login', '', 'SSL'));
		}

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_forgotten'),
			'href'      => $this->url->link('common/forgotten', '', 'SSL'),
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_your_email'] = $this->language->get('text_your_email');
		$this->data['text_email'] = $this->language->get('text_email');

		$this->data['entry_email'] = $this->language->get('entry_email');

		$this->data['button_reset'] = $this->language->get('button_reset');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = $this->url->link('common/forgotten', '', 'SSL');

		$this->data['cancel'] = $this->url->link('common/login', '', 'SSL');

		if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
		} else {
      		$this->data['email'] = '';
    	}

		$this->template = 'common/forgotten.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!isset($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		} elseif (!$this->model_user_user->getTotalUsersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_email');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>