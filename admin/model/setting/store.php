<?php
class ModelSettingStore extends Model {
	public function addStore($data) {
		$this->db->set('name', $data['config_name']);
		$this->db->set('url', $data['config_url']);
		$this->db->set('ssl', $data['config_ssl']);

		$this->db->insert('store');

		$this->cache->delete('store');

		return $this->db->getLastId();
	}

	public function editStore($store_id, $data) {
		$this->db->set('name', $data['config_name']);
		$this->db->set('url', $data['config_url']);
		$this->db->set('ssl', $data['config_ssl']);

		$this->db->update('store', NULL, array('store_id' => (int)$store_id));

		$this->cache->delete('store');
	}

	public function deleteStore($store_id) {
		$this->db->delete('store', array('store_id' => (int)$store_id));

		$this->cache->delete('store');
	}

	public function getStore($store_id) {
		$query = $this->db->distinct()->get_where('store', array('store_id' => (int)$store_id));

		return $query->row;
	}

	public function getStores($data = array()) {
		$store_data = $this->cache->get('store');

		if (!$store_data) {
			$query = $this->db->from('store')->order_by('url', 'ASC')->get();

			$store_data = $query->rows;

			$this->cache->set('store', $store_data);
		}

		return $store_data;
	}

	public function getTotalStores() {
      	return $this->db->count_all('store');
	}

	public function getTotalStoresByLayoutId($layout_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_layout_id', 'value' => (int)$layout_id, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByLanguage($language) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_language', 'value' => $language, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByCurrency($currency) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_currency', 'value' => $currency, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByCountryId($country_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_country_id', 'value' => (int)$country_id, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByZoneId($zone_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_zone_id', 'value' => (int)$zone_id, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByCustomerGroupId($customer_group_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_customer_group_id', 'value' => (int)$customer_group_id, 'store_id != ' => 0));

		return $query->row['total'];
	}

	public function getTotalStoresByInformationId($information_id) {
      	$account_query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_account_id', 'value' => (int)$information_id, 'store_id != ' => 0));

      	$account_query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_checkout_id', 'value' => (int)$information_id, 'store_id != ' => 0));

		return ($account_query->row['total'] + $checkout_query->row['total']);
	}

	public function getTotalStoresByOrderStatusId($order_status_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('setting', array('key' => 'config_order_status_id', 'value' => (int)$order_status_id, 'store_id != ' => 0));

		return $query->row['total'];
	}
}
?>