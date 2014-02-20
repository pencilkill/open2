<?php
class ControllerLocalisationTaxRate extends Controller {
	protected $_language = array('localisation/tax_rate');

	protected $_model = array('localisation/tax_rate', 'sale/customer_group', 'localisation/geo_zone', 'localisation/tax_class');

	private $error = array();
 
	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->getList(); 
	}

	public function insert() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_tax_rate->addTaxRate($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function update() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_tax_rate->editTaxRate($this->request->get['tax_rate_id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getForm();
	}

	public function delete() {
		$this->document->setTitle($this->language->get('heading_title'));
 		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $tax_rate_id) {
				$this->model_localisation_tax_rate->deleteTaxRate($tax_rate_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

		$this->getList();
	}

	private function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'tr.name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
				
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
						
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),      		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);		
		
		$this->data['insert'] = $this->url->link('localisation/tax_rate/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('localisation/tax_rate/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');		
		
		$this->data['tax_rates'] = array();
		
		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		
		$tax_rate_total = $this->model_localisation_tax_rate->getTotalTaxRates();

		$results = $this->model_localisation_tax_rate->getTaxRates($data);

		foreach ($results as $result) {
			$action = array();
			
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('localisation/tax_rate/update', 'token=' . $this->session->data['token'] . '&tax_rate_id=' . $result['tax_rate_id'] . $url, 'SSL')
			);
											
			$this->data['tax_rates'][] = array(
				'tax_rate_id'   => $result['tax_rate_id'],
				'name'          => $result['name'],
				'rate'          => $result['rate'],
				'type'          => ($result['type'] == 'F' ? $this->language->get('text_amount') : $this->language->get('text_percent')),				
				'geo_zone'      => $result['geo_zone'],
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
				'selected'      => isset($this->request->post['selected']) && in_array($result['tax_rate_id'], $this->request->post['selected']),
				'action'        => $action				
			);
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		 
		$this->data['sort_name'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.name' . $url, 'SSL');
		$this->data['sort_rate'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.rate' . $url, 'SSL');
		$this->data['sort_type'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.type' . $url, 'SSL');
		$this->data['sort_geo_zone'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=gz.name' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.date_added' . $url, 'SSL');
		$this->data['sort_date_modified'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . '&sort=tr.date_modified' . $url, 'SSL');
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $tax_rate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;

		$this->template = 'localisation/tax_rate_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function getForm() {
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}

 		if (isset($this->error['rate'])) {
			$this->data['error_rate'] = $this->error['rate'];
		} else {
			$this->data['error_rate'] = '';
		}
				
		$url = '';
			
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
		
		if (!isset($this->request->get['tax_rate_id'])) {
			$this->data['action'] = $this->url->link('localisation/tax_rate/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('localisation/tax_rate/update', 'token=' . $this->session->data['token'] . '&tax_rate_id=' . $this->request->get['tax_rate_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('localisation/tax_rate', 'token=' . $this->session->data['token'] . $url, 'SSL');

		if (isset($this->request->get['tax_rate_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$tax_rate_info = $this->model_localisation_tax_rate->getTaxRate($this->request->get['tax_rate_id']);
		}

		if (isset($this->request->post['name'])) {
			$this->data['name'] = $this->request->post['name'];
		} elseif (!empty($tax_rate_info)) {
			$this->data['name'] = $tax_rate_info['name'];
		} else {
			$this->data['name'] = '';
		}
		
		if (isset($this->request->post['rate'])) {
			$this->data['rate'] = $this->request->post['rate'];
		} elseif (!empty($tax_rate_info)) {
			$this->data['rate'] = $tax_rate_info['rate'];
		} else {
			$this->data['rate'] = '';
		}
		
		if (isset($this->request->post['type'])) {
			$this->data['type'] = $this->request->post['type'];
		} elseif (!empty($tax_rate_info)) {
			$this->data['type'] = $tax_rate_info['type'];
		} else {
			$this->data['type'] = '';
		}
		
		if (isset($this->request->post['tax_rate_customer_group'])) {
			$this->data['tax_rate_customer_group'] = $this->request->post['tax_rate_customer_group'];
		} elseif (isset($this->request->get['tax_rate_id'])) {
			$this->data['tax_rate_customer_group'] = $this->model_localisation_tax_rate->getTaxRateCustomerGroups($this->request->get['tax_rate_id']);
		} else {
			$this->data['tax_rate_customer_group'] = array();
		}	
		
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
						
		if (isset($this->request->post['geo_zone_id'])) {
			$this->data['geo_zone_id'] = $this->request->post['geo_zone_id'];
		} elseif (!empty($tax_rate_info)) {
			$this->data['geo_zone_id'] = $tax_rate_info['geo_zone_id'];
		} else {
			$this->data['geo_zone_id'] = '';
		}
				
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
				
		$this->template = 'localisation/tax_rate_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!$this->request->post['rate']) {
			$this->error['rate'] = $this->language->get('error_rate');
		}
								
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/tax_rate')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		foreach ($this->request->post['selected'] as $tax_rate_id) {
			$tax_rule_total = $this->model_localisation_tax_class->getTotalTaxRulesByTaxRateId($tax_rate_id);

			if ($tax_rule_total) {
				$this->error['warning'] = sprintf($this->language->get('error_tax_rule'), $tax_rule_total);
			}
		}
				
		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}	
}
?>