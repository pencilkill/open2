<?php
class ModelDesignLayout extends Model {
	public function addLayout($data) {
		$this->db->insert('layout', $data);

		$layout_id = $this->db->getLastId();

		if (isset($data['layout_route'])) {
			foreach ($data['layout_route'] as $layout_route) {
				$layout_route['layout_id'] = (int)$layout_id;

				$this->db->insert('layout_route', $layout_route);
			}
		}
	}

	public function editLayout($layout_id, $data) {
		$this->db->update('layout', $data, array('layout_id' => (int)$layout_id));

		$this->db->delete('layout_route', array('layout_id' => (int)$layout_id));

		if (isset($data['layout_route'])) {
			foreach ($data['layout_route'] as $layout_route) {
				$layout_route['layout_id'] = (int)$layout_id;

				$this->db->insert('layout_route', $layout_route);
			}
		}
	}

	public function deleteLayout($layout_id) {
		$this->db->delete('layout', array('layout_id' => (int)$layout_id));
		$this->db->delete('layout_route', array('layout_id' => (int)$layout_id));
		$this->db->delete('category_to_layout', array('layout_id' => (int)$layout_id));
		$this->db->delete('product_to_layout', array('layout_id' => (int)$layout_id));
		$this->db->delete('information_to_layout', array('layout_id' => (int)$layout_id));
		$this->db->delete('news_to_layout', array('layout_id' => (int)$layout_id));
	}

	public function getLayout($layout_id) {
		$query = $this->db->distinct()->get_where('layout', array('layout_id' => (int)$layout_id));

		return $query->row;
	}

	public function getLayouts($data = array()) {
		$query = $this->db->from('layout');

		$sort_data = array('name');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'name';
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

	public function getLayoutRoutes($layout_id) {
		$query = $this->db->get_where('layout_route', array('layout_id' => (int)$layout_id));

		return $query->rows;
	}

	public function getTotalLayouts() {
      	return $this->db->count_all('layout');
	}
}
?>