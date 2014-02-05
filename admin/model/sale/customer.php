<?php
class ModelSaleCustomer extends Model {
	public function addCustomer($data) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);

		$this->db->set('salt', $salt);
		$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));
		$this->db->set('date_added', date('Y-m-d H:i:s'));

      	$this->db->insert('customer', $data);

      	$customer_id = $this->db->getLastId();

      	if (isset($data['address'])) {
      		foreach ($data['address'] as $address) {
      			$address['customer_id'] = (int)$customer_id;

      			$this->db->insert('address', $address);

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->update('customer', array('address_id' => $address_id), array('customer_id' => (int)$customer_id));
				}
			}
		}
	}

	public function editCustomer($customer_id, $data) {
		$this->db->update('customer', $data, array('customer_id' => (int)$customer_id));

      	if ($data['password']) {
      		$salt = substr(md5(uniqid(rand(), true)), 0, 9);

        	$this->db->update('customer', array('salt' => $salt, 'password' => sha1($salt . sha1($salt . sha1($data['password'])))), array('customer_id' => (int)$customer_id));
      	}

      	$this->db->delete('address', array('customer_id' => (int)$customer_id));

      	if (isset($data['address'])) {
      		foreach ($data['address'] as $address) {
				$this->db->insert('address', $address);

				if (isset($address['default'])) {
					$address_id = $this->db->getLastId();

					$this->db->update('customer', array('address_id' => $address_id), array('customer_id' => (int)$customer_id));
				}
			}
		}
	}

	public function editToken($customer_id, $token) {
		$this->db->update('customer',  array('token' => $token), array('customer_id' => (int)$customer_id));
	}

	public function deleteCustomer($customer_id) {
      	$this->db->delete('customer', array('customer_id' => (int)$customer_id));
      	$this->db->delete('customer_reward', array('customer_id' => (int)$customer_id));
      	$this->db->delete('customer_transaction', array('customer_id' => (int)$customer_id));
      	$this->db->delete('customer_ip', array('customer_id' => (int)$customer_id));
      	$this->db->delete('address', array('customer_id' => (int)$customer_id));
	}

	public function getCustomer($customer_id) {
		$query = $this->db->distinct()->get_where('customer', array('customer_id' => (int)$customer_id));

		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->distinct()->get_where('customer', array('LCASE(email)' => strtolower($email)));

		return $query->row;
	}

	public function getCustomers($data = array()) {
		if (!empty($data['filter_ip'])) {
			$s1 = $this->db->select('customer_id')->from('customer_ip')->like('ip', $data['filter_ip'])->select_string();
		}

		$query = $this->db->select("*, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group", false)->from('customer c')
			->join('customer_group_description cgd', 'c.customer_group_id = cgd.customer_group_id')
			->where('cgd.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_name'])) {
			$query->like("LCASE(CONCAT(c.firstname, ' ', c.lastname))", utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_email'])) {
			$query->like('LCASE(c.email)', utf8_strtolower($data['filter_email']));
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$query->where('c.newsletter', (int)$data['filter_newsletter']);
		}

		if (!empty($data['filter_customer_group_id'])) {
			$query->where('c.customer_group_id', (int)$data['filter_customer_group_id']);
		}

		if (!empty($data['filter_ip'])) {
			$query->where("c.customer_id IN ({$s1})");
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('c.status', (int)$data['filter_status']);
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$query->where('c.approved', (int)$data['filter_approved']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(c.date_added)', date('Y-m-d', strtotime($data['filter_date_added'])));
		}

		$sort_data = array(
			'name',
			'c.email',
			'customer_group',
			'c.status',
			'c.approved',
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

	public function approve($customer_id) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$this->db->update('customer', array('approved' => 1), array('customer_id' => (int)$customer_id));

			$this->load->language('mail/customer');

			$this->load->model('setting/store');

			$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

			if ($store_info) {
				$store_name = $store_info['name'];
				$store_url = $store_info['url'] . 'index.php?route=account/login';
			} else {
				$store_name = $this->config->get('config_name');
				$store_url = HTTP_CATALOG . 'index.php?route=account/login';
			}

			$message  = sprintf($this->language->get('text_approve_welcome'), $store_name) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= $store_url . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $store_name;

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $store_name);
			$mail->AddAddresses($customer_info['email']);
			$mail->Subject = html_entity_decode(sprintf($this->language->get('text_approve_subject'), $store_name), ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function getAddress($address_id) {
		$address_query = $this->db->get_where('address', array('address_id' => (int)$address_id));

		if ($address_query->num_rows) {
			$country_query = $this->db->get_where('country', array('country_id' => (int)$address_query->row['country_id']));

			if ($country_query->num_rows) {
				$country = $country_query->row['name'];
				$iso_code_2 = $country_query->row['iso_code_2'];
				$iso_code_3 = $country_query->row['iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->get_where('zone', array('zone_id' => (int)$address_query->row['zone_id']));

			if ($zone_query->num_rows) {
				$zone = $zone_query->row['name'];
				$zone_code = $zone_query->row['code'];
			} else {
				$zone = '';
				$zone_code = '';
			}

			return array(
				'address_id'     => $address_query->row['address_id'],
				'customer_id'    => $address_query->row['customer_id'],
				'firstname'      => $address_query->row['firstname'],
				'lastname'       => $address_query->row['lastname'],
				'company'        => $address_query->row['company'],
				'company_id'     => $address_query->row['company_id'],
				'tax_id'         => $address_query->row['tax_id'],
				'address_1'      => $address_query->row['address_1'],
				'address_2'      => $address_query->row['address_2'],
				'postcode'       => $address_query->row['postcode'],
				'city'           => $address_query->row['city'],
				'zone_id'        => $address_query->row['zone_id'],
				'zone'           => $zone,
				'zone_code'      => $zone_code,
				'country_id'     => $address_query->row['country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		}
	}

	public function getAddresses($customer_id) {
		$address_data = array();

		$query = $this->db->select('address_id')->get_where('address', array('customer_id' => (int)$customer_id));

		foreach ($query->rows as $result) {
			$address_info = $this->getAddress($result['address_id']);

			if ($address_info) {
				$address_data[$result['address_id']] = $address_info;
			}
		}

		return $address_data;
	}

	public function getTotalCustomers($data = array()) {
		if (!empty($data['filter_ip'])) {
			$s1 = $this->db->select('customer_id')->from('customer_ip')->where('ip', $data['filter_ip'])->select_string();
		}

		$query = $this->db->select('COUNT(*) AS total')->from('customer');

		if (!empty($data['filter_name'])) {
			$query->like("LCASE(CONCAT(firstname, ' ', lastname))", utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_email'])) {
			$query->like('LCASE(email)', utf8_strtolower($data['filter_email']));
		}

		if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
			$query->where('newsletter', (int)$data['filter_newsletter']);
		}

		if (!empty($data['filter_customer_group_id'])) {
			$query->where('customer_group_id', (int)$data['filter_customer_group_id']);
		}

		if (!empty($data['filter_ip'])) {
			$query->where("customer_id IN ({$s1})");
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('status', (int)$data['filter_status']);
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$query->where('approved', (int)$data['filter_approved']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(date_added)', date('Y-m-d', strtotime($data['filter_date_added'])));
		}

		return $query->get()->row['total'];
	}

	public function getTotalCustomersAwaitingApproval() {
      	$query = $this->db->select('COUNT(*) AS total')->from('customer')->where('status', 0)->or_where('approved', 0);

		return $query->get()->row['total'];
	}

	public function getTotalAddressesByCustomerId($customer_id) {
      	$query = $this->db->select('COUNT(*) AS total')->from('address')->where('customer_id', (int)$customer_id);

		return $query->get()->row['total'];
	}

	public function getTotalAddressesByCountryId($country_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('address')->where('country_id', (int)$country_id);

		return $query->get()->row['total'];
	}

	public function getTotalAddressesByZoneId($zone_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('address')->where('zone_id', (int)$zone_id);

		return $query->get()->row['total'];
	}

	public function getTotalCustomersByCustomerGroupId($customer_group_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer')->where('customer_group_id', (int)$customer_group_id);

		return $query->get()->row['total'];
	}

	public function addTransaction($customer_id, $description = '', $amount = '', $order_id = 0) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$this->db->insert('customer_transaction', array('customer_id' => (int)$customer_id, 'order_id' => (int)$order_id, 'description' => $description, 'amount' => (float)$amount, 'date_added' => date('Y-m-d H:i:s')));

			$this->language->load('mail/customer');

			if ($customer_info['store_id']) {
				$this->load->model('setting/store');

				$store_info = $this->model_setting_store->getStore($customer_info['store_id']);

				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
				}
			} else {
				$store_name = $this->config->get('config_name');
			}

			$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($customer_id)));

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $store_name);
			$mail->AddAddresses($customer_info['email']);
			$mail->Subject = html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function deleteTransaction($order_id) {
		$this->db->delete('customer_transaction', array('order_id' => (int)$order_id));
	}

	public function getTransactions($customer_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->from('customer_transaction')->where('customer_id', (int)$customer_id)->order_by('date_added', 'DESC')->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalTransactions($customer_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_transaction')->where('customer_id', (int)$customer_id);

		return $query->get()->row['total'];
	}

	public function getTransactionTotal($customer_id) {
		$query = $this->db->select('SUM(amount) AS total')->from('customer_transaction')->where('customer_id', (int)$customer_id);

		return $query->get()->row['total'];
	}

	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_transaction')->where('order_id', (int)$order_id);

		return $query->get()->row['total'];
	}

	public function addReward($customer_id, $description = '', $points = '', $order_id = 0) {
		$customer_info = $this->getCustomer($customer_id);

		if ($customer_info) {
			$this->db->insert('customer_reward', array('customer_id' => (int)$customer_id, 'order_id' => (int)$order_id, 'points' => (int)$points, 'description' => $description, 'date_added' => date('Y-m-d H:i:s')));

			$this->language->load('mail/customer');

			if ($order_id) {
				$this->load->model('sale/order');

				$order_info = $this->model_sale_order->getOrder($order_id);

				if ($order_info) {
					$store_name = $order_info['store_name'];
				} else {
					$store_name = $this->config->get('config_name');
				}
			} else {
				$store_name = $this->config->get('config_name');
			}

			$message  = sprintf($this->language->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->language->get('text_reward_total'), $this->getRewardTotal($customer_id));

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $store_name);
			$mail->AddAddresses($customer_info['email']);
			$mail->Subject = html_entity_decode(sprintf($this->language->get('text_reward_subject'), $store_name), ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function deleteReward($order_id) {
		$this->db->delete('customer_reward',  array('order_id' => (int)$order_id));
	}

	public function getRewards($customer_id, $start = 0, $limit = 10) {
		$query = $this->db->from('customer_reward')->where('customer_id', (int)$customer_id)->order_by('date_added', 'DESC')->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalRewards($customer_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_reward')->where('customer_id', (int)$customer_id);

		return $query->get()->row['total'];
	}

	public function getRewardTotal($customer_id) {
		$query = $this->db->select('SUM(points) AS total')->from('customer_reward')->where('customer_id', (int)$customer_id);

		return $query->get()->row['total'];
	}

	public function getTotalCustomerRewardsByOrderId($order_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_reward')->where('order_id', (int)$order_id);

		return $query->get()->row['total'];
	}

	public function getIpsByCustomerId($customer_id) {
		$query = $this->db->from('customer_ip')->where('customer_id', (int)$customer_id);

		return $query->get()->rows;
	}

	public function getTotalCustomersByIp($ip) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_ip')->where('ip', $ip);

		return $query->get()->row['total'];
	}

	public function addBlacklist($ip) {
		$this->db->insert('customer_ip_blacklist', array('ip' => $ip));
	}

	public function deleteBlacklist($ip) {
		$this->db->delete('customer_ip_blacklist')->where('ip', $ip);
	}

	public function getTotalBlacklistsByIp($ip) {
      	$query = $this->db->select('COUNT(*) AS total')->from('customer_ip_blacklist')->where('ip', $ip);

		return $query->get()->row['total'];
	}
}
?>