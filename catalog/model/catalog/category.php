<?php
class ModelCatalogCategory extends Model {
	public function getCategory($category_id) {
		$query = $this->db->distinct()->from('category c')
			->join('category_description cd', 'c.category_id = cd.category_id')
			->join('category_to_store c2s', 'c.category_id = c2s.category_id')
			->where(array('c.category_id' => (int)$category_id, 'cd.language_id' => (int)$this->config->get('config_language_id'), 'c2s.store_id' => (int)$this->config->get('config_store_id'), 'c.status' => 1))
			->get();

		return $query->row;
	}

	public function getCategories($parent_id = 0) {
		$query = $this->db->distinct()->from('category c')
			->join('category_description cd', 'c.category_id = cd.category_id')
			->join('category_to_store c2s', 'c.category_id = c2s.category_id')
			->where(array('c.parent_id' => (int)$parent_id, 'cd.language_id' => (int)$this->config->get('config_language_id'), 'c2s.store_id' => (int)$this->config->get('config_store_id'), 'c.status' => 1))
			->order_by('c.sort_order', 'ASC')
			->order_by('LCASE(cd.name)', 'ASC')
			->get();

		return $query->rows;
	}

	public function getCategoriesByParentId($category_id) {
		$category_data = array();

		$category_query = $this->db->select('category_id')->get_where('category', array('parent_id' => (int)$category_id));

		foreach ($category_query->rows as $category) {
			$category_data[] = $category['category_id'];

			$children = $this->getCategoriesByParentId($category['category_id']);

			if ($children) {
				$category_data = array_merge($children, $category_data);
			}
		}

		return $category_data;
	}

	public function getCategoryLayoutId($category_id) {
		$query = $this->db->get_where('category_to_layout', array('category_id' => (int)$category_id, 'store_id' => (int)$this->config->get('config_store_id')));

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_category');
		}
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		$query = $this->db->select('COUNT(*) AS total')
			->from('category c')
			->join('category_to_store c2s', 'c.category_id = c2s.category_id')
			->where(array('c.parent_id' => (int)$parent_id, 'c2s.store_id' => (int)$this->config->get('config_store_id'), 'c.status' => 1))
			->get();

		return $query->row['total'];
	}
}
?>