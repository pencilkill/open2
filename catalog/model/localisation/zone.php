<?php
class ModelLocalisationZone extends Model {
	public function getZone($zone_id) {
		$query = $this->db->get_where('zone', array('zone_id' => (int)$zone_id, 'status' => 1));

		return $query->row;
	}

	public function getZonesByCountryId($country_id) {
		$zone_data = $this->cache->get('zone.' . (int)$country_id);

		if (!$zone_data) {
			$query = $this->db->from('zone')->where(array('country_id' => (int)$country_id, 'status' => 1))->order_by('name', 'ASC')->get();

			$zone_data = $query->rows;

			$this->cache->set('zone.' . (int)$country_id, $zone_data);
		}

		return $zone_data;
	}
}
?>