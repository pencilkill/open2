<?php
class ModelReportOnline extends Model {
	public function getCustomersOnline($data = array()) {
		$query = $this->db->select('co.ip, co.customer_id, co.url, co.referer, co.date_added')
			->from('customer_online co')
			->join('customer c', 'co.customer_id = c.customer_id');

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$query->like('co.ip', $data['filter_ip']);
		}

		if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
			$query->where('co.customer_id > ', 0);
			$query->like("CONCAT(c.firstname, ' ', c.lastname)", $data['filter_customer']);
		}

		$query->order_by('co.date_added', 'DESC');

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

	public function getTotalCustomersOnline($data = array()) {
		$query = $this->db->select('COUNT(*) AS total')->from('customer_online co')->join('customer c', 'co.customer_id = c.customer_id');

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$query->like('co.ip', $data['filter_ip']);
		}

		if (isset($data['filter_customer']) && !is_null($data['filter_customer'])) {
			$query->where('co.customer_id > ', 0);
			$query->like("CONCAT(c.firstname, ' ', c.lastname)", $data['filter_customer']);
		}

		return $query->get()->row['total'];
	}
}
?>