<?php
class ControllerPaymentKlarnaPP extends Controller {
	protected $preload_language = array('payment/klarna_pp');

	protected $preload_model = array('setting/setting', 'localisation/order_status', 'localisation/geo_zone');

    private $error = array();

    public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('klarna_pp', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} elseif (!function_exists('xmlrpc_encode_request')) {
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['merchant'])) {
			$this->data['error_merchant'] = $this->error['merchant'];
		} else {
			$this->data['error_merchant'] = '';
		}
		
 		if (isset($this->error['secret'])) {
			$this->data['error_secret'] = $this->error['secret'];
		} else {
			$this->data['error_secret'] = '';
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
			'href'      => $this->url->link('payment/klarna_pp', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/klarna_pp', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['klarna_pp_merchant'])) {
			$this->data['klarna_pp_merchant'] = $this->request->post['klarna_pp_merchant'];
		} else {
			$this->data['klarna_pp_merchant'] = $this->config->get('klarna_pp_merchant');
		}	
			
		if (isset($this->request->post['klarna_pp_secret'])) {
			$this->data['klarna_pp_secret'] = $this->request->post['klarna_pp_secret'];
		} else {
			$this->data['klarna_pp_secret'] = $this->config->get('klarna_pp_secret');
		}
		
		if (isset($this->request->post['klarna_pp_server'])) {
			$this->data['klarna_pp_server'] = $this->request->post['klarna_pp_server'];
		} else {
			$this->data['klarna_pp_server'] = $this->config->get('klarna_pp_server');
		}
		
		if (isset($this->request->post['klarna_pp_order_status_id'])) {
			$this->data['klarna_pp_order_status_id'] = $this->request->post['klarna_pp_order_status_id'];
		} else {
			$this->data['klarna_pp_order_status_id'] = $this->config->get('klarna_pp_order_status_id');
		}
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['klarna_pp_geo_zone_id'])) {
			$this->data['klarna_pp_geo_zone_id'] = $this->request->post['klarna_pp_geo_zone_id'];
		} else {
			$this->data['klarna_pp_geo_zone_id'] = $this->config->get('klarna_pp_geo_zone_id');
		}

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
				
		if (isset($this->request->post['klarna_pp_status'])) {
			$this->data['klarna_pp_status'] = $this->request->post['klarna_pp_status'];
		} else {
			$this->data['klarna_pp_status'] = $this->config->get('klarna_pp_status');
		}
		
		if (isset($this->request->post['klarna_pp_sort_order'])) {
			$this->data['klarna_pp_sort_order'] = $this->request->post['klarna_pp_sort_order'];
		} else {
			$this->data['klarna_pp_sort_order'] = $this->config->get('klarna_pp_sort_order');
		}
																				
        $this->template = 'payment/klarna_pp.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );
		
        $this->response->setOutput($this->render());
    }
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/klarna_pp')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['klarna_pp_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}
		
		if (!$this->request->post['klarna_pp_secret']) {
			$this->error['secret'] = $this->language->get('error_secret');
		}
								
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}