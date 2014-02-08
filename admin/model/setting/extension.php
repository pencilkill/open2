<?php
class ModelSettingExtension extends Model {
	public function getInstalled($type) {
		$extension_data = array();

		$query = $this->db->get_where('extension', array('type' => $type));

		foreach ($query->rows as $result) {
			$extension_data[] = $result['code'];
		}

		return $extension_data;
	}

	public function install($type, $code) {
		$this->db->insert('extension', array('type' => $type, 'code' => $code));
	}

	public function uninstall($type, $code) {
		$this->db->delete('extension', array('type' => $type, 'code' => $code));
	}
}
?>