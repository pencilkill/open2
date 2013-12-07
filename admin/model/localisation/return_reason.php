<?php
class ModelLocalisationReturnReason extends Model {
	public function addReturnReason($data) {
		foreach ($data['return_reason'] as $language_id => $value) {
			if (isset($return_reason_id)) {
				$value['return_reason_id'] = (int)$return_reason_id;

				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_reason', $value);
			} else {
				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_reason', $value);

				$return_reason_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('return_reason');
	}

	public function editReturnReason($return_reason_id, $data) {
		$this->db->delete('return_reason', array('return_reason_id' => (int)$return_reason_id));

		foreach ($data['return_reason'] as $language_id => $value) {
			$value['return_reason_id'] = (int)$return_reason_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('return_reason', $value);
		}

		$this->cache->delete('return_reason');
	}

	public function deleteReturnReason($return_reason_id) {
		$this->db->delete('return_reason', array('return_reason_id' => (int)$return_reason_id));

		$this->cache->delete('return_reason');
	}

	public function getReturnReason($return_reason_id) {
		$query = $this->db->get_where('return_reason', array('return_reason_id' => (int)$return_reason_id, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getReturnReasons($data = array()) {
      	if ($data) {
      		$query = $this->db->from('return_reason')->where('language_id', (int)$this->config->get('config_language_id'));

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
			$return_reason_data = $this->cache->get('return_reason.' . (int)$this->config->get('config_language_id'));

			if (!$return_reason_data) {
				$query = $this->db->select('return_reason_id, name')->from('return_reason')->where('language_id', (int)$this->config->get('config_language_id'))->order_by('name', 'ASC')->get();

				$return_reason_data = $query->rows;

				$this->cache->set('return_reason.' . (int)$this->config->get('config_language_id'), $return_reason_data);
			}

			return $return_reason_data;
		}
	}

	public function getReturnReasonDescriptions($return_reason_id) {
		$return_reason_data = array();

		$query = $this->db->get_where('return_reason', array('return_action_id' => (int)$return_action_id));

		foreach ($query->rows as $result) {
			$return_reason_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $return_reason_data;
	}

	public function getTotalReturnReasons() {
		return $this->db->where('language_id', (int)$this->config->get('config_language_id'))->count_all_results('return_reason');

		return $query->row['total'];
	}
}
?>