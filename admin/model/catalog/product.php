<?php
class ModelCatalogProduct extends Model {
	public function addProduct($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->insert('product', $data);

		$product_id = $this->db->getLastId();

		foreach ($data['product_description'] as $language_id => $value) {
			$value['product_id'] = (int)$product_id;

			$value['language_id'] = (int)$language_id;

			$this->db->insert('product_description', $value);
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$value = array('product_id' => (int)$product_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('product_to_store', $value);
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->delete('product_attribute', array('product_id' => (int)$product_id, 'attribute_id' => (int)$product_attribute['attribute_id']));

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$product_attribute_description['product_id'] = (int)$product_id;

						$product_attribute_description['attribute_id'] = (int)$product_attribute['attribute_id'];

						$product_attribute_description['language_id'] = (int)$language_id;

						$this->db->insert('product_attribute', $product_attribute_description);
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				$product_option['product_id'] = (int)$product_id;

				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					$this->db->insert('product_option', $product_option);

					$product_option_id = $this->db->getLastId();

					if (isset($product_option['product_option_value'])) {
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$product_option_value['product_id'] = (int)$product_id;

							$product_option_value['product_option_id'] = (int)$product_option_id;

							$this->db->insert('product_option_value', $product_option_value);
						}
					}
				} else {
					$this->db->insert('product_option', $product_option);
				}
			}
		}

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$product_discount['product_id'] = (int)$product_id;

				$this->db->insert('product_discount', $product_discount);
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$product_special['product_id'] = (int)$product_id;

				$this->db->insert('product_special', $product_special);
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$product_image['product_id'] = (int)$product_id;

				$this->db->insert('product_image', $product_image);
			}
		}

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$value = array('product_id' => (int)$product_id);

				$value['download_id'] = (int)$download_id;

				$this->db->insert('product_to_download', $value);
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$value = array('product_id' => (int)$product_id);

				$value['category_id'] = (int)$category_id;

				$this->db->insert('product_to_download', $value);
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->insert('product_related', array('product_id' => (int)$product_id, 'related_id' => (int)$related_id));
				$this->db->insert('product_related', array('product_id' => (int)$related_id, 'related_id' => (int)$product_id));
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				$product_reward['product_id'] = (int)$product_id;

				$product_reward['customer_group_id'] = (int)$customer_group_id;

				$this->db->insert('product_reward', $product_reward);
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout['product_id'] = (int)$product_id;

					$layout['store_id'] = (int)$store_id;

					$this->db->insert('product_to_layout', $layout);
				}
			}
		}

		if ($data['keyword']) {
			$this->db->set('query', 'product_id=' . (int)$product_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('product');
	}

	public function editProduct($product_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('product', $data, array('product_id' => (int)$product_id));

		$this->db->delete('product_description', array('product_id' => (int)$product_id));

		foreach ($data['product_description'] as $language_id => $value) {
			$value['product_id'] =  (int)$product_id;

			$value['language_id'] =  (int)$language_id;

			$this->db->insert('product_description', $value);
		}

		$this->db->delete('product_to_store', array('product_id' => (int)$product_id));

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$value = array('product_id' => (int)$product_id);

				$value['store_id'] = (int)$store_id;

				$this->db->insert('product_to_store', $value);
			}
		}

		$this->db->delete('product_attribute', array('product_id' => (int)$product_id));

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->delete('product_attribute', array('product_id' => (int)$product_id, 'attribute_id' => (int)$product_attribute['attribute_id']));

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$product_attribute_description['product_id'] = (int)$product_id;

						$product_attribute_description['attribute_id'] = (int)$product_attribute['attribute_id'];

						$product_attribute_description['language_id'] = (int)$language_id;

						$this->db->insert('product_attribute', $product_attribute_description);
					}
				}
			}
		}

		$this->db->delete('product_option', array('product_id' => (int)$product_id));
		$this->db->delete('product_option_value', array('product_id' => (int)$product_id));

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				$product_option['product_id'] = (int)$product_id;

				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					$this->db->insert('product_option', $product_option);

					$product_option_id = $this->db->getLastId();

					if (isset($product_option['product_option_value'])) {
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$product_option_value['product_id'] = (int)$product_id;

							$product_option_value['product_option_id'] = (int)$product_option_id;

							$this->db->insert('product_option_value', $product_option_value);
						}
					}
				} else {
					$this->db->insert('product_option', $product_option);
				}
			}
		}

		$this->db->delete('product_discount', array('product_id' => (int)$product_id));

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$product_discount['product_id'] = (int)$product_id;

				$this->db->insert('product_discount', $product_discount);
			}
		}

		$this->db->delete('product_special', array('product_id' => (int)$product_id));

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$product_special['product_id'] = (int)$product_id;

				$this->db->insert('product_special', $product_special);
			}
		}

		$this->db->delete('product_image', array('product_id' => (int)$product_id));

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$product_image['product_id'] = (int)$product_id;

				$this->db->insert('product_image', $product_image);
			}
		}

		$this->db->delete('product_to_download', array('product_id' => (int)$product_id));

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$value = array('product_id' => (int)$product_id);

				$value['download_id'] = (int)$download_id;

				$this->db->insert('product_to_download', $value);
			}
		}

		$this->db->delete('product_to_category', array('product_id' => (int)$product_id));

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$value = array('product_id' => (int)$product_id);

				$value['category_id'] = (int)$category_id;

				$this->db->insert('product_to_category', $value);
			}
		}

		$this->db->delete('product_related', array('product_id' => (int)$product_id));
		$this->db->delete('product_related', array('related_id' => (int)$product_id));

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->insert('product_related', array('product_id' => (int)$product_id, 'related_id' => (int)$related_id));
				$this->db->insert('product_related', array('product_id' => (int)$related_id, 'related_id' => (int)$product_id));
			}
		}

		$this->db->delete('product_reward', array('product_id' => (int)$product_id));

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				$product_reward['product_id'] = (int)$product_id;

				$product_reward['customer_group_id'] = (int)$customer_group_id;

				$this->db->insert('product_reward', $product_reward);
			}
		}

		$this->db->delete('product_to_layout', array('product_id' => (int)$product_id));

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$layout['product_id'] = (int)$product_id;

					$layout['store_id'] = (int)$store_id;

					$this->db->insert('product_reward', $layout);
				}
			}
		}

		$this->db->delete('url_alias', array('query' => 'product_id=' . (int)$product_id));

		if ($data['keyword']) {
			$this->db->set('query', 'product_id=' . (int)$product_id);
			$this->db->set('keyword', $data['keyword']);
			$this->db->insert('url_alias');
		}

		$this->cache->delete('product');
	}

	public function copyProduct($product_id) {
		$query = $this->db->distinct()->from('product p')->join('product_description pd', 'p.product_id = pd.product_id')->where(array('p.product_id' => (int)$product_id, 'pd.language_id' => (int)$this->config->get('config_language_id')));

		if ($query->num_rows) {
			$data = array();

			$data = $query->row;

			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data = array_merge($data, array('product_attribute' => $this->getProductAttributes($product_id)));
			$data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));
			$data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
			$data = array_merge($data, array('product_image' => $this->getProductImages($product_id)));
			$data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));
			$data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
			$data = array_merge($data, array('product_reward' => $this->getProductRewards($product_id)));
			$data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
			$data = array_merge($data, array('product_category' => $this->getProductCategories($product_id)));
			$data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
			$data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));
			$data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));

			$this->addProduct($data);
		}
	}

	public function deleteProduct($product_id) {
		$this->db->delete('product', array('product_id' => (int)$product_id));
		$this->db->delete('product_attribute', array('product_id' => (int)$product_id));
		$this->db->delete('product_description', array('product_id' => (int)$product_id));
		$this->db->delete('product_discount', array('product_id' => (int)$product_id));
		$this->db->delete('product_image', array('product_id' => (int)$product_id));
		$this->db->delete('product_option', array('product_id' => (int)$product_id));
		$this->db->delete('product_option_value', array('product_id' => (int)$product_id));
		$this->db->delete('product_related', array('product_id' => (int)$product_id));
		$this->db->delete('product_related', array('related_id' => (int)$product_id));
		$this->db->delete('product_reward', array('product_id' => (int)$product_id));
		$this->db->delete('product_special', array('product_id' => (int)$product_id));
		$this->db->delete('product_to_category', array('product_id' => (int)$product_id));
		$this->db->delete('product_to_download', array('product_id' => (int)$product_id));
		$this->db->delete('product_to_layout', array('product_id' => (int)$product_id));
		$this->db->delete('product_to_store', array('product_id' => (int)$product_id));
		$this->db->delete('review', array('product_id' => (int)$product_id));

		$this->db->delete('url_alias', array('query' => 'product_id=' . (int)$product_id));

		$this->cache->delete('product');
	}

	public function getProduct($product_id) {
		$s1 = $this->db->select('keyword')->from('url_alias')->where(array('query' => 'product_id=' . (int)$product_id))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS keyword')->from('product p')->join('product_description pd', 'p.product_id = pd.product_id')->where(array('p.product_id' => (int)$product_id, 'pd.language_id' => (int)$this->config->get('config_language_id')))->get();

		return $query->row;
	}

	public function getProducts($data = array()) {
		if ($data) {
			if (!empty($data['filter_category_id'])) {
				$category_data = array((int)$data['filter_category_id']);

				if (!empty($data['filter_sub_category'])) {
					$this->load->model('catalog/category');

					$categories = $this->model_catalog_category->getCategories($data['filter_category_id']);

					foreach ($categories as $category) {
						$category_data[] = (int)$category['category_id'];
					}
				}
			}

			$query = $this->db->from('product p')->join('product_description pd',  'p.product_id = pd.product_id');

			if (!empty($data['filter_category_id'])) {
				$query->join('product_to_category p2c', 'p.product_id = p2c.product_id');
			}

			$query->where('pd.language_id', (int)$this->config->get('config_language_id'));

			if (!empty($data['filter_name'])) {
				$query->like('LCASE(pd.name)', utf8_strtolower($data['filter_name']));
			}

			if (!empty($data['filter_model'])) {
				$query->like('LCASE(p.model)', utf8_strtolower($data['filter_model']));
			}

			if (!empty($data['filter_price'])) {
				$query->like('p.price', $data['filter_price']);
			}

			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$query->where('p.quantity', $data['filter_quantity']);
			}

			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$query->where('p.status', (int)$data['filter_status']);
			}

			if(!empty($category_data)){
				$query->where_in('p2c.category_id', $category_data);
			}

			$query->group_by('p.product_id');

			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'p.sort_order';
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
			$product_data = $this->cache->get('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'));

			if (!$product_data) {
				$query = $this->db->from('product p')->join('product_description pd', 'p.product_id = pd.product_id')->where('pd.language_id', (int)$this->config->get('config_language_id'))->order_by('p.sort_order', 'ASC')->order_by('pd.name', 'ASC')->get();

				$product_data = $query->rows;

				$this->cache->set('product.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $product_data);
			}

			return $product_data;
		}
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->from('product p')->join('product_description pd', 'p.product_id = pd.product_id')->join('product_to_category p2c', 'p.product_id = p2c.product_id')->where(array('pd.language_id' => (int)$this->config->get('config_language_id'), 'p2c.category_id' => (int)$category_id))->order_by('p.sort_order', 'ASC')->order_by('pd.name', 'ASC')->get();

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->get_where('product_description', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description'],
				'tag'              => $result['tag']
			);
		}

		return $product_description_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->select('pa.attribute_id, ad.name')->from('product_attribute pa')->join('attribute a', 'pa.attribute_id = a.attribute_id')->join('attribute_description ad', 'a.attribute_id = ad.attribute_id')->where(array('pa.product_id' => (int)$product_id, 'ad.language_id' => (int)$this->config->get('config_language_id')))->group_by('pa.attribute_id')->get();

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->get_where('product_attribute', array('product_id' => (int)$product_id, 'attribute_id' => (int)$product_attribute['attribute_id']));

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'name'                          => $product_attribute['name'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}

	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->from('product_option po')->join('option o', 'po.option_id = o.option_id')->join('option_description od', 'o.option_id = od.option_id')->where(array('po.product_id' => (int)$product_id, 'od.language_id' => (int)$this->config->get('config_language_id')))->order_by('o.sort_order', 'ASC')->get();

		foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();

				$product_option_value_query = $this->db->from('product_option_value pov')->join('option_value ov', 'pov.option_value_id = ov.option_value_id')->join('option_value_description ovd',  'ov.option_value_id = ovd.option_value_id')->where(array('pov.product_option_id' => (int)$product_option['product_option_id'], 'ovd.language_id' => (int)$this->config->get('config_language_id')))->order_by('ov.sort_order', 'ASC')->get();

				foreach ($product_option_value_query->rows as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}

				$product_option_data[] = array(
					'product_option_id'    => $product_option['product_option_id'],
					'option_id'            => $product_option['option_id'],
					'name'                 => $product_option['name'],
					'type'                 => $product_option['type'],
					'product_option_value' => $product_option_value_data,
					'required'             => $product_option['required']
				);
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);
			}
		}

		return $product_option_data;
	}

	public function getProductImages($product_id) {
		$query = $this->db->get_where('product_image', array('product_id' => (int)$product_id));

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->from('product_discount')->where(array('product_id' => (int)$product_id))->order_by('quantity', 'ASC')->order_by('priority', 'ASC')->order_by('price', 'ASC')->get();

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->from('product_special')->where(array('product_id' => (int)$product_id))->order_by('priority', 'ASC')->order_by('price', 'ASC')->get();

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->get_where('product_reward', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->get_where('product_to_download', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->get_where('product_to_store', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}

	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->get_where('product_to_layout', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->get_where('product_to_category', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->get_where('product_related', array('product_id' => (int)$product_id));

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getTotalProducts($data = array()) {
		if (!empty($data['filter_category_id'])) {
			$category_data = array((int)$data['filter_category_id']);

			if (!empty($data['filter_sub_category'])) {
				$this->load->model('catalog/category');

				$categories = $this->model_catalog_category->getCategories($data['filter_category_id']);

				foreach ($categories as $category) {
					$category_data[] = (int)$category['category_id'];
				}
			}
		}

		$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('p.product_id') . ') AS total', false)->from('product p')->join('product_description pd', 'p.product_id = pd.product_id');

		if (!empty($data['filter_category_id'])) {
			$query->join('product_to_category p2c', 'p.product_id = p2c.product_id');
		}

		$query->where('pd.language_id', (int)$this->config->get('config_language_id'));

		if (!empty($data['filter_name'])) {
			$query->like('LCASE(pd.name)', utf8_strtolower($data['filter_name']));
		}

		if (!empty($data['filter_model'])) {
			$query->like('LCASE(p.model)', utf8_strtolower($data['filter_model']));
		}

		if (!empty($data['filter_price'])) {
			$query->like('p.price', $data['filter_price']);
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$query->where('p.quantity', $data['filter_quantity']);
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$query->where('p.status', (int)$data['filter_status']);
		}

		if(!empty($category_data)){
			$query->where_in('p2c.category_id', $category_data);
		}

		return $query->get()->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		return $this->db->where(array('tax_class_id' => (int)$tax_class_id))->count_all_results('product');
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		return $this->db->where(array('stock_status_id' => (int)$stock_status_id))->count_all_results('product');
	}

	public function getTotalProductsByWeightClassId($weight_class_id) {
		return $this->db->where(array('weight_class_id' => (int)$weight_class_id))->count_all_results('product');
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		return $this->db->where(array('length_class_id' => (int)$length_class_id))->count_all_results('product');
	}

	public function getTotalProductsByDownloadId($download_id) {
		return $this->db->where(array('download_id' => (int)$download_id))->count_all_results('product_to_download');
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		return $this->db->where(array('manufacturer_id' => (int)$manufacturer_id))->count_all_results('product');
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		return $this->db->where(array('attribute_id' => (int)$attribute_id))->count_all_results('product_attribute');
	}

	public function getTotalProductsByOptionId($option_id) {
		return $this->db->where(array('option_id' => (int)$option_id))->count_all_results('product_option');
	}

	public function getTotalProductsByLayoutId($layout_id) {
		return $this->db->where(array('layout_id' => (int)$layout_id))->count_all_results('product_to_layout');
	}
}
?>