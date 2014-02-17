<?php
class ModelDesignLayout extends Model {
	public function getLayout($route) {
		$query = $this->db->from('layout_route')
			->where($this->db->escape($route) . " LIKE CONCAT(route, '%')", NULL, false)
			->where(array('store_id' => (int)$this->config->get('config_store_id')))
			->order_by('route', 'ASC')
			->limit(1, 0)
			->get();

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return 0;
		}
	}
}
?>