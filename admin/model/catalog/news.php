<?php
class ModelCatalogNews extends Model {
	public function addNews($data) {
		$this->db->insert('news', $data);

		$news_id = $this->db->getLastId();

		foreach ($data['news_description'] as $language_id => $value) {
			$value['news_id']=(int)$news_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert('news_description', $value);
		}

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$value = array('news_id' => (int)$news_id);

				$value['store_id']=(int)$store_id;

				$this->db->insert('news_to_store', $value);
			}
		}

		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout['news_id']=(int)$news_id;

					$layout['store_id'] = (int)$store_id;

					$this->db->insert('news_to_layout', $layout);
				}
			}
		}

		if ($data['keyword']) {
			$this->db->set('query', 'information_id=' . (int)$information_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('news');
	}

	public function editNews($news_id, $data) {
		$this->db->update('news', $data, array('news_id'=>(int)$news_id));

		$this->db->delete('news_description', array('news_id'=>(int)$news_id));

		foreach ($data['news_description'] as $language_id => $value) {
			$value['news_id']=(int)$news_id;

			$value['language_id']=(int)$language_id;

			$this->db->insert('news_description', $value);
		}

		$this->db->delete('news_to_store', array('news_id'=>(int)$news_id));

		if (isset($data['news_store'])) {
			foreach ($data['news_store'] as $store_id) {
				$value = array('news_id' => (int)$news_id);

				$value['store_id']=(int)$store_id;

				$this->db->insert('news_to_store', $value);
			}
		}

		$this->db->delete('news_to_layout', array('news_id' => (int)$news_id));

		if (isset($data['news_layout'])) {
			foreach ($data['news_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout['news_id']=(int)$news_id;

					$layout['store_id']=(int)$store_id;

					$this->db->insert('news_to_layout', $layout);
				}
			}
		}

		$this->db->delete('url_alias', array('query'=>'news_id='.(int)$news_id));

		if ($data['keyword']) {
			$this->db->set('query', 'new_id=' . (int)$new_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('news');
	}

	public function deleteNews($news_id) {
		$this->db->delete('news', array('news_id' => (int)$news_id));
		$this->db->delete('news_description', array('news_id' => (int)$news_id));
		$this->db->delete('news_to_store', array('news_id' => (int)$news_id));
		$this->db->delete('news_to_layout', array('news_id' => (int)$news_id));
		$this->db->delete('url_alias', array('news_id' => (int)$news_id));

		$this->cache->delete('news');
	}

	public function getNews($news_id) {
		$s1 = $this->db->select('keyword')->from('url_alias')->where(array('query' => 'news_id=' . (int)$news_id))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS keyword')->get_where('news', array('news_id' => (int)$news_id));

		return $query->row;
	}

	public function getNewses($data = array()) {
		if ($data) {
			$query =$this->db->from('news n')->join('news_description nd', 'n.news_id = nd.news_id')->where('nd.language_id' , (int)$this->config->get('config_language_id'));

			if(!empty($data['filter_title'])){
				$query->like('LCASE(nd.title)', utf8_strtolower($data['filter_title']));
			}

			if(!empty($data['filter_date_added'])){
				$query->where('n.date_added' , $data['filter_date_added']);
			}

			if(isset($data['filter_status']) && !is_null($data['filter_status'])){
				$query->where('n.status' ,$data['filter_status']);
			}

			$sort_data = array(
				'nd.title',
				'n.date_added',
				'n.sort_order',
				'n.status'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'n.sort_order';
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
		} else {
			$news_data = $this->cache->get('news.' . $this->config->get('config_language_id'));

			if (!$news_data) {
				$query = $this->db->from('news n')->join('news_description nd', 'n.news_id = nd.news_id')->where(array('nd.language_id' => (int)$this->config->get('config_language_id')))->order_by('n.sort_order', 'ASC')->get();

				$news_data = $query->rows;

				$this->cache->set('news.' . $this->config->get('config_language_id'), $news_data);
			}

			return $news_data;
		}
	}

	public function getNewsDescriptions($news_id) {
		$news_description_data = array();

		$query = $this->db->get_where('news_description', array('news_id' => (int)$news_id));

		foreach ($query->rows as $result) {
			foreach ($result as $key => $val){
				$news_description_data[$result['language_id']][$key]=$val;
			}
		}

		return $news_description_data;
	}

	public function getNewsStores($news_id) {
		$news_store_data = array();

		$query = $this->db->get_where('news_to_store', array('news_id' => (int)$news_id));

		foreach ($query->rows as $result) {
			$news_store_data[] = $result['store_id'];
		}

		return $news_store_data;
	}

	public function getNewsLayouts($news_id) {
		$news_layout_data = array();

		$query = $this->db->get_where('news_to_layout', array('news_id' => (int)$news_id));

		foreach ($query->rows as $result) {
			$news_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $news_layout_data;
	}

	public function getTotalNewses() {
		return $this->db->count_all('news');
	}
}
?>