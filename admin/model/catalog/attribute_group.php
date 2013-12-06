<?php
class ModelCatalogAttributeGroup extends Model {
	public function addAttributeGroup($data) {
		$this->db->insert('attribute_group', $data);

		$attribute_group_id = $this->db->getLastId();

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$value['attribute_group_id'] = (int)$attribute_group_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('attribute_group_description', $value);
		}
	}

	public function editAttributeGroup($attribute_group_id, $data) {
		$this->db->update('attribute_group', $data, array('attribute_group_id' => (int)$attribute_group_id));

		$this->db->delete('attribute_group_description', array('attribute_group_id' => (int)$attribute_group_id));

		foreach ($data['attribute_group_description'] as $language_id => $value) {
			$value['attribute_group_id'] = (int)$attribute_group_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('attribute_group_description', $value);
		}
	}

	public function deleteAttributeGroup($attribute_group_id) {
		$this->db->delete('attribute_group', array('attribute_group_id' => (int)$attribute_group_id));
		$this->db->delete('attribute_group_description', array('attribute_group_id' => (int)$attribute_group_id));
	}

	public function getAttributeGroup($attribute_group_id) {
		$query = $this->db->get_where('attribute_group', array('attribute_group_id' => (int)$attribute_group_id));

		return $query->row;
	}

	public function getAttributeGroups($data = array()) {
		$query = $this->db->from('attribute_group ag')->join('attribute_group_description agd', 'ag.attribute_group_id = agd.attribute_group_id')->where('agd.language_id', (int)$this->config->get('config_language_id'));

		$sort_data = array(
			'agd.name',
			'ag.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'agd.name';
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

	public function getAttributeGroupDescriptions($attribute_group_id) {
		$attribute_group_data = array();

		$query = $this->db->get_where('attribute_group_description', array('attribute_group_id' => (int)$attribute_group_id));

		foreach ($query->rows as $result) {
			$attribute_group_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $attribute_group_data;
	}

	public function getTotalAttributeGroups() {
		return $this->db->count_all('attribute_group');
	}
}
?>