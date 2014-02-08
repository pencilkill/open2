<?php
class ModelSaleAffiliate extends Model {
	public function addAffiliate($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$salt = substr(md5(uniqid(rand(), true)), 0, 9);
		$this->db->set('salt', $salt);
		$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));

		unset($data['password']);

      	$this->db->insert('affiliate', $data);
	}

	public function editAffiliate($affiliate_id, $data) {
      	if (!empty($data['password'])) {
      		$salt = substr(md5(uniqid(rand(), true)), 0, 9);
			$this->db->set('salt', $salt);
			$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));

			unset($data['password']);
      	}

		$this->db->update('affiliate', $data, array('affiliate_id' => (int)$affiliate_id));
	}

	public function deleteAffiliate($affiliate_id) {
		$this->db->delete('affiliate', array('affiliate_id' => (int)$affiliate_id));
		$this->db->delete('affiliate_transaction', array('affiliate_id' => (int)$affiliate_id));
	}

	public function getAffiliate($affiliate_id) {
		$query = $this->db->distinct()->get_where('affiliate', array('affiliate_id' => (int)$affiliate_id));

		return $query->row;
	}

	public function getAffiliateByEmail($email) {
		$query = $this->db->distinct()->get_where('affiliate', array('LCASE(email)' => strtolower($email)));

		return $query->row;
	}

	public function getAffiliates($data = array()) {
		$s1 = $this->db->select('SUM(at.amount)')->from('affiliate_transaction')->where('at.affiliate_id = ' . $this->db->protect_identifiers('a.affiliate_id'))->group_by('at.affiliate_id')->select_string();
		$query = $this->db->select("*, CONCAT(a.firstname, ' ', a.lastname) AS name, ({$s1}) AS balance")->from('affiliate a');

		$implode = array();

		if (!empty($data['filter_name'])) {
			$query->like("LCASE(CONCAT(a.firstname, ' ', a.lastname))", utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_email'])) {
			$query->where('a.email', $data['filter_email']);
		}

		if (!empty($data['filter_code'])) {
			$query->where('a.code', $data['filter_code']);
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('a.status', (int)$data['filter_status']);
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$query->where('a.approved', (int)$data['filter_approved']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(a.date_added)', $data['filter_date_added']);
		}

		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.status',
			'a.approved',
			'a.date_added'
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

	public function approve($affiliate_id) {
		$affiliate_info = $this->getAffiliate($affiliate_id);

		if ($affiliate_info) {
			$this->db->update('affiliate', array('approved' => 1), array('affiliate_id' => (int)$affiliate_id));

			$this->load->language('mail/affiliate');

			$message  = sprintf($this->language->get('text_approve_welcome'), $this->config->get('config_name')) . "\n\n";
			$message .= $this->language->get('text_approve_login') . "\n";
			$message .= HTTP_CATALOG . 'index.php?route=affiliate/login' . "\n\n";
			$message .= $this->language->get('text_approve_services') . "\n\n";
			$message .= $this->language->get('text_approve_thanks') . "\n";
			$message .= $this->config->get('config_name');

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
			$mail->AddAddresses($affiliate_info['email']);
			$mail->Subject = html_entity_decode(sprintf($this->language->get('text_approve_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function getAffiliatesByNewsletter() {
		$query = $this->db->from('affiliate')->where('newsletter', 1)->order_by('firstname, lastname, email');

		return $query->get()->rows;
	}

	public function getTotalAffiliates($data = array()) {
		$query = $this->db->select('COUNT(*) AS total')->from('affiliate');

		if (!empty($data['filter_name'])) {
			$query->like("CONCAT(firstname, ' ', lastname)", $data['filter_name']);
		}

		if (!empty($data['filter_email'])) {
			$query->where('email', $data['filter_email']);
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('status', (int)$data['filter_status']);
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$query->where('approved', (int)$data['filter_approved']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(date_added)', $data['filter_date_added']);
		}

		return $query->get()->row['total'];
	}

	public function getTotalAffiliatesAwaitingApproval() {
      	$query = $this->db->select('COUNT(*) AS total')->from('affiliate')->where(array('status' => 0, 'approved' => 0));

		return $query->get()->row['total'];
	}

	public function getTotalAffiliatesByCountryId($country_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('affiliate')->where('country_id', (int)$country_id);

		return $query->get()->row['total'];
	}

	public function getTotalAffiliatesByZoneId($zone_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('affiliate')->where('zone_id', (int)$zone_id);

		return $query->get()->row['total'];
	}

	public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0) {
		$affiliate_info = $this->getAffiliate($affiliate_id);

		if ($affiliate_info) {
			$this->db->set('affiliate_id', $affiliate_id);
			$this->db->set('order_id', (float)$order_id);
			$this->db->set('description', $description);
			$this->db->set('amount', (float)$amount);
			$this->db->set('date_added', date('Y-m-d H:i:s'));
			$this->db->insert('affiliate_transaction');

			$this->language->load('mail/affiliate');

			$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($affiliate_id), $this->config->get('config_currency')));

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
			$mail->AddAddresses($affiliate_info['email']);
			$mail->Subject = html_entity_decode(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')), ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function deleteTransaction($order_id) {
		$this->db->delete('affiliate_transaction', array('order_id', (int)$order_id));
	}

	public function getTransactions($affiliate_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->select('affiliate_transaction')->where('affiliate_id', (int)$affiliate_id)->order_by('date_added', 'DESC')->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalTransactions($affiliate_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('affiliate_transaction')->where('affiliate_id'. (int)$affiliate_id);

		return $query->get()->row['total'];
	}

	public function getTransactionTotal($affiliate_id) {
		$query = $this->db->select('SUM(amount) AS total')->from('affiliate_transaction')->where('affiliate_id', (int)$affiliate_id);

		return $query->get()->row['total'];
	}

	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->select('COUNT(*) AS total')->from('affiliate_transaction')->where('order_id', (int)$order_id);

		return $query->get()->row['total'];
	}
}
?>