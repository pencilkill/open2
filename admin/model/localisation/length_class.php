<?php
class ModelLocalisationLengthClass extends Model {
	public function addLengthClass($data) {
		$this->db->insert('length_class', $data);

		$length_class_id = $this->db->getLastId();

		foreach ($data['length_class_description'] as $language_id => $value) {
			$value['length_class_id'] = (int)$length_class_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('length_class', $value);
		}

		$this->cache->delete('length_class');
	}

	public function editLengthClass($length_class_id, $data) {
		$this->db->update('length_class', $data, array('length_class_id' => (int)$length_class_id));

		$this->db->delete('length_class', array('length_class_id' => (int)$length_class_id));

		foreach ($data['length_class_description'] as $language_id => $value) {
			$value['length_class_id'] = (int)$length_class_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('length_class', $value);
		}

		$this->cache->delete('length_class');
	}

	public function deleteLengthClass($length_class_id) {
		$this->db->delete('length_class', array('length_class_id' => (int)$length_class_id));
		$this->db->delete('length_class_description', array('length_class_id' => (int)$length_class_id));

		$this->cache->delete('length_class');
	}

	public function getLengthClasses($data = array()) {
		if ($data) {
			$query = $this->db->from('length_class lc')->join('length_class_description lcd', 'lc.length_class_id = lcd.length_class_id')->where('lcd.language_id', (int)$this->config->get('config_language_id'));

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
			$length_class_data = $this->cache->get('length_class.' . (int)$this->config->get('config_language_id'));

			if (!$length_class_data) {
				$query = $this->db->from('length_class lc')->join('length_class_description lcd', 'lc.length_class_id = lcd.length_class_id')->where('lcd.language_id', (int)$this->config->get('config_language_id'))->get();

				$length_class_data = $query->rows;

				$this->cache->set('length_class.' . (int)$this->config->get('config_language_id'), $length_class_data);
			}

			return $length_class_data;
		}
	}

	public function getLengthClass($length_class_id) {
		$query = $this->db->from('length_class lc')->join('length_class_description lcd', 'lc.length_class_id = lcd.length_class_id')->where(array('lcd.language_id' => (int)$this->config->get('config_language_id'), 'lc.length_class_id' => (int)$length_class_id))->get();

		return $query->row;
	}

	public function getLengthClassDescriptionByUnit($unit) {
		$query = $this->db->get_where('length_class_description', array('unit' => $unit, 'language_id', (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getLengthClassDescriptions($length_class_id) {
		$length_class_data = array();

		$query = $this->db->get_where('length_class_description', array('length_class_id' => (int)$length_class_id));

		foreach ($query->rows as $result) {
			$length_class_data[$result['language_id']] = array(
				'title' => $result['title'],
				'unit'  => $result['unit']
			);
		}

		return $length_class_data;
	}

	public function getTotalLengthClasses() {
		return $this->db->count_all('length_class');
	}
}
?>