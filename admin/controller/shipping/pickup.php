<?php
class ControllerShippingPickup extends Controller {
	protected $preload_language = array('shipping/pickup');

	protected $preload_model = array('setting/setting', 'localisation/geo_zone');

	private $error = array(); 
	
	public function index() {   
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('pickup', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');
						
			$this->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
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
       		'text'      => $this->language->get('text_shipping'),
			'href'      => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('shipping/pickup', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
		
		$this->data['action'] = $this->url->link('shipping/pickup', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['pickup_geo_zone_id'])) {
			$this->data['pickup_geo_zone_id'] = $this->request->post['pickup_geo_zone_id'];
		} else {
			$this->data['pickup_geo_zone_id'] = $this->config->get('pickup_geo_zone_id');
		}
		
		if (isset($this->request->post['pickup_status'])) {
			$this->data['pickup_status'] = $this->request->post['pickup_status'];
		} else {
			$this->data['pickup_status'] = $this->config->get('pickup_status');
		}
		
		if (isset($this->request->post['pickup_sort_order'])) {
			$this->data['pickup_sort_order'] = $this->request->post['pickup_sort_order'];
		} else {
			$this->data['pickup_sort_order'] = $this->config->get('pickup_sort_order');
		}				
		
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
						
		$this->template = 'shipping/pickup.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/pickup')) {
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