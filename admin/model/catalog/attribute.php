<?php
class ModelCatalogAttribute extends Model {
	public function addAttribute($data) {
		$this->db->insert('attribute', $data);

		$attribute_id = $this->db->getLastId();

		foreach ($data['attribute_description'] as $language_id => $value) {
			$value['attribute_id'] = (int)$attribute_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('attribute_description', $data);
		}
	}

	public function editAttribute($attribute_id, $data) {
		$this->db->update('attribute', $data, array('attribute_id' => (int)$attribute_id));

		$this->db->delete('attribute_description', array('attribute_id' => (int)$attribute_id));

		foreach ($data['attribute_description'] as $language_id => $value) {
			$value['attribute_id'] = (int)$attribute_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('attribute_description', $value);
		}
	}

	public function deleteAttribute($attribute_id) {
		$this->db->delete('attribute', array('attribute_id' => (int)$attribute_id));
		$this->db->delete('attribute_description', array('attribute_id' => (int)$attribute_id));
	}

	public function getAttribute($attribute_id) {
		$query = $this->db->get_where('attribute', array('attribute_id' => (int)$attribute_id));

		return $query->row;
	}

	public function getAttributes($data = array()) {
		$s1 = $this->db->select('agd.name')->from('attribute_group_description agd')->where('agd.attribute_group_id = ' . $this->db->protect_identifiers('a.attribute_group_id', true))->where('agd.language_id', (int)$this->config->get('config_language_id'))->select_string();

		$query = $this->db->select('*,(' . $s1 . ') AS attribute_group')->from('attribute a')->join('attribute_description ad', 'a.attribute_id = ad.attribute_id')->where('ad.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_name'])) {
			$query->like('LCASE(ad.name)', utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$query->where('a.attribute_group_id', (int)$data['filter_attribute_group_id']);
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		$query->order_by('attribute_group', 'ASC');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'ad.name';
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

	public function getAttributeDescriptions($attribute_id) {
		$attribute_data = array();

		$query = $this->db->get_where('attribute_description', array('attribute_id' => (int)$attribute_id));

		foreach ($query->rows as $result) {
			$attribute_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $attribute_data;
	}

	public function getAttributesByAttributeGroupId($data = array()) {
		$s1 = $this->db->select('agd.name')->from('attribute_group_description agd')->where('agd.attribute_group_id=' . $this->db->protect_identifiers('a.attribute_group_id'))->where('agd.language_id', (int)$this->config->get('config_language_id'));

		$query = $this->db->select('*,(' . $s1 . ') AS attribute_group')->from('attribute a')->join('attribute_description ad', 'a.attribute_id = ad.attribute_id')->where('ad.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_name'])) {
			$query->like('LCASE(ad.name)', utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$query->where('a.attribute_group_id', (int)$data['filter_attribute_group_id']);
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'ad.name';
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

	public function getTotalAttributes() {
		return $this->db->count_all('attribute');
	}

	public function getTotalAttributesByAttributeGroupId($attribute_group_id) {
      	return $this->db->where(array('attribute_group_id' => (int)$attribute_group_id))->count_all_results('attribute');
	}
}
?>