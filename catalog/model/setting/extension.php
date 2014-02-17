<?php
class ModelSettingExtension extends Model {
	function getExtensions($type) {
		$query = $this->db->get_where('extension', array('type' => $type));

		return $query->rows;
	}
}
?>