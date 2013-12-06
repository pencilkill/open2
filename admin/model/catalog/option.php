<?php
class ModelCatalogOption extends Model {
	public function addOption($data) {
		$this->db->insert('option', $data);

		$option_id = $this->db->getLastId();

		foreach ($data['option_description'] as $language_id => $value) {
			$value['option_id'] = (int)$option_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('option_description', $value);
		}

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				$option_value['option_id'] = (int)$option_id;

				$this->db->insert('option_value', $option_value);

				$option_value_id = $this->db->getLastId();

				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
					$option_value_description['option_id'] = (int)$option_id;

					$option_value_description['language_id'] = (int)$language_id;

					$option_value_description['option_value_id'] = (int)$option_value_id;

					$this->db->insert('option_value_description', $option_value_description);
				}
			}
		}
	}

	public function editOption($option_id, $data) {
		$this->db->update('option', $data, array('option_id' => (int)$option_id));

		$this->db->delete('option_description', array('option_id' => (int)$option_id));

		foreach ($data['option_description'] as $language_id => $value) {
			$value['option_id'] = (int)$option_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('option_description', $value);
		}

		$this->db->delete('option_value', array('option_id' => (int)$option_id));
		$this->db->delete('option_value_description', array('option_id' => (int)$option_id));

		if (isset($data['option_value'])) {
			foreach ($data['option_value'] as $option_value) {
				$option_value['option_id'] = (int)$option_id;

				$this->db->insert('option_value', $option_value);

				$option_value_id = $this->db->getLastId();

				foreach ($option_value['option_value_description'] as $language_id => $option_value_description) {
					$option_value_description['option_id'] = (int)$option_id;

					$option_value_description['language_id'] = (int)$language_id;

					$option_value_description['option_value_id'] = (int)$option_value_id;

					$this->db->insert('option_value_description', $option_value_description);
				}
			}
		}
	}

	public function deleteOption($option_id) {
		$this->db->delete('option', array('option_id' => (int)$option_id));
		$this->db->delete('option_description', array('option_id' => (int)$option_id));
		$this->db->delete('option_value', array('option_id' => (int)$option_id));
		$this->db->delete('option_value_description', array('option_id' => (int)$option_id));
	}

	public function getOption($option_id) {
		$query = $this->db->from('option o')->join('option_description od', 'o.option_id = od.option_id')->where(array('o.option_id' => (int)$option_id, 'od.language_id' => (int)$this->config->get('config_language_id')))->get();

		return $query->row;
	}

	public function getOptions($data = array()) {
		$query = $this->db->from('option o')->join('option_description od', 'o.option_id = od.option_id')->where('od.language_id', (int)$this->config->get('config_language_id'));

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$query->like('LCASE(od.name)', utf8_strtolower($data['filter_name']));
		}

		$sort_data = array(
			'od.name',
			'o.type',
			'o.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'o.sort_order';
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

	public function getOptionDescriptions($option_id) {
		$option_data = array();

		$query = $this->db->get_where('option_description', array('option_id' => (int)$option_id));

		foreach ($query->rows as $result) {
			$option_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $option_data;
	}

	public function getOptionValues($option_id) {
		$option_value_data = array();

		$option_value_query = $this->db->from('option_value ov')->join('option_value_description ovd', 'ov.option_value_id = ovd.option_value_id')->where(array('ov.option_id' => (int)$option_id, 'ovd.language_id' => (int)$this->config->get('config_language_id')))->order_by('ov.sort_order', 'ASC')->get();

		foreach ($option_value_query->rows as $option_value) {
			$option_value_data[] = array(
				'option_value_id' => $option_value['option_value_id'],
				'name'            => $option_value['name'],
				'image'           => $option_value['image'],
				'sort_order'      => $option_value['sort_order']
			);
		}

		return $option_value_data;
	}

	public function getOptionValueDescriptions($option_id) {
		$option_value_data = array();

		$option_value_query = $this->db->get_where('option_value', array('option_id' => (int)$option_id));

		foreach ($option_value_query->rows as $option_value) {
			$option_value_description_data = array();

			$option_value_description_query = $this->db->get_where('option_value_description', array('option_value_id' => (int)$option_value['option_value_id']));

			foreach ($option_value_description_query->rows as $option_value_description) {
				$option_value_description_data[$option_value_description['language_id']] = array('name' => $option_value_description['name']);
			}

			$option_value_data[] = array(
				'option_value_id'          => $option_value['option_value_id'],
				'option_value_description' => $option_value_description_data,
				'image'                    => $option_value['image'],
				'sort_order'               => $option_value['sort_order']
			);
		}

		return $option_value_data;
	}

	public function getTotalOptions() {
      	return $this->db->count_all('option');
	}
}
?>