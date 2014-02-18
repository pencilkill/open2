<?php
class Customer {
	private $customer_id;
	private $firstname;
	private $lastname;
	private $email;
	private $telephone;
	private $fax;
	private $newsletter;
	private $customer_group_id;
	private $address_id;

  	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

		if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->get_where('customer', array('customer_id' => (int)$this->session->data['customer_id'], 'status' => 1));

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->firstname = $customer_query->row['firstname'];
				$this->lastname = $customer_query->row['lastname'];
				$this->email = $customer_query->row['email'];
				$this->telephone = $customer_query->row['telephone'];
				$this->fax = $customer_query->row['fax'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->address_id = $customer_query->row['address_id'];

      			$this->db->update('customer', array('cart' => isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : '', 'wishlist' => isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : '', 'ip' => $this->request->server['REMOTE_ADDR']), array('customer_id' => (int)$this->customer_id));

				$query = $this->db->get_where('customer_ip', array('customer_id' => (int)$this->session->data['customer_id'], 'ip' => $this->request->server['REMOTE_ADDR']));

				if (!$query->num_rows) {
					$this->db->insert('customer_ip', array('customer_id' => (int)$this->session->data['customer_id'], 'ip' => $this->request->server['REMOTE_ADDR'], 'date_added' => date('Y-m-d H:i:s')));
				}
			} else {
				$this->logout();
			}
  		}
	}

  	public function login($email, $password, $override = false) {
		if ($override) {
			$customer_query = $this->db->get_where('customer', array('LOWER(email)' => strtolower($email), 'status' => 1));
		} else {
			$customer_query = $this->db->from('customer')
				->where(array('LOWER(email)' => strtolower($email), 'status' => 1, 'approved' => 1))
				->where('(password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1(' . $this->db->escape($password) . '))))) OR password = ' . $this->db->escape(md5($password)) . ')')
				->get();
		}

		if ($customer_query->num_rows) {
			$this->session->data['customer_id'] = $customer_query->row['customer_id'];

			if ($customer_query->row['cart'] && is_string($customer_query->row['cart'])) {
				$cart = unserialize($customer_query->row['cart']);

				foreach ($cart as $key => $value) {
					if (!array_key_exists($key, $this->session->data['cart'])) {
						$this->session->data['cart'][$key] = $value;
					} else {
						$this->session->data['cart'][$key] += $value;
					}
				}
			}

			if ($customer_query->row['wishlist'] && is_string($customer_query->row['wishlist'])) {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$wishlist = unserialize($customer_query->row['wishlist']);

				foreach ($wishlist as $product_id) {
					if (!in_array($product_id, $this->session->data['wishlist'])) {
						$this->session->data['wishlist'][] = $product_id;
					}
				}
			}

			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
			$this->lastname = $customer_query->row['lastname'];
			$this->email = $customer_query->row['email'];
			$this->telephone = $customer_query->row['telephone'];
			$this->fax = $customer_query->row['fax'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->address_id = $customer_query->row['address_id'];

			$this->db->update('customer', array('ip' => $this->request->server['REMOTE_ADDR']), array('customer_id' => (int)$this->customer_id));

	  		return true;
    	} else {
      		return false;
    	}
  	}

	public function logout() {
		$this->db->update('customer', array('cart' => isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : '', 'wishlist' => isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : ''), array('customer_id' => (int)$this->customer_id));

		unset($this->session->data['customer_id']);

		$this->customer_id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->telephone = '';
		$this->fax = '';
		$this->newsletter = '';
		$this->customer_group_id = '';
		$this->address_id = '';
  	}

  	public function isLogged() {
    	return $this->customer_id;
  	}

  	public function getId() {
    	return $this->customer_id;
  	}

  	public function getFirstName() {
		return $this->firstname;
  	}

  	public function getLastName() {
		return $this->lastname;
  	}

  	public function getEmail() {
		return $this->email;
  	}

  	public function getTelephone() {
		return $this->telephone;
  	}

  	public function getFax() {
		return $this->fax;
  	}

  	public function getNewsletter() {
		return $this->newsletter;
  	}

  	public function getCustomerGroupId() {
		return $this->customer_group_id;
  	}

  	public function getAddressId() {
		return $this->address_id;
  	}

  	public function getBalance() {
		$query = $this->db->select('SUM(amount) AS total')->get_where('customer_transaction', array('customer_id' => (int)$this->customer_id));

		return $query->row['total'];
  	}

  	public function getRewardPoints() {
		$query = $this->db->select('SUM(points) AS total')->get_where('customer_reward', array('customer_id' => (int)$this->customer_id));

		return $query->row['total'];
  	}
}
?>