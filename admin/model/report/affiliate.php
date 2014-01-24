<?php
class ModelReportAffiliate extends Model {
	public function getCommission($data = array()) {
		$query = $this->db->select("at.affiliate_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total")
			->from('affiliate_transaction at')
			->join('affiliate a', 'at.affiliate_id = a.affiliate_id')
			->join('order o', 'at.order_id = o.order_id');


		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(at.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(at.date_added) <= ', $data['filter_date_end']);
		}

		$query->group_by('at.affiliate_id');

		$query->order_by('commission', 'DESC');

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

	public function getTotalCommission() {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('affiliate_id') . ') AS total', false)->from('affiliate_transaction');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(at.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(at.date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}

	public function getProducts($data = array()) {
		$query = $this->db->select("at.product_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.order_id) AS orders, SUM(o.total) AS total ")
			->from('affiliate_transaction at')
			->join('affiliate a', 'at.affiliate_id = a.affiliate_id')
			->join('order o', 'at.order_id = o.order_id')
			->join('product');	// TODO: raw file has no on condition ???

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(at.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(at.date_added) <= ', $data['filter_date_end']);
		}

		$query->group_by('at.affiliate_id');

		$query->order_by('commission', 'DESC');

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

	public function getTotalProducts() {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('product_id') . ') AS total', false)->from('affiliate_transaction');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(at.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(at.date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}
}
?>