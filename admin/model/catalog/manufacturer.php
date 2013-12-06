<?php
class ModelCatalogManufacturer extends Model {
	public function addManufacturer($data) {
      	$this->db->insert('manufacturer', $data);

		$manufacturer_id = $this->db->getLastId();

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$value = array('manufacturer_id' => (int)$manufacturer_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('manufacturer_to_store', $value);
			}
		}

		if ($data['keyword']) {
			$this->db->set('query', 'manufacturer_id=' . (int)$manufacturer_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('manufacturer');
	}

	public function editManufacturer($manufacturer_id, $data) {
      	$this->db->update('manufacturer', $data, array('manufacturer_id' => (int)$manufacturer_id));

		$this->db->delete('manufacturer_to_store', array('manufacturer_id' => (int)$manufacturer_id));

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$value = array('manufacturer_id' => (int)$manufacturer_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('manufacturer_to_store', $value);
			}
		}

		$this->db->delete('url_alias', array('query' => 'manufacturer_id=' . (int)$manufacturer_id));

		if ($data['keyword']) {
			$this->db->set('query', 'manufacturer_id=' . (int)$manufacturer_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('manufacturer');
	}

	public function deleteManufacturer($manufacturer_id) {
		$this->db->delete('manufacturer', array('manufacturer_id' => (int)$manufacturer_id));
		$this->db->delete('manufacturer_to_store', array('manufacturer_id' => (int)$manufacturer_id));
		$this->db->delete('url_alias', array('query' => 'manufacturer_id=' . (int)$manufacturer_id));

		$this->cache->delete('manufacturer');
	}

	public function getManufacturer($manufacturer_id) {
		$s1 = $this->db->select('keyword')->from('url_alias')->where(array('query' => 'manufacturer_id=' . (int)$manufacturer_id))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS keyword')->get_where('manufacturer', array('manufacturer_id' => (int)$manufacturer_id));

		return $query->row;
	}

	public function getManufacturers($data = array()) {
		$query = $this->db->from('manufacturer');

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'sort_order';
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

	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();

		$query = $this->db->get_where('manufacturer_to_store', array('manufacturer_id' => (int)$manufacturer_id));

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}

	public function getTotalManufacturersByImageId($image_id) {
      	return $this->db->where('image_id', (int)$image_id)->count_all_results('manufacturer');
	}

	public function getTotalManufacturers() {
      	return $this->db->count_all('manufacturer');
	}
}
?>