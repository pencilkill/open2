<?php
class ModelSettingSetting extends Model {
	public function getSetting($group, $store_id = 0) {
		$data = array();

		$query = $this->db->get_where('setting', array('store_id' => (int)$store_id, 'group' => $group));

		foreach ($query->rows as $result) {
			if (!$result['serialized']) {
				$data[$result['key']] = $result['value'];
			} else {
				$data[$result['key']] = unserialize($result['value']);
			}
		}

		return $data;
	}

	public function editSetting($group, $data, $store_id = 0) {
		$this->db->delete('setting', array('store_id' => (int)$store_id, 'group' => $group));

		foreach ($data as $key => $value) {
			$this->db->set('store_id', (int)$store_id);
			$this->db->set('group', $group);
			$this->db->set('key', $key);
			if (!is_array($value)) {
				$this->db->set('value', $value);
				$this->db->set('serialized', 0);
			} else {
				$this->db->set('value', serialize($value));
				$this->db->set('serialized', 1);
			}

			$this->db->insert('setting');
		}
	}

	public function deleteSetting($group, $store_id = 0) {
		$this->db->delete('setting', array('store_id' => (int)$store_id, 'group' => $group));
	}
}
?>