<?php
class ModelLocalisationZone extends Model {
	public function addZone($data) {
		$this->db->insert('zone', $data);

		$this->cache->delete('zone');
	}

	public function editZone($zone_id, $data) {
		$this->db->update('zone', $data, array('zone_id' => (int)$zone_id));

		$this->cache->delete('zone');
	}

	public function deleteZone($zone_id) {
		$this->db->delete('zone', array('zone_id' => (int)$zone_id));

		$this->cache->delete('zone');
	}

	public function getZone($zone_id) {
		$query = $this->db->distinct()->get_where('zone', array('zone_id' => (int)$zone_id));

		return $query->row;
	}

	public function getZones($data = array()) {
		$query = $this->db->select('*, z.name, c.name')->from('zone z')->join('country c', 'z.country_id = c.country_id');

		$sort_data = array(
			'c.name',
			'z.name',
			'z.code'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'c.name';
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

	public function getZonesByCountryId($country_id) {
		$zone_data = $this->cache->get('zone.' . (int)$country_id);

		if (!$zone_data) {
			$query = $this->db->from('zone')->where(array('country_id' => (int)$country_id))->order_by('name', 'ASC');

			$zone_data = $query->rows;

			$this->cache->set('zone.' . (int)$country_id, $zone_data);
		}

		return $zone_data;
	}

	public function getTotalZones() {
		return $this->db->count_all('zone');
	}

	public function getTotalZonesByCountryId($country_id) {
		return $this->db->where(array('country_id' => (int)$country_id))->count_all_results('zone');
	}
}
?>