<?php
class ModelLocalisationLanguage extends Model {
	public function addLanguage($data) {
		$this->db->insert('language', $data);

		$this->cache->delete('language');

		$language_id = $this->db->getLastId();

		// Attribute
		$query = $this->db->get_where('attribute_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $attribute) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('attribute_description', $attribute);
		}

		$this->cache->delete('attribute');

		// Attribute Group
		$query = $this->db->get_where('attribute_group_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $attribute_group) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('attribute_group_description', $attribute_group);
		}

		$this->cache->delete('attribute');

		// Banner
		$query = $this->db->get_where('banner_image_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $banner_image) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('banner_image_description', $banner_image);
		}

		$this->cache->delete('attribute');

		// Category
		$query = $this->db->get_where('category_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $category) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('category_description', $category);
		}

		$this->cache->delete('category');

		// Customer Group
		$query = $this->db->get_where('customer_group_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $customer_group) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('customer_group_description', $customer_group);
		}

		// Download
		$query = $this->db->get_where('download_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $download) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('download_description', $download);
		}

		// Information
		$query = $this->db->get_where('information_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $information) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('information_description', $information);
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information['information_id'] . "', language_id = '" . (int)$language_id . "', title = " . $this->db->escape($information['title']) . ", description = " . $this->db->escape($information['description']) . "");
		}

		$this->cache->delete('information');

		// News
		$query = $this->db->get_where('news_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $news) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('news_description', $news);
		}

		$this->cache->delete('news');

		// Length
		$query = $this->db->get_where('length_class_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $length) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('length_class_description', $length);
		}

		$this->cache->delete('length_class');

		// Option
		$query = $this->db->get_where('option_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $option) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('option_description', $option);
		}

		// Option Value
		$query = $this->db->get_where('option_value_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $option_value) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('option_value_description', $option_value);
		}

		// Order Status
		$query = $this->db->get_where('order_status', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $order_status) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('order_status', $order_status);
		}

		$this->cache->delete('order_status');

		// Product
		$query = $this->db->get_where('product_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $product) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('product_description', $product);
		}

		$this->cache->delete('product');

		// Product Attribute
		$query = $this->db->get_where('product_attribute', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $product_attribute) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('product_attribute', $product_attribute);
		}

		// Return Action
		$query = $this->db->get_where('return_action', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $return_action) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('return_action', $return_action);
		}

		// Return Reason
		$query = $this->db->get_where('return_reason', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $return_reason) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('return_reason', $return_reason);
		}

		// Return Status
		$query = $this->db->get_where('return_status', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $return_status) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('return_status', $return_status);
		}

		// Stock Status
		$query = $this->db->get_where('stock_status', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $stock_status) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('stock_status', $stock_status);
		}

		$this->cache->delete('stock_status');

		// Voucher Theme
		$query = $this->db->get_where('voucher_theme_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $voucher_theme) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('voucher_theme_description', $voucher_theme);
		}

		// Weight Class
		$query = $this->db->get_where('weight_class_description', array('language_id' => (int)$this->config->get('config_language_id')));

		foreach ($query->rows as $weight_class) {
			$this->set('language_id', (int)$language_id);
			$this->db->insert('weight_class_description', $weight_class);
		}

		$this->cache->delete('weight_class');
	}

	public function editLanguage($language_id, $data) {
		$this->db->update('language', $data, array('language_id' => (int)$language_id));

		$this->cache->delete('language');
	}

	public function deleteLanguage($language_id) {
		$this->db->delete('language', array('language_id' => (int)$language_id));

		$this->cache->delete('language');

		$this->db->delete('attribute_description', array('language_id' => (int)$language_id));
		$this->db->delete('attribute_group_description', array('language_id' => (int)$language_id));

		$this->db->delete('banner_image_description', array('language_id' => (int)$language_id));

		$this->db->delete('category_description', array('language_id' => (int)$language_id));

		$this->cache->delete('category');

		$this->db->delete('customer_group_description', array('language_id' => (int)$language_id));
		$this->db->delete('download_description', array('language_id' => (int)$language_id));
		$this->db->delete('information_description', array('language_id' => (int)$language_id));

		$this->cache->delete('information');

		$this->db->delete('length_class_description', array('language_id' => (int)$language_id));

		$this->cache->delete('length_class');

		$this->db->delete('option_description', array('language_id' => (int)$language_id));
		$this->db->delete('option_value_description', array('language_id' => (int)$language_id));
		$this->db->delete('order_status', array('language_id' => (int)$language_id));

		$this->cache->delete('order_status');

		$this->db->delete('product_attribute', array('language_id' => (int)$language_id));
		$this->db->delete('product_description', array('language_id' => (int)$language_id));

		$this->cache->delete('product');

		$this->db->delete('return_action', array('language_id' => (int)$language_id));

		$this->cache->delete('return_action');

		$this->db->delete('return_reason', array('language_id' => (int)$language_id));

		$this->cache->delete('return_reason');

		$this->db->delete('return_status', array('language_id' => (int)$language_id));

		$this->cache->delete('return_status');

		$this->db->delete('stock_status', array('language_id' => (int)$language_id));

		$this->cache->delete('stock_status');

		$this->db->delete('voucher_theme_description', array('language_id' => (int)$language_id));

		$this->cache->delete('voucher_theme');

		$this->db->delete('weight_class_description', array('language_id' => (int)$language_id));

		$this->cache->delete('weight_class');
	}

	public function getLanguage($language_id) {
		$query = $this->db->distinct()->get_where('language', array('language_id' => (int)$language_id));

		return $query->row;
	}

	public function getLanguages($data = array()) {
		if ($data) {
			$query = $this->db->from('language');

			$sort_data = array(
				'name',
				'code',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'sort_order';
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
			$language_data = $this->cache->get('language');

			if (!$language_data) {
				$language_data = array();

				$query = $this->db->from('language')->order_by('sort_order', 'ASC')->get();

    			foreach ($query->rows as $result) {
      				$language_data[$result['code']] = array(
        				'language_id' => $result['language_id'],
        				'name'        => $result['name'],
        				'code'        => $result['code'],
						'locale'      => $result['locale'],
						'image'       => $result['image'],
						'directory'   => $result['directory'],
						'filename'    => $result['filename'],
						'sort_order'  => $result['sort_order'],
						'status'      => $result['status']
      				);
    			}

				$this->cache->set('language', $language_data);
			}

			return $language_data;
		}
	}

	public function getTotalLanguages() {
		return $this->db->count_all('language');
	}
}
?>