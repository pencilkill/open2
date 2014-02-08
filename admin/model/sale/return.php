<?php
class ModelSaleReturn extends Model {
	public function addReturn($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

      	$this->db->insert('return', $data);
	}

	public function editReturn($return_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

		$this->db->update('return', $data, array('return_id' => (int)$return_id));
	}

	public function editReturnAction($return_id, $return_action_id) {
		$this->db->update('return', array('return_action_id' => (int)$return_action_id), array('return_id' => (int)$return_id));
	}

	public function deleteReturn($return_id) {
		$this->db->delete('return', array('return_id' => (int)$return_id));
		$this->db->delete('return_history', array('return_id' => (int)$return_id));
	}

	public function getReturn($return_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = r.customer_id) AS customer FROM `" . DB_PREFIX . "return` r WHERE r.return_id = '" . (int)$return_id . "'");

		return $query->row;
	}

	public function getReturns($data = array()) {
		$s1 = $this->db->select('rs.name rs')->from('return_status rs')->where('rs.return_status_id = ' . $this->db->protect_identifiers('r.return_status_id'))->where('rs.language_id', (int)$this->config->get('config_language_id'))->select_string();

		$query = $this->db->select("*, CONCAT(r.firstname, ' ', r.lastname) AS customer, ({$s1}) AS status", false)->from('return r');

		if (!empty($data['filter_return_id'])) {
			$query->where('r.return_id', (int)$data['filter_return_id']);
		}

		if (!empty($data['filter_order_id'])) {
			$query->where('r.order_id', (int)$data['filter_order_id']);
		}

		if (!empty($data['filter_customer'])) {
			$query->like("LCASE(CONCAT(r.firstname, ' ', r.lastname))", utf8_strtolower($data['filter_customer']));
		}

		if (!empty($data['filter_product'])) {
			$query->where('r.product', $data['filter_product']);
		}

		if (!empty($data['filter_model'])) {
			$query->where('r.model', $data['filter_model']);
		}

		if (!empty($data['filter_return_status_id'])) {
			$query->where('r.return_status_id', (int)$data['filter_return_status_id']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(r.date_added)', $data['filter_date_added']);
		}

		if (!empty($data['filter_date_modified'])) {
			$query->where('DATE(r.date_modified)', $data['filter_date_modified']);
		}

		$sort_data = array(
			'r.return_id',
			'r.order_id',
			'customer',
			'r.product',
			'r.model',
			'status',
			'r.date_added',
			'r.date_modified'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'r.return_id';
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

	public function getTotalReturns($data = array()) {
		$query = $this->db->select('COUNT(*) AS total')->from('return r');

		if (!empty($data['filter_return_id'])) {
			$query->where('r.return_id', (int)$data['filter_return_id']);
		}

		if (!empty($data['filter_order_id'])) {
			$query->where('r.order_id', (int)$data['filter_order_id']);
		}

		if (!empty($data['filter_customer'])) {
			$query->like("LCASE(CONCAT(r.firstname, ' ', r.lastname))", utf8_strtolower($data['filter_customer']));
		}

		if (!empty($data['filter_product'])) {
			$query->where('r.product', $data['filter_product']);
		}

		if (!empty($data['filter_model'])) {
			$query->where('r.model', $data['filter_model']);
		}

		if (!empty($data['filter_return_status_id'])) {
			$query->where('r.return_status_id', (int)$data['filter_return_status_id']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(r.date_added)', $data['filter_date_added']);
		}

		if (!empty($data['filter_date_modified'])) {
			$query->where('DATE(r.date_modified)', $data['filter_date_modified']);
		}

		return $query->get()->row['total'];
	}

	public function getTotalReturnsByReturnStatusId($return_status_id) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('return', array('return_status_id' => (int)$return_status_id));

		return $query->row['total'];
	}

	public function getTotalReturnsByReturnReasonId($return_reason_id) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('return', array('return_reason_id' => (int)$return_reason_id));

		return $query->row['total'];
	}

	public function getTotalReturnsByReturnActionId($return_action_id) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('return', array('return_action_id' => (int)$return_action_id));

		return $query->row['total'];
	}

	public function addReturnHistory($return_id, $data) {
		$this->db->update('return', array('return_status_id' => (int)$data['return_status_id'], 'date_modified' => date('Y-m-d H:i:s')), array('return_id' => (int)$return_id));

		$this->db->set('return_id', (int)$return_id);
		$this->db->set('return_status_id', (int)$data['return_status_id']);
		$this->db->set('notify', (isset($data['notify']) ? (int)$data['notify'] : 0));
		$this->db->set('comment', strip_tags($data['comment']));
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$this->db->insert('return_history');

      	if ($data['notify']) {
        	$return_query = $this->db->select('*, rs.name AS status')
        		->from('return r')
        		->join('return_status rs', 'r.return_status_id = rs.return_status_id')
        		->where(array('r.return_id' => (int)$return_id, 'rs.language_id' => (int)$this->config->get('config_language_id')))
        		->get();

			if ($return_query->num_rows) {
				$this->language->load('mail/return');

				$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'), $return_id);

				$message  = $this->language->get('text_return_id') . ' ' . $return_id . "\n";
				$message .= $this->language->get('text_date_added') . ' ' . date($this->language->get('date_format_short'), strtotime($return_query->row['date_added'])) . "\n\n";
				$message .= $this->language->get('text_return_status') . "\n";
				$message .= $return_query->row['status'] . "\n\n";

				if ($data['comment']) {
					$message .= $this->language->get('text_comment') . "\n\n";
					$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
				}

				$message .= $this->language->get('text_footer');

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
				$mail->AddAddresses($return_query->row['email']);
	    		$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
	    		$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
	    		$mail->send();
			}
		}
	}

	public function getReturnHistories($return_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->select('rh.date_added, rs.name AS status, rh.comment, rh.notify')
			->from('return_history rh')
			->join('return_status rs', 'rh.return_status_id = rs.return_status_id')
			->where(array('rh.return_id' => (int)$return_id, 'rs.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('rh.date_added', 'ASC')
			->limit((int)$limit, (int)$start);

		return $query->rows;
	}

	public function getTotalReturnHistories($return_id) {
	  	$query = $this->db->select('COUNT(*) AS total')->get_where('return_history', array('return_id' => (int)$return_id));

		return $query->row['total'];
	}

	public function getTotalReturnHistoriesByReturnStatusId($return_status_id) {
		$query = $this->db->select('COUNT(*) AS total')->where('return_status_id', (int)$return_status_id)->group_by('return_id');

		return $query->get()->row['total'];
	}
}
?>