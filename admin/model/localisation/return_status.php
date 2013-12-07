<?php
class ModelLocalisationReturnStatus extends Model {
	public function addReturnStatus($data) {
		foreach ($data['return_status'] as $language_id => $value) {
			if (isset($return_status_id)) {
				$value['return_status_id'] = (int)$return_status_id;

				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_status', $value);
			} else {
				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_status', $value);

				$return_status_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('return_status');
	}

	public function editReturnStatus($return_status_id, $data) {
		$this->db->delete('return_status', array('return_status_id' => (int)$return_status_id));

		foreach ($data['return_status'] as $language_id => $value) {
			$value['return_status_id'] = (int)$return_status_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('return_status', $value);
		}

		$this->cache->delete('return_status');
	}

	public function deleteReturnStatus($return_status_id) {
		$this->db->delete('return_status', array('return_status_id' => (int)$return_status_id));

		$this->cache->delete('return_status');
	}

	public function getReturnStatus($return_status_id) {
		$query = $this->db->get_where('return_status', array('return_status_id' => (int)$return_status_id, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getReturnStatuses($data = array()) {
      	if ($data) {
      		$query = $this->db->from('return_status')->where('language_id', (int)$this->config->get('config_language_id'));

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
			$return_status_data = $this->cache->get('return_status.' . (int)$this->config->get('config_language_id'));

			if (!$return_status_data) {
				$query = $this->db->select('return_status_id, name')->from('return_status')->where('language_id', (int)$this->config->get('config_language_id'))->order_by('name', 'ASC')->get();

				$return_status_data = $query->rows;

				$this->cache->set('return_status.' . (int)$this->config->get('config_language_id'), $return_status_data);
			}

			return $return_status_data;
		}
	}

	public function getReturnStatusDescriptions($return_status_id) {
		$return_status_data = array();

		$query = $this->db->get_where('return_status', array('return_status_id' => (int)$return_status_id));

		foreach ($query->rows as $result) {
			$return_status_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $return_status_data;
	}

	public function getTotalReturnStatuses() {
		return $this->db->where('language_id', (int)$this->config->get('config_language_id'))->count_all_results('return_status');
	}
}
?>