<?php
class ModelSaleCustomerGroup extends Model {
	public function addCustomerGroup($data) {
		$this->db->insert('customer_group', $data);

		$customer_group_id = $this->db->getLastId();

		foreach ($data['customer_group_description'] as $language_id => $value) {
			$value['customer_group_id'] = (int)$customer_group_id;
			$value['language_id'] = (int)$language_id;

			$this->db->insert('customer_group_description', $value);
		}
	}

	public function editCustomerGroup($customer_group_id, $data) {
		$this->db->update('customer_group', $data, array('customer_group_id' => $customer_group_id));

		$this->db->delete('customer_group_description', array('customer_group_id' => $customer_group_id));

		foreach ($data['customer_group_description'] as $language_id => $value) {
			$value['customer_group_id'] = (int)$customer_group_id;
			$value['language_id'] = (int)$language_id;

			$this->db->insert('customer_group_description', $value);
		}
	}

	public function deleteCustomerGroup($customer_group_id) {
		$this->db->delete('customer_group', array('customer_group_id' => $customer_group_id));
		$this->db->delete('customer_group_description', array('customer_group_id' => $customer_group_id));
		$this->db->delete('product_discount', array('customer_group_id' => $customer_group_id));
		$this->db->delete('product_special', array('customer_group_id' => $customer_group_id));
		$this->db->delete('product_reward', array('customer_group_id' => $customer_group_id));
	}

	public function getCustomerGroup($customer_group_id) {
		$query = $this->db->distinct()->select()->from('customer_group cg')
			->join('customer_group_description cgd', 'cg.customer_group_id = cgd.customer_group_id')
			->where(array('cg.customer_group_id' => (int)$customer_group_id, 'cgd.language_id' => (int)$this->config->get('config_language_id')));

		return $query->get()->row;
	}

	public function getCustomerGroups($data = array()) {
		$query = $this->db->from('customer_group cg')
			->join('customer_group_description cgd', 'cg.customer_group_id = cgd.customer_group_id')
			->where('cgd.language_id', (int)$this->config->get('config_language_id'));

		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'cgd.name';
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query->order_by($sort, 'DESC');
		} else {
			$query->order_by($sort, 'ASC');
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if (!isset($data['start']) || (int)$data['start'] < 0) {
				$data['start'] = 0;
			}

			if (!isset($data['limit']) || (int)$data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$query->limit((int)$data['limit'], (int)$data['start']);
		}

		return $query->get()->rows;
	}

	public function getCustomerGroupDescriptions($customer_group_id) {
		$customer_group_data = array();

		$query = $this->db->get_where('customer_group_description', array('customer_group_id' => (int)$customer_group_id));

		foreach ($query->rows as $result) {
			$customer_group_data[$result['language_id']] = array(
				'name'        => $result['name'],
				'description' => $result['description']
			);
		}

		return $customer_group_data;
	}

	public function getTotalCustomerGroups() {
		return $this->db->count_all('customer_group');
	}
}
?>