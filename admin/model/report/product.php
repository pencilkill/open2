<?php
class ModelReportProduct extends Model {
	public function getProductsViewed($data = array()) {
		$query = $this->db->select('pd.name, p.model, p.viewed')->from('product p')->join('product_description pd', 'p.product_id = pd.product_id')->where(array('pd.language_id' => (int)$this->config->get('config_language_id'), 'p.viewed >' => 0))->order_by('p.viewed', 'DESC');

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

	public function getTotalProductsViewed() {
      	$query = $this->db->where('viewed > ', 0)->count_all_results('product');

		return $query->row['total'];
	}

	public function getTotalProductViews() {
      	$query = $this->db->select('SUM(viewed) AS total')->from('product');

		return $query->get()->row['total'];
	}

	public function reset() {
		$this->db->update('product', array('viewed' => '0'));
	}

	public function getPurchased($data = array()) {
		$query = $this->db->select('op.name, op.model, SUM(op.quantity) AS quantity, SUM(op.total + op.total * op.tax / 100) AS total')
			->from('order_product op')
			->join('order o', 'op.order_id = o.order_id');

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

		$query->group_by('op.model');

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

	public function getTotalPurchased($data) {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('op.model') . ') AS total', false)->from('order_product op')->join('order o', 'op.order_id = o.order_id');

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
}
?>