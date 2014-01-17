<?php
class ModelLocalisationCounty extends Model {
	public function getCounty($county_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "county WHERE county_id = '" . (int)$county_id . "' AND status = '1'");

		return $query->row;
	}

	public function getCountiesByZoneId($zone_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "county WHERE zone_id = '" . (int)$zone_id . "' AND status = '1' ORDER BY sort_order DESC");

		$zone_data = $query->rows;

		return $query->rows;
	}
}
?>