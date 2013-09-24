<?php
class ModelLocalisationTwzone extends Model {
	public function getCity($city_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_city WHERE city_id = '" . (int)$city_id . "' AND status = '1'");

		return $query->row;
	}

	public function getCities() {
		$city_data = $this->cache->get('tw_city.' . (int)$city_id);

		if (!$city_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_city WHERE status = '1' ORDER BY sort_order DESC");

			$city_data = $query->rows;

			$this->cache->set('tw_city.' . (int)$city_id, $city_data);
		}

		return $city_data;
	}

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "' AND status = '1'");

		return $query->row;
	}

	public function getZonesByCityId($city_id) {
		$zone_data = $this->cache->get('tw_zone.' . (int)$city_id);

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_zone WHERE city_id = '" . (int)$city_id . "' AND status = '1' ORDER BY sort_order DESC");

			$zone_data = $query->rows;

			$this->cache->set('tw_zone.' . (int)$city_id, $zone_data);
		}

		return $zone_data;
	}
}
?>