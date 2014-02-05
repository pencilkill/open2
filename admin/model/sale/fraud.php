<?php
class ModelSaleFraud extends Model {
	public function getFraud($order_id) {
		$query = $this->db->get_where('order_fraud', array('order_id' => (int)$order_id));

		return $query->row;
	}
}
?>