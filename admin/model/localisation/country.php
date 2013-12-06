<?php
class ModelLocalisationCountry extends Model {
	public function addCountry($data) {
		$this->db->insert('country', $data);

		$this->cache->delete('country');
	}

	public function editCountry($country_id, $data) {
		$this->db->update('country', $data, array('country_id' => (int)$country_id));

		$this->cache->delete('country');
	}

	public function deleteCountry($country_id) {
		$this->db->delete('country', array('country_id' => (int)$country_id));

		$this->cache->delete('country');
	}

	public function getCountry($country_id) {
		$query = $this->db->distinct()->get_where('country', array('country_id' => (int)$country_id));

		return $query->row;
	}

	public function getCountries($data = array()) {
		if ($data) {
			$query = $this->db->from('country');

			$sort_data = array(
				'name',
				'iso_code_2',
				'iso_code_3'
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
			$country_data = $this->cache->get('country');

			if (!$country_data) {
				$query = $this->db->from('country')->order_by('name', 'ASC')->get();

				$country_data = $query->rows;

				$this->cache->set('country', $country_data);
			}

			return $country_data;
		}
	}

	public function getTotalCountries() {
      	return $this->db->count_all('country');
	}
}
?>