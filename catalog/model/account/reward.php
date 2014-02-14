<?php
class ModelAccountReward extends Model {
	public function getRewards($data = array()) {
		$query = $this->db->from('customer_reward')->where(array('customer_id' => (int)$this->customer->getId()));

		$sort_data = array(
			'points',
			'description',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'date_added';
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

	public function getTotalRewards() {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('customer_reward', array('customer_id' => (int)$this->customer->getId()));

		return $query->row['total'];
	}

	public function getTotalPoints() {
		$query = $this->db->select('SUM(points) AS total')->from('customer_reward')->where(array('customer_id' => (int)$this->customer->getId()))->group_by('customer_id')->get();

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}
}
?>