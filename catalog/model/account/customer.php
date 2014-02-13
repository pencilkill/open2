<?php
class ModelAccountCustomer extends Model {
	public function addCustomer($data) {
		if (isset($data['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($data['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $data['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$this->load->model('account/customer_group');

		$customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

		//
		$this->db->set('store_id', (int)$this->config->get('config_store_id'));

		$salt = substr(md5(uniqid(rand(), true)), 0, 9);
		$this->db->set('salt', $salt);

		$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));
		unset($data['password']);

		$this->db->set('customer_group_id', $customer_group_id);

		$this->db->set('ip', $this->db->escape($this->request->server['REMOTE_ADDR']));

		$this->db->set('status', 1);

		$this->db->set('approved', (int)!$customer_group_info['approval']);

		$this->db->set('date_added', date('Y-m-d H:i:s'));

      	$this->db->insert('customer', $data);

		$customer_id = $this->db->getLastId();

		//
		$this->db->set('customer_id', (int)$customer_id);

      	$this->db->insert('address', $data);

		$address_id = $this->db->getLastId();

      	$this->db->update('customer', array('address_id' => (int)$address_id), array('customer_id', (int)$customer_id));

		$this->language->load('mail/customer');

		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

		$message = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";

		if (!$customer_group_info['approval']) {
			$message .= $this->language->get('text_login') . "\n";
		} else {
			$message .= $this->language->get('text_approval') . "\n";
		}

		$message .= $this->url->link('account/login', '', 'SSL') . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $this->config->get('config_name');

		$mail = new Mail();

		$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
		$mail->AddAddresses($data['email']);
		$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));

		// Send to main admin email if new account email is enabled
		if ($this->config->get('config_account_mail')) {
			$mail->AddAddresses($this->config->get('config_email'));

			// Send to additional alert emails if new account email is enabled
			$mail->AddAddresses($this->config->get('config_alert_emails'));
		}

		$mail->send();
	}

	public function editCustomer($data) {
		if(isset($data['password'])){
			$salt = substr(md5(uniqid(rand(), true)), 0, 9);

			$this->db->set('salt', $salt);
			$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));

			unset($data['password']);
		}

		$this->db->update('customer', $data, array('customer_id' => (int)$this->customer->getId()));
	}

	public function editPassword($email, $password) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);

		$this->db->set('salt', $salt);
		$this->db->set('password', sha1($salt . sha1($salt . sha1($password))));

      	$this->db->update('customer', NULL, array('email' => $email));
	}

	public function editNewsletter($newsletter) {
      	$this->db->update('customer', array('newsletter' => (int)$newsletter), array('customer_id' => (int)$this->customer->getId()));
	}

	public function getCustomer($customer_id) {
		$query = $this->db->get_where('customer', array('customer_id' => (int)$customer_id));

		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->get_where('customer', array('email' => $email));

		return $query->row;
	}

	public function getCustomerByToken($token) {
		$query = $this->db->get_where('customer', array('token' => $token, 'token != ' => ''));

		$this->db->update('customer', array('token' => ''));

		return $query->row;
	}

	public function getCustomers($data = array()) {
		$query = $this->db->select("*, CONCAT(c.firstname, ' ', c.lastname) AS name, cg.name AS customer_group", false)->from('customer c')->join('customer_group cg', 'c.customer_group_id = cg.customer_group_id');

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$query->like("LCASE(CONCAT(c.firstname, ' ', c.lastname))", utf8_strtolower($data['filter_name']));
		}

		if (isset($data['filter_email']) && !is_null($data['filter_email'])) {
			$query->where('c.email', $data['filter_email']);
		}

		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id'])) {
			$query->where('cg.customer_group_id', $data['filter_customer_group_id']);
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('c.status', (int)$data['filter_status']);
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$query->where('c.approved', (int)$data['filter_approved']);
		}

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$query->where("c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = " . $this->db->escape($data['filter_ip']) . ")", NULL, false);
		}

		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added'])) {
			$query->where('DATE(c.date_added)', $data['filter_date_added']);
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.ip',
			'c.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query->order_by($sort, 'DESC');
		} else {
			$query->order_by($sort, 'ASC');
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || (int)$data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || (int)$data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query->limit((int)$data['limit'], (int)$data['start']);
		}

		return $query->get()->rows;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('customer', array('LOWER(email)' => strtolower($email)));

		return $query->row['total'];
	}

	public function getIps($customer_id) {
		$query = $this->db->get_where('customer_ip', array('customer_id' => (int)$customer_id));

		return $query->rows;
	}

	public function isBlacklisted($ip) {
		$query = $this->db->get_where('customer_ip_blacklist', array('ip' => $ip));

		return $query->num_rows;
	}
}
?>