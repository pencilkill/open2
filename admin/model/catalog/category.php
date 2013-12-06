<?php
class ModelCatalogCategory extends Model {
	public function addCategory($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->insert('category', $data);

		$category_id = $this->db->getLastId();

		foreach ($data['category_description'] as $language_id => $value) {
			$value['category_id'] = (int)$category_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('category_description', $value);
		}

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$value = array('category_id' => (int)$category_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('category_to_store', $value);
			}
		}

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout['category_id'] = (int)$category_id;

					$layout['store_id'] = (int)$store_id;

					$this->db->insert('category_to_layout', $layout);
				}
			}
		}

		if ($data['keyword']) {
			$this->db->set('query', 'category_id=' . (int)$category_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('category');
	}

	public function editCategory($category_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('category', $data, array('category_id' => (int)$category_id));

		$this->db->delete('category_description', array('' => $a));	// category_id = '" . (int)$category_id . "'")

		foreach ($data['category_description'] as $language_id => $value) {
			$this->db->insert('category_description', $value);
		}

		$this->db->delete('category_to_store', array('' => $a));	// category_id = '" . (int)$category_id . "'")

		if (isset($data['category_store'])) {
			foreach ($data['category_store'] as $store_id) {
				$value = array('category_id' => (int)$category_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('category_to_store', $value);
			}
		}

		$this->db->delete('category_to_layout', array('' => $a));	// category_id = '" . (int)$category_id . "'")

		if (isset($data['category_layout'])) {
			foreach ($data['category_layout'] as $store_id => $value) {
				if ($value['layout_id']) {
					$value['category_id'] = (int)$category_id;

					$value['store_id'] = (int)$store_id;

					$this->db->insert('category_to_layout', $value);
				}
			}
		}

		$this->db->delete('url_alias', array('query' => 'category_id=' . (int)$category_id));

		if ($data['keyword']) {
			$this->db->set('query', 'category_id=' . (int)$category_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('category');
	}

	public function deleteCategory($category_id) {
		$this->db->delete('category', array('category_id' => (int)$category_id));
		$this->db->delete('category_description', array('category_id' => (int)$category_id));
		$this->db->delete('category_to_store', array('category_id' => (int)$category_id));
		$this->db->delete('category_to_layout', array('category_id' => (int)$category_id));
		$this->db->delete('product_to_category', array('category_id' => (int)$category_id));
		$this->db->delete('url_alias', array('query' => 'category_id=' . (int)$category_id));

		// recursive
		$query = $this->db->select('category_id')->get_where('category', array('parent_id' => (int)$category_id));

		foreach ($query->rows as $result) {
			$this->deleteCategory($result['category_id']);
		}

		$this->cache->delete('category');
	}

	public function getCategory($category_id) {
		$s1 = $this->db->select('keyword')->from('url_alias')->where(array('query' => 'category_id=' . (int)$category_id))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS keyword')->get_where('category', array('category_id' => (int)$category_id));

		return $query->row;
	}

	public function getCategories($parent_id = 0) {
		$category_data = $this->cache->get('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id);

		if (!$category_data) {
			$category_data = array();

			$query = $this->db->from('category c')->join('category_description cd', 'c.category_id = cd.category_id')->where(array('c.parent_id' => (int)$parent_id, 'cd.language_id' => (int)$this->config->get('config_language_id')))->order_by('c.sort_order', 'ASC')->order_by('cd.name', 'ASC')->get();

			foreach ($query->rows as $result) {
				$category_data[] = array(
					'category_id' => $result['category_id'],
					'name'        => $this->getPath($result['category_id'], $this->config->get('config_language_id')),
					'status'  	  => $result['status'],
					'sort_order'  => $result['sort_order']
				);

				$category_data = array_merge($category_data, $this->getCategories($result['category_id']));
			}

			$this->cache->set('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id, $category_data);
		}

		return $category_data;
	}

	/**
	 * no cache will be setted cause there is no rules to named the cache file which has no conflict with original cache system
	 * @param Integer $parent_id
	 * @return Array
	 */
	public function getChildren($parent_id = 0) {
		$category_data = array();

		$query = $this->db->from('category c')->join('category_description cd', 'c.category_id = cd.category_id')->where(array('c.parent_id' => (int)$parent_id, 'cd.language_id' => (int)$this->config->get('config_language_id')))->order_by('c.sort_order', 'ASC')->order_by('cd.name', 'ASC')->get();

		foreach ($query->rows as $result) {
			$category_data[] = array(
				'category_id' => $result['category_id'],
				'name'        => $this->getPath($result['category_id']),
				'status'  	  => $result['status'],
				'sort_order'  => $result['sort_order']
			);
		}

		return $category_data;
	}

	public function getPath($category_id) {
		$query = $this->db->select('name, parent_id')->from('category c')->join('category_description cd', 'c.category_id = cd.category_id')->where(array('c.category_id' => (int)$category_id, 'cd.language_id' => (int)$this->config->get('config_language_id')))->order_by('c.sort_order', 'ASC')->order_by('cd.name', 'ASC')->get();

		if ($query->row['parent_id']) {
			return $this->getPath($query->row['parent_id']) . $this->language->get('text_separator') . $query->row['name'];
		} else {
			return $query->row['name'];
		}
	}

	public function getCategoryDescriptions($category_id) {
		$category_description_data = array();

		$query = $this->db->get_where('category_description', array('category_id' => (int)$category_id));

		foreach ($query->rows as $result) {
			$category_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'description'      => $result['description']
			);
		}

		return $category_description_data;
	}

	public function getCategoryStores($category_id) {
		$category_store_data = array();

		$query = $this->db->get_where('category_to_store', array('category_id' => (int)$category_id));

		foreach ($query->rows as $result) {
			$category_store_data[] = $result['store_id'];
		}

		return $category_store_data;
	}

	public function getCategoryLayouts($category_id) {
		$category_layout_data = array();

		$query = $this->db->get_where('category_to_layout', array('category_id' => (int)$category_id));

		foreach ($query->rows as $result) {
			$category_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $category_layout_data;
	}

	public function getTotalCategories() {
		return $this->db->count_all('category');
	}

	public function getTotalCategoriesByImageId($image_id) {
		return $this->db->where('image_id' , (int)$image_id)->count_all_results('category');
	}

	public function getTotalCategoriesByLayoutId($layout_id) {
		return $this->db->where('layout_id', (int)$layout_id)->count_all_results('category_to_layout');
	}
}
?>