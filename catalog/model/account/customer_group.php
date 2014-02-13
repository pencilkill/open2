<?php
class ModelAccountCustomerGroup extends Model {
	public function getCustomerGroup($customer_group_id) {
		$query = $this->db->distinct()->get_where('customer_group', array('customer_group_id' => (int)$customer_group_id));

		return $query->row;
	}

	public function getCustomerGroups() {
		$query = $this->db->from('customer_group cg')
			->join('customer_group_description cgd', 'cg.customer_group_id = cgd.customer_group_id')
			->where('cgd.language_id', (int)$this->config->get('config_language_id'))
			->order_by('cg.sort_order', 'ASC')->order_by('cgd.name', 'ASC');

		return $query->get()->rows;
	}
}
?>