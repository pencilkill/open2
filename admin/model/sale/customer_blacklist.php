<?php
class ModelSaleCustomerBlacklist extends Model {
	public function addCustomerBlacklist($data) {
		$this->db->insert('customer_ip_blacklist', $data);
	}

	public function editCustomerBlacklist($customer_ip_blacklist_id, $data) {
		$this->db->update('customer_ip_blacklist', $data, array('customer_ip_blacklist_id' => (int)$customer_ip_blacklist_id));
	}

	public function deleteCustomerBlacklist($customer_ip_blacklist_id) {
		$this->db->delete('customer_ip_blacklist', array('customer_ip_blacklist_id' => (int)$customer_ip_blacklist_id));
	}

	public function getCustomerBlacklist($customer_ip_blacklist_id) {
		$query = $this->db->get_where('customer_ip_blacklist', array('customer_ip_blacklist_id' => (int)$customer_ip_blacklist_id));

		return $query->row;
	}

	public function getCustomerBlacklists($data = array()) {
		$s1 = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('customer_id') . ')', false)->from('customer_ip ci')->where('ci.ip = ' . $this->db->protect_identifiers('cib.ip'))->select_string();

		$query = $this->db->select("*, ({$s1}) AS total")->from('customer_ip_blacklist cib');


		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query->order_by('ip', 'DESC');
		} else {
			$query->order_by('ip', 'ASC');
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

	public function getTotalCustomerBlacklists($data = array()) {
      	return $this->db->count_all('customer_ip_blacklist');
	}
}
?>