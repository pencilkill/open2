<?php
class ModelLocalisationWeightClass extends Model {
	public function addWeightClass($data) {
		$this->db->insert('weight_class', $data);

		$weight_class_id = $this->db->getLastId();

		foreach ($data['weight_class_description'] as $language_id => $value) {
			$value['weight_class_id'] = (int)$weight_class_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('weight_class_description', $data);
		}

		$this->cache->delete('weight_class');
	}

	public function editWeightClass($weight_class_id, $data) {
		$this->db->update('weight_class', $data, array('weight_class_id' => (int)$weight_class_id));

		$this->db->delete('weight_class_description', array('weight_class_id' => (int)$weight_class_id));

		foreach ($data['weight_class_description'] as $language_id => $value) {
			$value['weight_class_id'] = (int)$weight_class_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('weight_class_description', $value);
		}

		$this->cache->delete('weight_class');
	}

	public function deleteWeightClass($weight_class_id) {
		$this->db->delete('weight_class', array('weight_class_id' => (int)$weight_class_id));
		$this->db->delete('weight_class_description', array('weight_class_id' => (int)$weight_class_id));

		$this->cache->delete('weight_class');
	}

	public function getWeightClasses($data = array()) {
		if ($data) {
			$query = $this->db->from('weight_class wc')->join('weight_class_description wcd', 'wc.weight_class_id = wcd.weight_class_id')->where('wcd.language_id', (int)$this->config->get('config_language_id'));

			$sort_data = array(
				'title',
				'unit',
				'value'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'title';
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
		} else {
			$weight_class_data = $this->cache->get('weight_class.' . (int)$this->config->get('config_language_id'));

			if (!$weight_class_data) {
				$query = $this->db->from('weight_class wc')->join('weight_class_description wcd', 'wc.weight_class_id = wcd.weight_class_id')->where('wcd.language_id', (int)$this->config->get('config_language_id'))->get();

				$weight_class_data = $query->rows;

				$this->cache->set('weight_class.' . (int)$this->config->get('config_language_id'), $weight_class_data);
			}

			return $weight_class_data;
		}
	}

	public function getWeightClass($weight_class_id) {
		$query = $this->db->from('weight_class wc')->join('weight_class_description wcd', 'wc.weight_class_id = wcd.weight_class_id')->where(array('wc.weight_class_id' => (int)$weight_class_id, 'wcd.language_id' => (int)$this->config->get('config_language_id')))->get();

		return $query->row;
	}

	public function getWeightClassDescriptionByUnit($unit) {
		$query = $this->db->get_where('weight_class_description', array('unit' => $unit, 'language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getWeightClassDescriptions($weight_class_id) {
		$weight_class_data = array();

		$query = $this->db->get_where('weight_class_description', array('weight_class_id' => (int)$weight_class_id));

		foreach ($query->rows as $result) {
			$weight_class_data[$result['language_id']] = array(
				'title' => $result['title'],
				'unit'  => $result['unit']
			);
		}

		return $weight_class_data;
	}

	public function getTotalWeightClasses() {
      	return $this->db->count_all('weight_class');
	}
}
?>