<?php
class ModelReportReturn extends Model {
	public function getReturns($data = array()) {
		$query = $this->db->select('MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS returns')->from('return r');

		if (!empty($data['filter_return_status_id'])) {
			$query->where('r.return_status_id', (int)$data['filter_return_status_id']);
		} else {
			$query->where('r.return_status_id > ', 0);
		}

		if (!empty($data['filter_date_start'])) {
			$query->where('DATE(r.date_added) >= ', $data['filter_date_start']);
		}

		if (!empty($data['filter_date_end'])) {
			$query->where('DATE(r.date_added) <= ', $data['filter_date_end']);
		}

		if (isset($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query->where('DAY(r.date_added)');
				break;
			default:
			case 'week':
				$query->where('WEEK(r.date_added)');
				break;
			case 'month':
				$query->where('MONTH(r.date_added)');
				break;
			case 'year':
				$query->where('YEAR(r.date_added)');
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

	public function getTotalReturns($data = array()) {
		if (!empty($data['filter_group'])) {
			$group = $data['filter_group'];
		} else {
			$group = 'week';
		}

		switch($group) {
			case 'day';
				$query = $this->db->select("COUNT(DISTINCT DAY(" . $this->db->protect_identifiers('date_added') . ")) AS total", false)->from('return');
				break;
			default:
			case 'week':
				$query = $this->db->select("COUNT(DISTINCT WEEK(" . $this->db->protect_identifiers('date_added') . ")) AS total", false)->from('return');
				break;
			case 'month':
				$query = $this->db->select("COUNT(DISTINCT MONTH(" . $this->db->protect_identifiers('date_added') . ")) AS total", false)->from('return');
				break;
			case 'year':
				$query = $this->db->select("COUNT(DISTINCT YEAR(" . $this->db->protect_identifiers('date_added') . ")) AS total", false)->from('return');
				break;
		}

		if (!empty($data['filter_return_status_id'])) {
			$query->where('return_status_id', (int)$data['filter_return_status_id']);
		} else {
			$query->where('return_status_id > ', 0);
		}

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