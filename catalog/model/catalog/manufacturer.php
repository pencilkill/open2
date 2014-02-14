<?php
class ModelCatalogManufacturer extends Model {
	public function getManufacturer($manufacturer_id) {
		$query = $this->db->from('manufacturer m')
			->join('manufacturer_to_store m2s', 'm.manufacturer_id = m2s.manufacturer_id')
			->where(array('m.manufacturer_id' => (int)$manufacturer_id, 'm2s.store_id' => (int)$this->config->get('config_store_id')))
			->get();

		return $query->row;
	}

	public function getManufacturers($data = array()) {
		if ($data) {
			$query = $this->db->from('manufacturer m')
				->join('manufacturer_to_store m2s', 'm.manufacturer_id = m2s.manufacturer_id')
				->where(array('m2s.store_id' => (int)$this->config->get('config_store_id')));


			$sort_data = array(
				'name',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'name';
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
			$manufacturer_data = $this->cache->get('manufacturer.' . (int)$this->config->get('config_store_id'));

			if (!$manufacturer_data) {
				$query = $this->db->from('manufacturer m')
					->join('manufacturer_to_store m2s', 'm.manufacturer_id = m2s.manufacturer_id')
					->where(array('m2s.store_id' => (int)$this->config->get('config_store_id')))
					->order_by('name', 'ASC')
					->get();

				$manufacturer_data = $query->rows;

				$this->cache->set('manufacturer.' . (int)$this->config->get('config_store_id'), $manufacturer_data);
			}

			return $manufacturer_data;
		}
	}
}
?>