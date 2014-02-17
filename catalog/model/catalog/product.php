<?php
class ModelCatalogProduct extends Model {
	public function updateViewed($product_id) {
		$this->db->set('viewed', '(viewed+1)', false);

		$this->db->update('product', NULL, array('product_id' => (int)$product_id));
	}

	public function getProduct($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$s1 = $this->db->select('price')
			->from('product_discount pd2')
			->where('pd2.product_id = p.product_id', NULL, false)
			->where(array('pd2.customer_group_id' => (int)$customer_group_id, 'pd2.quantity' => 1))
			->where("((pd2.date_start = '0000-00-00' OR pd2.date_start < '" . date('Y-m-d H:i:s') . "') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '" . date('Y-m-d H:i:s') . "'))")
			->order_by('pd2.priority', 'ASC')->order_by('pd2.price', 'ASC')
			->limit(1, 0)
			->select_string();

		$s2 = $this->db->select('price')
			->from('product_special ps')
			->where('ps.product_id = p.product_id', NULL, false)
			->where(array('ps.customer_group_id' => (int)$customer_group_id))
			->where("((ps.date_start = '0000-00-00' OR ps.date_start < '" . date('Y-m-d H:i:s') . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:i:s') . "'))")
			->order_by('ps.priority', 'ASC')->order_by('ps.price', 'ASC')
			->limit(1, 0)
			->select_string();

		$s3 = $this->db->select('points')
			->from('product_reward pr')
			->where('pr.product_id = p.product_id', NULL, false)
			->where(array('pr.customer_group_id' => (int)$customer_group_id))
			->select_string();

		$s4 = $this->db->select('ss.name')
			->from('stock_status ss')
			->where('ss.stock_status_id = p.stock_status_id', NULL, false)
			->where(array('ss.language_id' => (int)$this->config->get('config_language_id')))
			->select_string();

		$s5 = $this->db->select('wcd.unit')
			->from('weight_class_description wcd')
			->where('wcd.weight_class_id = p.weight_class_id', NULL, false)
			->where(array('wcd.language_id' => (int)$this->config->get('config_language_id')))
			->select_string();

		$s6 = $this->db->select('lcd.unit')
			->from('length_class_description lcd')
			->where('lcd.length_class_id = p.length_class_id', NULL, false)
			->where(array('lcd.language_id' => (int)$this->config->get('config_language_id')))
			->select_string();

		$s7 = $this->db->select('AVG(rating) AS total')
			->from('review r1')
			->where('r1.product_id = p.product_id', NULL, false)
			->where(array('r1.status' => 1))
			->group_by('r1.product_id')
			->select_string();

		$s8 = $this->db->select('COUNT(*) AS total')
			->from('review r2')
			->where('r2.product_id = p.product_id', NULL, false)
			->where(array('r2.status' => 1))
			->group_by('r2.product_id')
			->select_string();

		$query = $this->db->select("DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, ({$s1}) AS discount, ({$s2}) AS special, ({$s3}) AS reward, ({$s4}) AS stock_status, ({$s5}) AS weight_class, ({$s6}) AS length_class, ({$s7}) AS rating, ({$s8}) AS reviews, p.sort_order", false)
			->from('product p')
			->join('product_description pd', 'p.product_id = pd.product_id')
			->join('product_to_store p2s', 'p.product_id = p2s.product_id')
			->join('manufacturer m', 'p.manufacturer_id = m.manufacturer_id')
			->where(array('p.product_id' => (int)$product_id, 'pd.language_id' => (int)$this->config->get('config_language_id'), 'p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')));

		if ($query->num_rows) {
			return array_merge($query->row, array(
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'rating'           => round($query->row['rating']),
			));
		} else {
			return false;
		}
	}

	public function getProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$cache = md5(http_build_query($data));

		$product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);

		if (!$product_data) {
			$s1 = $this->db->select('AVG(rating) AS total')
				->from('review r1')
				->where('r1.product_id = p.product_id', NULL, false)
				->where(array('r1.status' => 1))
				->group_by('r1.product_id')
				->select_string();

			$query = $this->db->select("p.product_id, ({$s1}) AS total", false)
				->from('product p')
				->join('product_description pd', 'p.product_id = pd.product_id')
				->join('product_to_store p2s', 'p.product_id = p2s.product_id');

			if (!empty($data['filter_category_id'])) {
				$query->join('product_to_category p2c', 'p.product_id = p2c.product_id');
			}

			$query->where(array('pd.language_id' => (int)$this->config->get('config_language_id'), 'p.status' => 1, 'p.date_available <= ' < date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')));

			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql = "(";

				if (!empty($data['filter_name'])) {
					if (!empty($data['filter_description'])) {
						$sql .= "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%' OR MATCH(pd.description) AGAINST(" . $this->db->escape(utf8_strtolower($data['filter_name'])) . ")";
					} else {
						$sql .= "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
					}
				}

				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}

				if (!empty($data['filter_tag'])) {
					$sql .= "MATCH(pd.tag) AGAINST(" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . ")";
				}

				$sql .= ")";

				$query->where($sql, NULL, false);

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.model)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.sku)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.upc)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.ean)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.jan)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.isbn)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.mpn)', utf8_strtolower($data['filter_name']));
				}
			}

			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();

					$implode_data[] = (int)$data['filter_category_id'];

					$this->load->model('catalog/category');

					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);

					foreach ($categories as $category_id) {
						$implode_data[] = (int)$category_id;
					}

					$query->where("p2c.category_id IN (" . implode(', ', $implode_data) . ")", NULL, false);
				} else {
					$query->where('p2c.category_id', (int)$data['filter_category_id']);
				}
			}

			if (!empty($data['filter_manufacturer_id'])) {
				$query->where('p.manufacturer_id', (int)$data['filter_manufacturer_id']);
			}

			$query->where('p.product_id');

			$sort_data = array(
				'pd.name',
				'p.model',
				'p.quantity',
				'p.price',
				'rating',
				'p.sort_order',
				'p.date_added'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sort = 'LCASE(' . $data['sort'] . ')';
				} else {
					$sort = $data['sort'];
				}
			} else {
				$sort = 'p.sort_order';
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by($sort, 'DESC')->order_by('pd.name', 'DESC');
			} else {
				$query->order_by($sort, 'ASC')->order_by('pd.name', 'ASC');
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

			foreach ($query->get()->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
		}

		return $product_data;
	}

	public function getProductSpecials($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$s1 = $this->db->select('AVG(rating) AS total')
				->from('review r1')
				->where('r1.product_id = ps.product_id', NULL, false)
				->where(array('r1.status' => 1))
				->group_by('r1.product_id')
				->select_string();

		$query = $this->db->select("DISTINCT ps.product_id, ({$s1}) AS rating", false)
			->from('product_special ps')
			->join('product p', 'ps.product_id = p.product_id')
			->join('product_description pd', 'p.product_id = pd.product_id')
			->join('product_to_store p2s', 'p.product_id = p2s.product_id')
			->where(array('p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id'), 'ps.customer_group_id' => (int)$customer_group_id))
			->where("((ps.date_start = '0000-00-00' OR ps.date_start < '" . date('Y-m-d H:i:s') . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:i:s') . "'))", NULL, false)
			->group_by('ps.product_id');

		$sort_data = array(
			'pd.name',
			'p.model',
			'ps.price',
			'rating',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sort = 'LCASE(' . $data['sort'] . ')';
				} else {
					$sort = $data['sort'];
				}
			} else {
				$sort = 'p.sort_order';
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by($sort, 'DESC')->order_by('pd.name', 'DESC');
			} else {
				$query->order_by($sort, 'ASC')->order_by('pd.name', 'ASC');
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

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getLatestProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) {
			$query = $this->db->select('p.product_id')
				->from('product p')
				->join('product_to_store p2s', 'p.product_id = p2s.product_id')
				->where(array('p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')))
				->order_by('p.date_added', 'DESC')
				->limit((int)$limit, 0);

			foreach ($query->get()->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getPopularProducts($limit) {
		$product_data = array();

		$query = $this->db->select('p.product_id')
				->from('product p')
				->join('product_to_store p2s', 'p.product_id = p2s.product_id')
				->where(array('p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')))
				->order_by('p.viewed', 'DESC')->order_by('p.date_added', 'DESC')
				->limit((int)$limit, 0);

		foreach ($query->get()->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
		}

		return $product_data;
	}

	public function getBestSellerProducts($limit) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$product_data = $this->cache->get('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit);

		if (!$product_data) {
			$product_data = array();

			$query = $this->db->select('op.product_id, COUNT(*) AS total')
				->from('order_product op')
				->join('order o', 'op.order_id = o.order_id')
				->join('product p', 'op.product_id = p.product_id')
				->join('product_to_store p2s', 'p.product_id = p2s.product_id')
				->where(array('o.order_status_id > ' => 0, 'p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')))
				->group_by('op.product_id')
				->order_by('total', 'DESC')
				->limit((int)$limit, 0);

			foreach ($query->get()->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id']);
			}

			$this->cache->set('product.bestseller.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'). '.' . $customer_group_id . '.' . (int)$limit, $product_data);
		}

		return $product_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->select('ag.attribute_group_id, agd.name')
			->from('product_attribute pa')
			->join('attribute a', 'pa.attribute_id = a.attribute_id')
			->join('attribute_group ag', 'a.attribute_group_id = ag.attribute_group_id')
			->join('attribute_group_description agd', 'ag.attribute_group_id = agd.attribute_group_id')
			->where(array('pa.product_id' => (int)$product_id, 'agd.language_id' => (int)$this->config->get('config_language_id')))
			->group_by('ag.attribute_group_id')
			->order_by('ag.sort_order', 'ASC')
			->order_by('agd.name', 'ASC');

		foreach ($product_attribute_group_query->get()->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->select('a.attribute_id, ad.name, pa.text')
				->from('product_attribute pa')
				->join('attribute a', 'pa.attribute_id = a.attribute_id')
				->join('attribute_description ad', 'a.attribute_id = ad.attribute_id')
				->where(array('pa.product_id' => (int)$product_id, 'a.attribute_group_id' => (int)$product_attribute_group['attribute_group_id'], 'ad.language_id' => (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id')))
				->order_by('a.sort_order', 'ASC')
				->order_by('ad.name', 'ASC');

			$product_attribute_group['attribute'] = $product_attribute_query->get()->rows;

			$product_attribute_group_data[] = $product_attribute_group;
		}

		return $product_attribute_group_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->from('product_option po')
			->join('option o', 'po.option_id = o.option_id')
			->join('option_description od', 'o.option_id = od.option_id')
			->where(array('po.product_id' => (int)$product_id, 'od.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('o.sort_order', 'ASC');

		foreach ($product_option_query->get()->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();

				$product_option_value_query = $this->db->from('product_option_value pov')
					->join('option_value ov', 'pov.option_value_id = ov.option_value_id')
					->join('option_value_description ovd', 'ov.option_value_id = ovd.option_value_id')
					->where(array('pov.product_id' => (int)$product_id, 'pov.product_option_id' => (int)$product_option['product_option_id'], 'ovd.language_id' => (int)$this->config->get('config_language_id')))
					->order_by('ov.sort_order', 'ASC');

				$product_option['option_value'] = $product_option_value_query->rows;

				$product_option_data[] = $product_option;
			} else {
				$product_option_data[] = $product_option;
			}
      	}

		return $product_option_data;
	}

	public function getProductDiscounts($product_id) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$query = $this->db->from('product_discount')
			->where(array('product_id' => (int)$product_id, 'customer_group_id' => (int)$customer_group_id, 'quantity > ' => 1))
			->where("((date_start = '0000-00-00' OR date_start < '" . date('Y-m-d H:i:s') . "') AND (date_end = '0000-00-00' OR date_end > '" . date('Y-m-d H:i:s') . "'))", NULL, false)
			->order_by('quantity', 'ASC')
			->order_by('priority', 'ASC')
			->order_by('price', 'ASC');

		return $query->get()->rows;
	}

	public function getProductImages($product_id) {
		$query = $this->db->from('product_image')->where(array('product_id' => (int)$product_id))->order_by('sort_order', 'ASC');

		return $query->get()->rows;
	}

	public function getProductRelated($product_id) {
		$product_data = array();

		$query = $this->db->from('product_related pr')
			->join('product p', 'pr.related_id = p.product_id')
			->join('product_to_store p2s', 'p.product_id = p2s.product_id')
			->where(array('pr.product_id' => (int)$product_id, 'p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')));

		foreach ($query->get()->rows as $result) {
			$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
		}

		return $product_data;
	}

	public function getProductLayoutId($product_id) {
		$query = $this->db->get_where('product_to_layout', array('product_id' => (int)$product_id, 'store_id' => (int)$this->config->get('config_store_id')));

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return  $this->config->get('config_layout_product');
		}
	}

	public function getCategories($product_id) {
		$query = $this->db->get_where('product_to_category', array('product_id' => (int)$product_id));

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$cache = md5(http_build_query($data));

		$product_data = $this->cache->get('product.total.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache);

		if (!$product_data) {
			$query = $this->db->select('COUNT(DISTINCT p.product_id) AS total', false)
				->from('product p')
				->join('product_description pd', 'p.product_id = pd.product_id')
				->join('product_to_store p2s', 'p.product_id = p2s.product_id');

			if (!empty($data['filter_category_id'])) {
				$query->join('product_to_category p2c', 'p.product_id = p2c.product_id');
			}

			$query->where(array('pd.language_id' => (int)$this->config->get('config_language_id'), 'p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id')));

			if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
				$sql = "(";

				if (!empty($data['filter_name'])) {
					if (!empty($data['filter_description'])) {
						$sql .= "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%' OR MATCH(pd.description) AGAINST(" . $this->db->escape(utf8_strtolower($data['filter_name'])) . ")";
					} else {
						$sql .= "LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "%'";
					}
				}

				if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
					$sql .= " OR ";
				}

				if (!empty($data['filter_tag'])) {
					$sql .= "MATCH(pd.tag) AGAINST(" . $this->db->escape(utf8_strtolower($data['filter_tag'])) . ")";
				}

				$sql .= ")";

				$query->where($sql, NULL, false);

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.model)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.sku)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.upc)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.ean)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.jan)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.isbn)', utf8_strtolower($data['filter_name']));
				}

				if (!empty($data['filter_name'])) {
					$query->or_where('LCASE(p.mpn)', utf8_strtolower($data['filter_name']));
				}
			}

			if (!empty($data['filter_category_id'])) {
				if (!empty($data['filter_sub_category'])) {
					$implode_data = array();

					$implode_data[] = (int)$data['filter_category_id'];

					$this->load->model('catalog/category');

					$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);

					foreach ($categories as $category_id) {
						$implode_data[] = (int)$category_id;
					}

					$query->where("p2c.category_id IN (" . implode(', ', $implode_data) . ")", NULL, false);
				} else {
					$query->where('p2c.category_id', (int)$data['filter_category_id']);
				}
			}

			if (!empty($data['filter_manufacturer_id'])) {
				$query->where('p.manufacturer_id', (int)$data['filter_manufacturer_id']);
			}

			$product_data = $query->get()->row['total'];

			$this->cache->set('product.total.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$customer_group_id . '.' . $cache, $product_data);
		}

		return $product_data;
	}

	public function getTotalProductSpecials() {
		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$query = $this->db->select('COUNT(DISTINCT ps.product_id) AS total', false)
			->from('product_special ps')
			->join('product p', 'ps.product_id = p.product_id')
			->join('product_to_store p2s', 'p.product_id = p2s.product_id')
			->where(array('p.status' => 1, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p2s.store_id' => (int)$this->config->get('config_store_id'), 'ps.customer_group_id' => (int)$customer_group_id))
			->where("((ps.date_start = '0000-00-00' OR ps.date_start < '" . date('Y-m-d H:i:s') . "') AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:i:s') . "'))", NULL, false)
			->get();

		if (isset($query->row['total'])) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}
}
?>