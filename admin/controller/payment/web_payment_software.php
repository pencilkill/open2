<?php 
class ControllerPaymentWebPaymentSoftware extends Controller {
	protected $_language = array('payment/web_payment_software');

	protected $_model = array('setting/setting', 'localisation/order_status', 'localisation/geo_zone');

	private $error = array(); 

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->model_setting_setting->editSetting('web_payment_software', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['login'])) {
			$this->data['error_login'] = $this->error['login'];
		} else {
			$this->data['error_login'] = '';
		}

 		if (isset($this->error['key'])) {
			$this->data['error_key'] = $this->error['key'];
		} else {
			$this->data['error_key'] = '';
		}
		
		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/web_payment_software', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/web_payment_software&token=' . $this->session->data['token'];
		
		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];
		
		if (isset($this->request->post['web_payment_software_login'])) {
			$this->data['web_payment_software_merchant_name'] = $this->request->post['web_payment_software_merchant_name'];
		} else {
			$this->data['web_payment_software_merchant_name'] = $this->config->get('web_payment_software_merchant_name');
		}
	
		if (isset($this->request->post['web_payment_software_merchant_key'])) {
			$this->data['web_payment_software_merchant_key'] = $this->request->post['web_payment_software_merchant_key'];
		} else {
			$this->data['web_payment_software_merchant_key'] = $this->config->get('web_payment_software_merchant_key');
		}
		
		if (isset($this->request->post['web_payment_software_mode'])) {
			$this->data['web_payment_software_mode'] = $this->request->post['web_payment_software_mode'];
		} else {
			$this->data['web_payment_software_mode'] = $this->config->get('web_payment_software_mode');
		}
		
		if (isset($this->request->post['web_payment_software_method'])) {
			$this->data['web_payment_software_method'] = $this->request->post['web_payment_software_method'];
		} else {
			$this->data['web_payment_software_method'] = $this->config->get('web_payment_software_method');
		}
		
		if (isset($this->request->post['web_payment_software_order_status_id'])) {
			$this->data['web_payment_software_order_status_id'] = $this->request->post['web_payment_software_order_status_id'];
		} else {
			$this->data['web_payment_software_order_status_id'] = $this->config->get('web_payment_software_order_status_id'); 
		} 

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['web_payment_software_geo_zone_id'])) {
			$this->data['web_payment_software_geo_zone_id'] = $this->request->post['web_payment_software_geo_zone_id'];
		} else {
			$this->data['web_payment_software_geo_zone_id'] = $this->config->get('web_payment_software_geo_zone_id'); 
		} 
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['web_payment_software_status'])) {
			$this->data['web_payment_software_status'] = $this->request->post['web_payment_software_status'];
		} else {
			$this->data['web_payment_software_status'] = $this->config->get('web_payment_software_status');
		}
		
		if (isset($this->request->post['web_payment_software_total'])) {
			$this->data['web_payment_software_total'] = $this->request->post['web_payment_software_total'];
		} else {
			$this->data['web_payment_software_total'] = $this->config->get('web_payment_software_total');
		}
		
		if (isset($this->request->post['web_payment_software_sort_order'])) {
			$this->data['web_payment_software_sort_order'] = $this->request->post['web_payment_software_sort_order'];
		} else {
			$this->data['web_payment_software_sort_order'] = $this->config->get('web_payment_software_sort_order');
		}

		$this->template = 'payment/web_payment_software.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/web_payment_software')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['web_payment_software_merchant_name']) {
			$this->error['login'] = $this->language->get('error_login');
		}

		if (!$this->request->post['web_payment_software_merchant_key']) {
			$this->error['key'] = $this->language->get('error_key');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}