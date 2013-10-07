<?php
class ModelLocalisationTwzone extends Model {
	public function getCities($country_id = 206) {
		$sql = "SELECT * FROM " . DB_PREFIX . "zone WHERE status = '1'";
		if(!empty($country_id)){
			$sql .= " AND country_id = '".$country_id."'";
		}
		$sql .= " ORDER BY zone_id ASC";

		$query = $this->db->query($sql);

		$city_data = $query->rows;

		return $city_data;
	}

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "' AND status = '1'");

		return $query->row;
	}

	public function getZonesByCityId($city_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$city_id . "' AND status = '1' ORDER BY sort_order DESC");

		$zone_data = $query->rows;

		return $query->rows;
	}
}
?>