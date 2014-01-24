<?php
class ModelReportSale extends Model {
	public function getOrders($data = array()) {
		$s1 = $this->db->select('SUM(op.quantity)')->from('order_product op')->where('op.order_id', $this->db->protect_identifiers('o.order_id'))->group_by('op.order_id')->select_string();

		$s2 = $this->db->select('SUM(ot.value)')->from('order_total ot')->where(array('ot.order_id' => $this->db->protect_identifiers('o.order_id'), 'ot.code' => 'tax'))->group_by('ot.order_id')->select_string();

		$s3 = $this->db->select('o.order_id, (' . $s1 . ') AS products, (' . $s2 . ') AS tax, o.total, o.date_added')->from('order o');

		if (!empty($data['filter_order_status_id'])) {
			$s3->where('o.order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$s3->where('o.order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$s3->where('DATE(o.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$s3->where('DATE(o.date_added) <= ', $data['filter_date_end']);
		}

		$s3 = $s3->group_by('o.order_id')->select_string();

		$query = $this->db->select('MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.order_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.tax) AS tax, SUM(tmp.total) AS total')->from('(' . $s3 . ') tmp');

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query->group_by('DAY(tmp.date_added)');
				break;
			default:
			case 'week':
				$query->group_by('WEEK(tmp.date_added)');
				break;
			case 'month':
				$query->group_by('MONTH(tmp.date_added)');
				break;
			case 'year':
				$query->group_by('YEAR(tmp.date_added)');
				break;
		}

		$query->order_by('tmp.date_added', 'DESC');

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
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query = $this->db->select('COUNT(DISTINCT ' . $this->protect_identifiers('DAY(date_added)') . ') AS total')->from('order');
				break;
			default:
			case 'week':
				$query = $this->db->select('COUNT(DISTINCT ' . $this->protect_identifiers('WEEK(date_added)') . ') AS total')->from('order');
				break;
			case 'month':
				$query = $this->db->select('COUNT(DISTINCT ' . $this->protect_identifiers('MONTH(date_added)') . ') AS total')->from('order');
				break;
			case 'year':
				$query = $this->db->select('COUNT(DISTINCT ' . $this->protect_identifiers('YEAR(date_added)') . ') AS total')->from('order');
				break;
		}

		if (!empty($data['filter_order_status_id'])) {
			$query->where('order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$query->where('order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(date_added) <= ', $data['filter_date_end']);
		}

		return $query->get()->row['total'];
	}

	public function getTaxes($data = array()) {
		$query = $this->db->select('MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS orders')
			->from('order_total ot')
			->join('order o', 'ot.order_id = o.order_id')
			->where('ot.code', 'tax');

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

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query->group_by('ot.title, DAY(o.date_added)');
				break;
			default:
			case 'week':
				$query->group_by('ot.title, WEEK(o.date_added)');
				break;
			case 'month':
				$query->group_by('ot.title, MONTH(o.date_added)');
				break;
			case 'year':
				$query->group_by('ot.title, YEAR(o.date_added)');
				break;
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

	public function getTotalTaxes($data = array()) {
		$s1 = $this->db->select('COUNT(*) AS total')
			->from('order_total ot')
			->join('order o', 'ot.order_id = o.order_id')
			->where('ot.code', 'tax');

		if (!empty($data['filter_order_status_id'])) {
			$s1->where('order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$s1->where('order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$s1->where('DATE(date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$s1->where('DATE(date_added) <= ', $data['filter_date_end']);
		}

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$s1->group_by('DAY(o.date_added), ot.title');
				break;
			default:
			case 'week':
				$s1->group_by('WEEK(o.date_added), ot.title');
				break;
			case 'month':
				$s1->group_by('MONTH(o.date_added), ot.title');
				break;
			case 'year':
				$s1->group_by('YEAR(o.date_added), ot.title');
				break;
		}

		$s1 = $s1->select_string();

		$query = $this->db->select('COUNT(*) AS total')->from('(' . $s1 . ') tmp');

		return $query->get()->row['total'];
	}

	public function getShipping($data = array()) {
		$query = $this->db->select('MIN(o.date_added) AS date_start, MAX(o.date_added) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.order_id) AS orders')
			->from('order_total ot')
			->join('order o', 'ot.order_id = o.order_id')
			->where('ot.code', 'shipping');

		if (!empty($data['filter_order_status_id'])) {
			$query->where('o.order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$query->where('o.order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(date_added) <= ', $data['filter_date_end']);
		}

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query->group_by('ot.title, DAY(o.date_added)');
				break;
			default:
			case 'week':
				$query->group_by('ot.title, WEEK(o.date_added)');
				break;
			case 'month':
				$query->group_by('ot.title, MONTH(o.date_added)');
				break;
			case 'year':
				$query->group_by('ot.title, YEAR(o.date_added)');
				break;
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

	public function getTotalShipping($data = array()) {
		$s1 = $this->db->select('COUNT(*) AS total')->from('order_total ot')
			->join('order o', 'ot.order_id = o.order_id')
			->where('ot.code', 'shipping');

		if (!empty($data['filter_order_status_id'])) {
			$s1->where('order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$s1->where('order_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$s1->where('DATE(date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$s1->where('DATE(date_added) <= ', $data['filter_date_end']);
		}

		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$s1->group_by('DAY(o.date_added), ot.title');
				break;
			default:
			case 'week':
				$s1->group_by('WEEK(o.date_added), ot.title');
				break;
			case 'month':
				$s1->group_by('MONTH(o.date_added), ot.title');
				break;
			case 'year':
				$s1->group_by('YEAR(o.date_added), ot.title');
				break;
		}

		$s1 = $s1->select_string();

		$query = $this->db->select('COUNT(*) AS total')->from('(' . $s1 . ') tmp');

		return $query->get()->row['total'];
	}
}
?>