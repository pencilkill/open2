<?php
class ModelCatalogInformation extends Model {
	public function addInformation($data) {
		$this->db->insert('information', $data);

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
			$value['information_id'] = (int)$information_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('information_description', $value);
		}

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$value = array('information_id' => (int)$information_id);

				$value = array('store_id' => (int)$store_id);

				$this->db->insert('information_to_store', $value);
			}
		}

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout = array('information_id' => (int)$information_id);

					$layout = array('store_id' => (int)$store_id);

					$this->db->insert('information_to_layout', $layout);
				}
			}
		}

		if ($data['keyword']) {
			$this->db->set('query', 'information_id=' . (int)$information_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('information');
	}

	public function editInformation($information_id, $data) {
		$this->db->update('information', $data, array('information_id' => (int)$information_id));

		$this->db->delete('information_description', array('information_id' => (int)$information_id));

		foreach ($data['information_description'] as $language_id => $value) {
			$value['information_id'] = (int)$information_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('information_description', $value);
		}

		$this->db->delete('information_to_store', array('information_id' =>(int)$information_id));

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$value = array('information_id' => (int)$information_id);

				$value = array('store_id' => (int)$store_id);

				$this->db->insert('information_to_store', $value);
			}
		}

		$this->db->delete('information_to_layout', array('information_id' =>(int)$information_id));

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout = array('information_id' => (int)$information_id);

					$layout = array('store_id' => (int)$store_id);

					$this->db->insert('information_to_layout', $layout);
				}
			}
		}

		$this->db->delete('url_alias', array('query' => 'information_id=' . (int)$information_id));

		if ($data['keyword']) {
			$this->db->set('query', 'information_id=' . (int)$information_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('information');
	}

	public function deleteInformation($information_id) {
		$this->db->delete('information', array('information_id' =>(int)$information_id));
		$this->db->delete('information_description', array('information_id' =>(int)$information_id));
		$this->db->delete('information_to_store', array('information_id' =>(int)$information_id));
		$this->db->delete('information_to_layout', array('information_id' =>(int)$information_id));
		$this->db->delete('url_alias', array('query' => 'information_id=' . (int)$information_id));

		$this->cache->delete('information');
	}

	public function getInformation($information_id) {
		$s1 = $this->db->select('keyword')->from('url_alias')->where(array('query' => 'information_id=' . (int)$information_id))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS keyword')->get_where('information', array('information_id' => (int)$information_id));

		return $query->row;
	}

	public function getInformations($data = array()) {
		if ($data) {
			$query =$this->db->from('information i')->join('information_description id', 'i.information_id = id.information_id')->where('id.language_id' , (int)$this->config->get('config_language_id'));

			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'i.sort_order';
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
			$information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

			if (!$information_data) {
				$query = $this->db->from('information i')->join('information_description id', 'i.information_id = id.information_id')->where(array('id.language_id' => (int)$this->config->get('config_language_id')))->order_by('n.sort_order', 'ASC');

				$information_data = $query->get()->rows;

				$this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescriptions($information_id) {
		$information_description_data = array();

		$query = $this->db->get_where('information_description', array('information_id' => (int)$information_id));

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array(
				'title'       => $result['title'],
				'description' => $result['description']
			);
		}

		return $information_description_data;
	}

	public function getInformationStores($information_id) {
		$information_store_data = array();

		$query = $this->db->get_where('information_to_store', array('information_id' => (int)$information_id));

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getInformationLayouts($information_id) {
		$information_layout_data = array();

		$query = $this->db->get_where('information_to_layout', array('information_id' => (int)$information_id));

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	public function getTotalInformations() {
		return $this->db->count_all('information');
	}

	public function getTotalInformationsByLayoutId($layout_id) {
		return $this->db->where('layout_id', (int)$layout_id)->count_all_results('information_to_layout');
	}
}
?>