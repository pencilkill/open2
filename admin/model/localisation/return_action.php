<?php
class ModelLocalisationReturnAction extends Model {
	public function addReturnAction($data) {
		foreach ($data['return_action'] as $language_id => $value) {
			if (isset($return_action_id)) {
				$value['return_action_id'] = (int)$return_action_id;

				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_action', $value);
			} else {
				$value['language_id'] = (int)$language_id;

				$this->db->insert('return_action', $value);

				$return_action_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('return_action');
	}

	public function editReturnAction($return_action_id, $data) {
		$this->db->delete('return_action', array('return_action_id' => (int)$return_action_id));

		foreach ($data['return_action'] as $language_id => $value) {
			$value['return_action_id'] = (int)$return_action_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('return_action', $value);
		}

		$this->cache->delete('return_action');
	}

	public function deleteReturnAction($return_action_id) {
		$this->db->delete('return_action', array('return_action_id' => (int)$return_action_id));

		$this->cache->delete('return_action');
	}

	public function getReturnAction($return_action_id) {
		$query = $this->db->get_where('return_action', array('return_action_id' => (int)$return_action_id, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getReturnActions($data = array()) {
      	if ($data) {
      		$query = $this->db->from('return_action')->where('language_id', (int)$this->config->get('config_language_id'));

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
			$return_action_data = $this->cache->get('return_action.' . (int)$this->config->get('config_language_id'));

			if (!$return_action_data) {
				$query = $this->db->select('return_action_id, name')->from('return_action')->where('language_id', (int)$this->config->get('config_language_id'))->order_by('name', 'ASC')->get();

				$return_action_data = $query->rows;

				$this->cache->set('return_action.' . (int)$this->config->get('config_language_id'), $return_action_data);
			}

			return $return_action_data;
		}
	}

	public function getReturnActionDescriptions($return_action_id) {
		$return_action_data = array();

		$query = $this->db->get_where('return_action', array('return_action_id' => (int)$return_action_id));

		foreach ($query->rows as $result) {
			$return_action_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $return_action_data;
	}

	public function getTotalReturnActions() {
		return $this->db->where('language_id', (int)$this->config->get('config_language_id'))->count_all_results('return_action');
	}
}
?>