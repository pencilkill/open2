<?php
class ModelCatalogFaq extends Model {
	public function addFaq($data) {
		$this->db->insert(DB_PREFIX . 'faq', $data);

		$faq_id = $this->db->getLastId();

		foreach ($data['faq_description'] as $language_id => $value) {
			$value['faq_id']=(int)$faq_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert(DB_PREFIX . 'faq_description', $value);
		}

		if (isset($data['faq_store'])) {
			foreach ($data['faq_store'] as $store_id) {
				$data['faq_id']=(int)$faq_id;

				$data['store_id']=(int)$store_id;

				$this->db->insert(DB_PREFIX . 'faq_to_store', $data);
			}
		}

		if (isset($data['faq_layout'])) {
			foreach ($data['faq_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "faq_to_layout SET faq_id = '" . (int)$faq_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		if ($data['keyword']) {
			$data['query']='faq_id=' . (int)$faq_id;

			$this->db->insert(DB_PREFIX . 'url_alias', $data);
		}

		$this->cache->delete('faq');
	}

	public function editFaq($faq_id, $data) {
		$this->db->update(DB_PREFIX . 'faq', $data, array('faq_id'=>(int)$faq_id));

		$this->db->delete(DB_PREFIX . 'faq_description', array('faq_id'=>(int)$faq_id));

		foreach ($data['faq_description'] as $language_id => $value) {
			$value['faq_id']=(int)$faq_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert(DB_PREFIX . 'faq_description', $value);
		}

		$this->db->delete(DB_PREFIX . 'faq_to_store', array('faq_id'=>(int)$faq_id));

		if (isset($data['faq_store'])) {
			foreach ($data['faq_store'] as $store_id) {
				$data['faq_id']=(int)$faq_id;

				$data['store_id']=(int)$store_id;

				$this->db->insert(DB_PREFIX . 'faq_to_store', $data);
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_to_layout WHERE faq_id = '" . (int)$faq_id . "'");

		if (isset($data['faq_layout'])) {
			foreach ($data['faq_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "faq_to_layout SET faq_id = '" . (int)$faq_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		$this->db->delete(DB_PREFIX . 'url_alias', array('query'=>'faq_id='.(int)$faq_id));

		if ($data['keyword']) {
			$data['query']='faq_id='.(int)$faq_id;

			$this->db->insert(DB_PREFIX . 'url_alias', $data);
		}

		$this->cache->delete('faq');
	}

	public function deleteFaq($faq_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_to_store WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "faq_to_layout WHERE faq_id = '" . (int)$faq_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'faq_id=" . (int)$faq_id . "'");

		$this->cache->delete('faq');
	}

	public function getFaq($faq_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'faq_id=" . (int)$faq_id . "') AS keyword FROM " . DB_PREFIX . "faq WHERE faq_id = '" . (int)$faq_id . "'");

		return $query->row;
	}

	public function getFaqs($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'nd.title',
				'n.sort_order',
				'n.status'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY n.sort_order";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC,n.faq_id desc";
			} else {
				$sql .= " ASC,n.faq_id desc";
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
		} else {
			$faq_data = $this->cache->get('faq.' . $this->config->get('config_language_id'));

			if (!$faq_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY n.sort_order");

				$faq_data = $query->rows;

				$this->cache->set('faq.' . $this->config->get('config_language_id'), $faq_data);
			}

			return $faq_data;
		}
	}

	public function getFaqDescriptions($faq_id) {
		$faq_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq_description WHERE faq_id = '" . (int)$faq_id . "'");

		foreach ($query->rows as $result) {
			foreach ($result as $key => $val){
				$faq_description_data[$result['language_id']][$key]=$val;
			}
		}

		return $faq_description_data;
	}

	public function getFaqStores($faq_id) {
		$faq_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq_to_store WHERE faq_id = '" . (int)$faq_id . "'");

		foreach ($query->rows as $result) {
			$faq_store_data[] = $result['store_id'];
		}

		return $faq_store_data;
	}

	public function getFaqLayouts($faq_id) {
		$faq_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq_to_layout WHERE faq_id = '" . (int)$faq_id . "'");

		foreach ($query->rows as $result) {
			$faq_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $faq_layout_data;
	}

	public function getTotalFaqs() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "faq");

		return $query->row['total'];
	}
}
?>