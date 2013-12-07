<?php
class ModelLocalisationStockStatus extends Model {
	public function addStockStatus($data) {
		foreach ($data['stock_status'] as $language_id => $value) {
			if (isset($stock_status_id)) {
				$value['stock_status_id'] = (int)$stock_status_id;

				$value['language_id'] = (int)$language_id;

				$this->db->insert('stock_status', $value);
			} else {
				$value['language_id'] = (int)$language_id;

				$this->db->insert('stock_status', $value);

				$stock_status_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('stock_status');
	}

	public function editStockStatus($stock_status_id, $data) {
		$this->db->delete('stock_status', array('stock_status_id' => (int)$stock_status_id));

		foreach ($data['stock_status'] as $language_id => $value) {
			$value['stock_status_id'] = (int)$stock_status_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('stock_status', $value);
		}

		$this->cache->delete('stock_status');
	}

	public function deleteStockStatus($stock_status_id) {
		$this->db->delete('stock_status', array('stock_status_id' => (int)$stock_status_id));

		$this->cache->delete('stock_status');
	}

	public function getStockStatus($stock_status_id) {
		$query = $this->db->get_where('stock_status', array('stock_status_id' => (int)$stock_status_id, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getStockStatuses($data = array()) {
		if ($data) {
			$query = $this->db->from('stock_status')->where('language_id', (int)$this->config->get('config_language_id'));

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
			$stock_status_data = $this->cache->get('stock_status.' . (int)$this->config->get('config_language_id'));

			if (!$stock_status_data) {
				$query = $this->db->select('stock_status_id, name')->from('stock_status')->where('language_id', (int)$this->config->get('config_language_id'))->order_by('name', 'ASC')->get();

				$stock_status_data = $query->rows;

				$this->cache->set('stock_status.' . (int)$this->config->get('config_language_id'), $stock_status_data);
			}

			return $stock_status_data;
		}
	}

	public function getStockStatusDescriptions($stock_status_id) {
		$stock_status_data = array();

		$query = $this->db->get_where('stock_status', array('stock_status_id' => (int)$stock_status_id));

		foreach ($query->rows as $result) {
			$stock_status_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $stock_status_data;
	}

	public function getTotalStockStatuses() {
		return $this->db->where('language_id', (int)$this->config->get('config_language_id'))->count_all_results('stock_status');
	}
}
?>