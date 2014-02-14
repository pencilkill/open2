<?php
class ModelAccountReturn extends Model {
	public function addReturn($data) {
		$this->db->set('customer_id', (int)$this->customer->getId());
		$this->db->set('return_status_id', (int)$this->config->get('config_return_status_id'));
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

		$this->db->insert('return', $data);
	}

	public function getReturn($return_id) {
		$s1 = $this->db->select('rr.name')->from('return_reason rr')->where("rr.return_reason_id = r.return_reason_id AND rr.language_id = '" . (int)$this->config->get('config_language_id') . "'")->select_string();
		$s2 = $this->db->select('ra.name')->from('return_action ra')->where("ra.return_action_id = r.return_action_id AND ra.language_id = '" . (int)$this->config->get('config_language_id') . "'")->select_string();
		$s3 = $this->db->select('rs.name')->from('return_status rs')->where("rs.return_status_id = r.return_status_id AND rs.language_id = '" . (int)$this->config->get('config_language_id') . "'")->select_string();

		$query = $this->db->select("r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, ({$s1}) AS reason, ({$s2}) AS action, ({$s3}) AS status, r.comment, r.date_ordered, r.date_added, r.date_modified", false)
			->get_where('return r', array('return_id' => (int)$return_id, 'customer_id' => $this->customer->getId()));

		return $query->row;
	}

	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->select('r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added')
			->from('return r')
			->join('return_status rs', 'r.return_status_id = rs.return_status_id')
			->where(array('r.customer_id' => $this->customer->getId(), 'rs.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('r.return_id', 'DESC')
			->limit((int)$limit, (int)$start)
			->get();

		return $query->rows;
	}

	public function getTotalReturns() {
		$query = $this->db->select('COUNT(*) AS total')->get_where('return', array('customer_id' => $this->customer->getId()));

		return $query->row['total'];
	}

	public function getReturnHistories($return_id) {
		$query = $this->db->select('rh.date_added, rs.name AS status, rh.comment, rh.notify')
			->from('return_history rh')
			->join('return_status rs', 'rh.return_status_id = rs.return_status_id')
			->where(array('rh.return_id' => (int)$return_id, 'rs.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('rh.date_added', 'ASC')
			->get();

		return $query->rows;
	}
}
?>