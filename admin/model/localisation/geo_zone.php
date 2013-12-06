<?php
class ModelLocalisationGeoZone extends Model {
	public function addGeoZone($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->insert('geo_zone', $data);

		$geo_zone_id = $this->db->getLastId();

		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$value['geo_zone_id'] = (int)$geo_zone_id;

				$value['date_added'] = date('Y-m-d H:i:s');

				$this->db->insert('zone_to_geo_zone', $value);
			}
		}

		$this->cache->delete('geo_zone');
	}

	public function editGeoZone($geo_zone_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('geo_zone', $data, array('geo_zone_id' => (int)$geo_zone_id));

		$this->db->delete('zone_to_geo_zone', array('geo_zone_id' => (int)$geo_zone_id));

		if (isset($data['zone_to_geo_zone'])) {
			foreach ($data['zone_to_geo_zone'] as $value) {
				$value['geo_zone_id'] = (int)$geo_zone_id;

				$value['date_added'] = date('Y-m-d H:i:s');

				$this->db->insert('zone_to_geo_zone', $value);
			}
		}

		$this->cache->delete('geo_zone');
	}

	public function deleteGeoZone($geo_zone_id) {
		$this->db->delete('geo_zone', array('geo_zone_id' => (int)$geo_zone_id));
		$this->db->delete('zone_to_geo_zone', array('geo_zone_id' => (int)$geo_zone_id));

		$this->cache->delete('geo_zone');
	}

	public function getGeoZone($geo_zone_id) {
		$query = $this->db->distinct()->get_where('geo_zone', array('geo_zone_id' => (int)$geo_zone_id));

		return $query->row;
	}

	public function getGeoZones($data = array()) {
		if ($data) {
			$query = $this->db->from('geo_zone');

			$sort_data = array(
				'name',
				'description'
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
			$geo_zone_data = $this->cache->get('geo_zone');

			if (!$geo_zone_data) {
				$query = $this->db->from('geo_zone')->order_by('name', 'ASC')->get();

				$geo_zone_data = $query->rows;

				$this->cache->set('geo_zone', $geo_zone_data);
			}

			return $geo_zone_data;
		}
	}

	public function getTotalGeoZones() {
		return $this->db->count_all('geo_zone');
	}

	public function getZoneToGeoZones($geo_zone_id) {
		$query = $this->db->get_where('zone_to_geo_zone', array('geo_zone_id' => (int)$geo_zone_id));

		return $query->rows;
	}

	public function getTotalZoneToGeoZoneByGeoZoneId($geo_zone_id) {
      	return $this->db->where(array('geo_zone_id' => (int)$geo_zone_id))->count_all_results('zone_to_geo_zone');
	}

	public function getTotalZoneToGeoZoneByCountryId($country_id) {
		return $this->db->where(array('country_id' => (int)$country_id))->count_all_results('zone_to_geo_zone');
	}

	public function getTotalZoneToGeoZoneByZoneId($zone_id) {
		return $this->db->where(array('zone_id' => (int)$zone_id))->count_all_results('zone_to_geo_zone');
	}
}
?>