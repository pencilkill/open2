<?php
class ModelReportCustomer extends Model {
	public function getOrders($data = array()) {
		$s1 = $this->db->select('SUM(op.quantity)')->from('order_product op')->where('op.order_id = ', $this->protected_identifiers('o.order_id'))->group_by('op.order_id')->select_string();

		$s2 = $this->db->select("o.order_id, c.customer_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, o.email, cgd.name AS customer_group, c.status, (" . $s1 . ") AS products, o.total")
			->from('order o')
			->join('customer c', 'o.customer_id = c.customer_id')
			->join('customer_group_description cgd', 'c.customer_group_id = cgd.customer_group_id')
			->where(array('o.customer_id >' => 0, 'cgd.language_id' => (int)$this->config->get('config_language_id')));

		if (!empty($data['filter_order_status_id'])) {
			$s2->where('o.order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$s2->where('o.order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$s2->where('DATE(o.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$s2->where('DATE(o.date_added) <= ', $data['filter_date_end']);
		}


		$s2 = $s2->select_string();

		$query = $this->db->select('tmp.customer_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.order_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.total) AS total')->from($s2 . ' tmp');

		$query->group_by('tmp.customer_id');

		$query->order_by('total', 'DESC');

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

	public function getTotalOrders($data = array()) {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('o.customer_id') . ') AS total', false)->from('order o')->where('o.customer_id >', 0);

		if (!empty($data['filter_order_status_id'])) {
			$query->where('o.order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$query->where('o.order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(o.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(o.date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}

	public function getRewardPoints($data = array()) {
		$query = $this->db->select("cr.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(cr.points) AS points, COUNT(o.order_id) AS orders, SUM(o.total) AS total")
			->from('customer_reward cr')
			->join('customer c', 'cr.customer_id = c.customer_id')
			->join('customer_group_description cgd ', 'c.customer_group_id = cgd.customer_group_id')
			->join('order o', 'cr.order_id = o.order_id')
			->where('cgd.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(cr.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(cr.date_added) <= ', $data['filter_date_end']);
		}

		$query->group_by('cr.customer_id');

		$query->order_by('points', 'DESC');

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

	public function getTotalRewardPoints() {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('customer_id') . ') AS total', false)->from('customer_reward');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(cr.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(cr.date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}

	public function getCredit($data = array()) {
		$query = $this->db->select("ct.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, cgd.name AS customer_group, c.status, SUM(ct.amount) AS total")
			->from('customer_transaction ct')
			->join('customer c', 'ct.customer_id = c.customer_id')
			->join('customer_group_description cgd', 'c.customer_group_id = cgd.customer_group_id')
			->where('cgd.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(ct.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(ct.date_added) <= ', $data['filter_date_end']);
		}

		$query->group_by('ct.customer_id');

		$query->order_by('total', 'DESC');

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

	public function getTotalCredit() {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('customer_id') . ') AS total', false)->from('customer_transaction');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(cr.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(cr.date_added) <= ' , $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}
}
?>