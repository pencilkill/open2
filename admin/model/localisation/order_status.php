<?php
class ModelLocalisationOrderStatus extends Model {
	public function addOrderStatus($data) {
		foreach ($data['order_status'] as $language_id => $value) {
			if (isset($order_status_id)) {
				$value['order_status_id'] = (int)$order_status_id;

				$value['language_id'] = (int)$language_id;

				$this->db->insert('order_status', $value);
			} else {
				$value['language_id'] = (int)$language_id;

				$this->db->insert('order_status', $value);

				$order_status_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('order_status');
	}

	public function editOrderStatus($order_status_id, $data) {
		$this->db->delete('order_status', array('order_status_id' => (int)$order_status_id));

		foreach ($data['order_status'] as $language_id => $value) {
			$value['order_status_id'] = (int)$order_status_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('order_status', $value);
		}

		$this->cache->delete('order_status');
	}

	public function deleteOrderStatus($order_status_id) {
		$this->db->delete('order_status', array('order_status_id' => (int)$order_status_id));

		$this->cache->delete('order_status');
	}

	public function getOrderStatus($order_status_id) {
		$query = $this->db->get_where('order_status', array('order_status_id' => (int)$order_status_id, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getOrderStatuses($data = array()) {
      	if ($data) {
      		$query = $this->db->from('order_status')->where('language_id', (int)$this->config->get('config_language_id'));

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by('name', 'DESC');
			} else {
				$query->order_by('name', 'ASC');
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
		} else {
			$order_status_data = $this->cache->get('order_status.' . (int)$this->config->get('config_language_id'));

			if (!$order_status_data) {
				$query = $this->db->select('order_status_id, name')->from('order_status')->where('language_id', (int)$this->config->get('config_language_id'))->order_by('name', 'ASC')->get();

				$order_status_data = $query->rows;

				$this->cache->set('order_status.' . (int)$this->config->get('config_language_id'), $order_status_data);
			}

			return $order_status_data;
		}
	}

	public function getOrderStatusDescriptions($order_status_id) {
		$order_status_data = array();

		$query = $this->db->get_where('order_status', array('order_status_id' => (int)$order_status_id));

		foreach ($query->rows as $result) {
			$order_status_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $order_status_data;
	}

	public function getTotalOrderStatuses() {
		return $this->db->where('language_id', (int)$this->config->get('config_language_id'))->count_all_results('order_status');
	}
}
?>