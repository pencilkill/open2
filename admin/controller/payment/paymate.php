<?php 
class ControllerPaymentPayMate extends Controller {
	protected $_language = array('payment/paymate');

	protected $_model = array('setting/setting', 'localisation/order_status', 'localisation/geo_zone');

	private $error = array(); 

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paymate', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['username'])) {
			$this->data['error_username'] = $this->error['username'];
		} else {
			$this->data['error_username'] = '';
		}
		
		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
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
			'href'      => $this->url->link('payment/paymate', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/paymate', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['paymate_username'])) {
			$this->data['paymate_username'] = $this->request->post['paymate_username'];
		} else {
			$this->data['paymate_username'] = $this->config->get('paymate_username');
		}
		
		if (isset($this->request->post['paymate_password'])) {
			$this->data['paymate_username'] = $this->request->post['paymate_username'];
		} elseif ($this->config->get('paymate_password')) {
			$this->data['paymate_password'] = $this->config->get('paymate_password');
		} else {
			$this->data['paymate_password'] = md5(mt_rand());
		}
				
		if (isset($this->request->post['paymate_test'])) {
			$this->data['paymate_test'] = $this->request->post['paymate_test'];
		} else {
			$this->data['paymate_test'] = $this->config->get('paymate_test');
		}
				
		if (isset($this->request->post['paymate_total'])) {
			$this->data['paymate_total'] = $this->request->post['paymate_total'];
		} else {
			$this->data['paymate_total'] = $this->config->get('paymate_total'); 
		} 
				
		if (isset($this->request->post['paymate_order_status_id'])) {
			$this->data['paymate_order_status_id'] = $this->request->post['paymate_order_status_id'];
		} else {
			$this->data['paymate_order_status_id'] = $this->config->get('paymate_order_status_id'); 
		} 
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['paymate_geo_zone_id'])) {
			$this->data['paymate_geo_zone_id'] = $this->request->post['paymate_geo_zone_id'];
		} else {
			$this->data['paymate_geo_zone_id'] = $this->config->get('paymate_geo_zone_id'); 
		} 
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['paymate_status'])) {
			$this->data['paymate_status'] = $this->request->post['paymate_status'];
		} else {
			$this->data['paymate_status'] = $this->config->get('paymate_status');
		}
		
		if (isset($this->request->post['paymate_sort_order'])) {
			$this->data['paymate_sort_order'] = $this->request->post['paymate_sort_order'];
		} else {
			$this->data['paymate_sort_order'] = $this->config->get('paymate_sort_order');
		}

		$this->template = 'payment/paymate.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/paymate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['paymate_username']) {
			$this->error['username'] = $this->language->get('error_username');
		}
		
		if (!$this->request->post['paymate_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}
						
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>
