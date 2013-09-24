<?php
class ModelCatalogNews extends Model {
	public function addNews($data) {
		$this->db->insert(DB_PREFIX . 'news', $data);

		$news_id = $this->db->getLastId();

		foreach ($data['news_description'] as $language_id => $value) {
			$value['news_id']=(int)$news_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert(DB_PREFIX . 'news_description', $value);
		}

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$data['news_id']=(int)$news_id;

				$data['store_id']=(int)$store_id;

				$this->db->insert(DB_PREFIX . 'news_to_store', $data);
			}
		}

		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "news_to_layout SET news_id = '" . (int)$news_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		if ($data['keyword']) {
			$data['query']='news_id=' . (int)$news_id;

			$this->db->insert(DB_PREFIX . 'url_alias', $data);
		}

		$this->cache->delete('news');
	}

	public function editNews($news_id, $data) {
		$this->db->update(DB_PREFIX . 'news', $data, array('news_id'=>(int)$news_id));

		$this->db->delete(DB_PREFIX . 'news_description', array('news_id'=>(int)$news_id));

		foreach ($data['news_description'] as $language_id => $value) {
			$value['news_id']=(int)$news_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert(DB_PREFIX . 'news_description', $value);
		}

		$this->db->delete(DB_PREFIX . 'news_to_store', array('news_id'=>(int)$news_id));

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$data['news_id']=(int)$news_id;

				$data['store_id']=(int)$store_id;

				$this->db->insert(DB_PREFIX . 'news_to_store', $data);
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");

		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "news_to_layout SET news_id = '" . (int)$news_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}

		$this->db->delete(DB_PREFIX . 'url_alias', array('query'=>'news_id='.(int)$news_id));

		if ($data['keyword']) {
			$data['query']='news_id='.(int)$news_id;

			$this->db->insert(DB_PREFIX . 'url_alias', $data);
		}

		$this->cache->delete('news');
	}

	public function deleteNews($news_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_description WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_store WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'news_id=" . (int)$news_id . "'");

		$this->cache->delete('news');
	}

	public function getNews($news_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'news_id=" . (int)$news_id . "') AS keyword FROM " . DB_PREFIX . "news WHERE news_id = '" . (int)$news_id . "'");

		return $query->row;
	}

	public function getNewses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			if(!empty($data['filter_title'])){
				$sql .= " AND LCASE(nd.title) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_title'])) . "%'";
			}

			if(!empty($data['filter_date_added'])){
				$sql .= " AND n.date_added = '" . $data['filter_date_added'] . "'";
			}

			if(isset($data['filter_status']) && !is_null($data['filter_status'])){
				$sql .= " AND n.status = '" . $data['filter_status'] . "'";
			}

			$sort_data = array(
				'nd.title',
				'n.date_added',
				'n.sort_order',
				'n.status'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY n.sort_order";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
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
			$news_data = $this->cache->get('news.' . $this->config->get('config_language_id'));

			if (!$news_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY n.sort_order");

				$news_data = $query->rows;

				$this->cache->set('news.' . $this->config->get('config_language_id'), $news_data);
			}

			return $news_data;
		}
	}

	public function getNewsDescriptions($news_id) {
		$news_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_description WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			foreach ($result as $key => $val){
				$news_description_data[$result['language_id']][$key]=$val;
			}
		}

		return $news_description_data;
	}

	public function getNewsStores($news_id) {
		$news_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_store WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$news_store_data[] = $result['store_id'];
		}

		return $news_store_data;
	}

	public function getNewsLayouts($news_id) {
		$news_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "'");

		foreach ($query->rows as $result) {
			$news_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $news_layout_data;
	}

	public function getTotalNewses() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news");

		return $query->row['total'];
	}
}
?>