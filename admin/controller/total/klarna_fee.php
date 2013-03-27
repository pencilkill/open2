<?php 
class ControllerTotalKlarnaFee extends Controller {
	protected $preload_language = array('total/klarna_fee');

	protected $preload_model = array('setting/setting', 'localisation/tax_class');
 
	private $error = array(); 
	 
	public function index() { 
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('klarna_fee', $this->request->post);
		
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

   		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_total'),
			'href'      => $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('total/klarna_fee', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('total/klarna_fee', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/total', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['klarna_fee_total'])) {
			$this->data['klarna_fee_total'] = $this->request->post['klarna_fee_total'];
		} else {
			$this->data['klarna_fee_total'] = $this->config->get('klarna_fee_total');
		}
		
		if (isset($this->request->post['klarna_fee_fee'])) {
			$this->data['klarna_fee_fee'] = $this->request->post['klarna_fee_fee'];
		} else {
			$this->data['klarna_fee_fee'] = $this->config->get('klarna_fee_fee');
		}
		
		if (isset($this->request->post['klarna_fee_tax_class_id'])) {
			$this->data['klarna_fee_tax_class_id'] = $this->request->post['klarna_fee_tax_class_id'];
		} else {
			$this->data['klarna_fee_tax_class_id'] = $this->config->get('klarna_fee_tax_class_id');
		}

		if (isset($this->request->post['klarna_fee_status'])) {
			$this->data['klarna_fee_status'] = $this->request->post['klarna_fee_status'];
		} else {
			$this->data['klarna_fee_status'] = $this->config->get('klarna_fee_status');
		}

		if (isset($this->request->post['klarna_fee_sort_order'])) {
			$this->data['klarna_fee_sort_order'] = $this->request->post['klarna_fee_sort_order'];
		} else {
			$this->data['klarna_fee_sort_order'] = $this->config->get('klarna_fee_sort_order');
		}
		
		$this->data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->template = 'total/klarna_fee.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'total/klarna_fee')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>