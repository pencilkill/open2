<?php
class ModelLocalisationCounty extends Model {
	public function addCounty($data) {
		$this->db->insert('county', $data);

		$this->cache->delete('county');
	}

	public function editCounty($county_id, $data) {
		$this->db->update('county', $data, array('county_id' => (int)$county_id));

		$this->cache->delete('county');
	}

	public function deleteCounty($county_id) {
		$this->db->delete('county', array('county_id' => (int)$county_id));

		$this->cache->delete('county');
	}

	public function getCounty($county_id) {
		$query = $this->db->distinct()->get_where('county',  array('county_id' => (int)$county_id));

		return $query->row;
	}

	public function getCounties($data = array()) {
		$query = $this->db->select('*,' . $this->db->protect_identifiers('c.name, z.name AS zone'))->from('county c')->join('zone z', 'c.zone_id = z.zone_id');

		$sort_data = array(
			'z.sort_order',
			'c.sort_order',
			'c.postcode'
		);

		$quer->order_by('z.sort_order', 'ASC');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'c.sort_order';
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

	public function getCountiesByZoneId($zone_id) {
		$county_data = $this->cache->get('county.' . (int)$zone_id);

		if (!$county_data) {
			$query = $this->db->from('county')->where('zone_id' , (int)$zone_id)->order_by('sord_order', 'ASC');

			$county_data = $query->rows;

			$this->cache->set('county.' . (int)$zone_id, $county_data);
		}

		return $county_data;
	}

	public function getTotalZones() {
      	return $this->db->count_all('county');
	}

	public function getTotalCountiesByZoneId($zone_id) {
		return $this->db->where(array('county_id' => (int)$zone_id))->count_all_results('county');
	}
}
?>