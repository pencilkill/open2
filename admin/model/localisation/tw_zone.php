<?php
class ModelLocalisationTwZone extends Model {
	public function addZone($data) {
		$this->db->insert('tw_zone', $data);

		$this->cache->delete('tw_zone');
	}

	public function editZone($zone_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "tw_zone SET status = '" . (int)$data['status'] . "', name = " . $this->db->escape($data['name']) . ", postcode = " . $this->db->escape($data['postcode']) . ", zone_id = '" . (int)$data['zone_id'] . "' WHERE zone_id = '" . (int)$zone_id . "'");

		$this->cache->delete('tw_zone');
	}

	public function deleteZone($zone_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "'");

		$this->cache->delete('tw_zone');
	}

	public function getZone($zone_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row;
	}

	public function getZones($data = array()) {
		$sql = "SELECT *, z.name, c.name AS country FROM " . DB_PREFIX . "tw_zone tz LEFT JOIN " . DB_PREFIX . "zone z ON (tz.zone_id = z.zone_id)";

		$sort_data = array(
			'c.name',
			'tz.name',
			'tz.postcode'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY c.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getZonesByCityId($zone_id) {
		$zone_data = $this->cache->get('tw_zone.' . (int)$zone_id);

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "' ORDER BY name");

			$zone_data = $query->rows;

			$this->cache->set('tw_zone.' . (int)$zone_id, $zone_data);
		}

		return $zone_data;
	}

	public function getTotalZones() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "tw_zone");

		return $query->row['total'];
	}

	public function getTotalZonesByCityId($zone_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "tw_zone WHERE zone_id = '" . (int)$zone_id . "'");

		return $query->row['total'];
	}
}
?>