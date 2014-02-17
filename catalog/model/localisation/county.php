<?php
class ModelLocalisationCounty extends Model {
	public function getCounty($county_id) {
		$query = $this->db->get_where('county', array('county_id' => (int)$county_id, 'status' => 1));

		return $query->row;
	}

	public function getCountiesByZoneId($zone_id) {
		$county_data = $this->cache->get('county.' . (int)$zone_id);

		if (!$county_data) {
			$query = $this->db->from('county', array('zone_id' => (int)$zone_id, 'status' => 1))->order_by('sort_order', 'DESC')->get();

			$county_data = $query->rows;

			$this->cache->set('county.' . (int)$zone_id, $county_data);
		}

		return $county_data;
	}
}
?>