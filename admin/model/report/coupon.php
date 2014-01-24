<?php
class ModelReportCoupon extends Model {
	public function getCoupons($data = array()) {
		$query = $this->db->select($this->db->protect_identifiers('ch.coupon_id, c.name, c.code') . ', ' . 'COUNT(DISTINCT ' . $this->db->protect_identifiers('ch.order_id') . ') AS ' . $this->db->protect_identifiers('orders') . ', ' . $this->db->protect_identifiers('SUM(ch.amount) AS total'), false)
			->from('coupon_history ch')
			->join('coupon c', 'ch.coupon_id = c.coupon_id');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(c.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(c.date_added) <= ', $data['filter_date_end']);
		}

		$query->group_by('ch.coupon_id');

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

	public function getTotalCoupons($data = array()) {
		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protected_identifiers('coupon_id') . ') AS total', false)->from('coupon_history');

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}
}
?>