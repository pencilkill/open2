<?php
class ModelSaleOrder extends Model {
	public function addOrder($data) {
		$this->load->model('setting/store');

		$store_info = $this->model_setting_store->getStore($data['store_id']);

		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = HTTP_CATALOG;
		}

		$this->load->model('setting/setting');

		$setting_info = $this->model_setting_setting->getSetting('setting', $data['store_id']);

		if (isset($setting_info['invoice_prefix'])) {
			$invoice_prefix = $setting_info['invoice_prefix'];
		} else {
			$invoice_prefix = $this->config->get('config_invoice_prefix');
		}

		$this->load->model('localisation/country');

		$this->load->model('localisation/zone');

		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);

		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZone($data['shipping_zone_id']);

		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';
		}

		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);

		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];
		} else {
			$payment_country = '';
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZone($data['payment_zone_id']);

		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';
		}

		$this->load->model('localisation/currency');

		$currency_info = $this->model_localisation_currency->getCurrencyByCode($this->config->get('config_currency'));

		if ($currency_info) {
			$currency_id = $currency_info['currency_id'];
			$currency_code = $currency_info['code'];
			$currency_value = $currency_info['value'];
		} else {
			$currency_id = 0;
			$currency_code = $this->config->get('config_currency');
			$currency_value = 1.00000;
		}

		$this->db->set('invoice_prefix', $invoice_prefix);
		$this->db->set('store_name', $store_name);
		$this->db->set('store_url', $store_url);
		$this->db->set('payment_country', $payment_country);
		$this->db->set('payment_zone', $payment_zone);
		$this->db->set('payment_address_format', $payment_address_format);
		$this->db->set('shipping_country', $shipping_country);
		$this->db->set('shipping_zone', $shipping_zone);
		$this->db->set('shipping_address_format', $shipping_address_format);
		$this->db->set('language_id', (int)$this->config->get('config_language_id'));
		$this->db->set('currency_id', (int)$currency_id);
		$this->db->set('currency_code', $currency_code);
		$this->db->set('currency_value', (float)$currency_value);
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

      	$this->db->insert('order', $data);

      	$order_id = $this->db->getLastId();

      	if (isset($data['order_product'])) {
      		foreach ($data['order_product'] as $order_product) {

      			$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_product', $order_product);

				$order_product_id = $this->db->getLastId();

				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {

						$this->db->set('order_id', (int)$order_id);
						$this->db->set('order_product_id', (int)$order_product_id);

						$this->db->insert('order_option', $order_option);
					}
				}

				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {

						$this->db->set('order_id', (int)$order_id);
						$this->db->set('order_product_id', (int)$order_product_id);

						$this->db->insert('order_download', $order_download);
					}
				}
			}
		}

		if (isset($data['order_voucher'])) {
			foreach ($data['order_voucher'] as $order_voucher) {

				$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_voucher', $order_voucher);

      			$this->db->update('voucher', array('order_id' => (int)$order_id), array('voucher_id' => (int)$order_voucher['voucher_id']));
			}
		}

		// Get the total
		$total = 0;

		if (isset($data['order_total'])) {
      		foreach ($data['order_total'] as $order_total) {

      			$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_total', $order_total);
			}

			$total += $order_total['value'];
		}

		// Affiliate
		$affiliate_id = 0;
		$commission = 0;

		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id'];
				$commission = ($total / 100) * $affiliate_info['commission'];
			}
		}

		// Update order total
		$this->db->update('order', array('total' => (float)$total, 'affiliate_id' => (int)$affiliate_id, 'commission' => (float)$commission), array('order_id' => (int)$order_id));
	}

	public function editOrder($order_id, $data) {
		$this->load->model('localisation/country');

		$this->load->model('localisation/zone');

		$country_info = $this->model_localisation_country->getCountry($data['shipping_country_id']);

		if ($country_info) {
			$shipping_country = $country_info['name'];
			$shipping_address_format = $country_info['address_format'];
		} else {
			$shipping_country = '';
			$shipping_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZone($data['shipping_zone_id']);

		if ($zone_info) {
			$shipping_zone = $zone_info['name'];
		} else {
			$shipping_zone = '';
		}

		$country_info = $this->model_localisation_country->getCountry($data['payment_country_id']);

		if ($country_info) {
			$payment_country = $country_info['name'];
			$payment_address_format = $country_info['address_format'];
		} else {
			$payment_country = '';
			$payment_address_format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$zone_info = $this->model_localisation_zone->getZone($data['payment_zone_id']);

		if ($zone_info) {
			$payment_zone = $zone_info['name'];
		} else {
			$payment_zone = '';
		}

		$this->db->set('payment_country', $payment_country);
		$this->db->set('payment_zone', $payment_zone);
		$this->db->set('payment_address_format', $payment_address_format);
		$this->db->set('shipping_country', $shipping_country);
		$this->db->set('shipping_zone', $shipping_zone);
		$this->db->set('shipping_address_format', $shipping_address_format);
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

      	$this->db->update('order', $data, array('order_id' => (int)$order_id));

		$this->db->delete('order_product', array('order_id' => (int)$order_id));
		$this->db->delete('order_option', array('order_id' => (int)$order_id));
		$this->db->delete('order_download', array('order_id' => (int)$order_id));

      	if (isset($data['order_product'])) {
      		foreach ($data['order_product'] as $order_product) {

      			$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_product', $order_product);

				$order_product_id = $this->db->getLastId();

				if (isset($order_product['order_option'])) {
					foreach ($order_product['order_option'] as $order_option) {

						$this->db->set('order_id', (int)$order_id);
						$this->db->set('order_product_id', (int)$order_product_id);

						$this->db->insert('order_option', $order_option);
					}
				}

				if (isset($order_product['order_download'])) {
					foreach ($order_product['order_download'] as $order_download) {

						$this->db->set('order_id', (int)$order_id);
						$this->db->set('order_product_id', (int)$order_product_id);

						$this->db->insert('order_download', $order_download);
					}
				}
			}
		}

		$this->db->delete('order_voucher', array('order_id' => (int)$order_id));

		if (isset($data['order_voucher'])) {
			foreach ($data['order_voucher'] as $order_voucher) {

				$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_voucher', $order_voucher);

      			$this->db->update('voucher', array('order_id' => (int)$order_id), array('voucher_id' => (int)$order_voucher['voucher_id']));
			}
		}

		// Get the total
		$total = 0;

		$this->db->delete('order_total', array('order_id' => (int)$order_id));

		if (isset($data['order_total'])) {
      		foreach ($data['order_total'] as $order_total) {

      			$this->db->set('order_id', (int)$order_id);

      			$this->db->insert('order_total', $order_total);
			}

			$total += $order_total['value'];
		}

		// Affiliate
		$affiliate_id = 0;
		$commission = 0;

		if (!empty($this->request->post['affiliate_id'])) {
			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($this->request->post['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_id = $affiliate_info['affiliate_id'];
				$commission = ($total / 100) * $affiliate_info['commission'];
			}
		}

		$this->db->update('order', array('total' => (float)$total, 'affiliate_id' => (int)$affiliate_id, 'commission' => (float)$commission), array('order_id' => (int)$order_id));
	}

	public function deleteOrder($order_id) {
		$order_query = $this->db->get_where('order', array('order_status_id > ' => 0, 'order_id' => (int)$order_id));

		if ($order_query->num_rows) {
			$product_query = $this->db->get_where('order_product', array('order_id' => (int)$order_id));

			foreach($product_query->rows as $product) {
				$this->db->set('quantity', '`quantity` + ' . (int)$product['quantity'], false);
				$this->db->where(array('product_id' => (int)$product['product_id'], 'subtract' => 1));
				$this->db->update('product');

				$option_query = $this->db->get_where('order_option')->where(array('order_id' => (int)$order_id, 'order_product_id' => (int)$product['order_product_id']));

				foreach ($option_query->rows as $option) {
					$this->db->set('quantity', '`quantity` + ' . (int)$product['quantity'], false);
					$this->db->where(array('product_option_value_id' => (int)$option['product_option_value_id'], 'subtract' => 1));
					$this->db->update('product_option_value');
				}
			}
		}

		$this->db->delete('order', array('order_id' => (int)$order_id));
		$this->db->delete('order_product', array('order_id' => (int)$order_id));
		$this->db->delete('order_option', array('order_id' => (int)$order_id));
		$this->db->delete('order_download', array('order_id' => (int)$order_id));
		$this->db->delete('order_voucher', array('order_id' => (int)$order_id));
		$this->db->delete('order_total', array('order_id' => (int)$order_id));
		$this->db->delete('order_history', array('order_id' => (int)$order_id));
		$this->db->delete('order_fraud', array('order_id' => (int)$order_id));
		$this->db->delete('customer_transaction', array('order_id' => (int)$order_id));
		$this->db->delete('customer_reward', array('order_id' => (int)$order_id));
		$this->db->delete('affiliate_transaction', array('order_id' => (int)$order_id));
	}

	public function getOrder($order_id) {
		$s1 = $this->db->select("CONCAT(c.firstname, ' ', c.lastname)", false)->from('customer c')->where('c.customer_id = ' . $this->db->protect_identifiers('o.customer_id'))->select_string();

		$order_query = $this->db->select("*,({$s1}) AS customer")->from('order o')->where('o.order_id', (int)$order_id)->get();

		if ($order_query->num_rows) {
			$reward = 0;

			$order_product_query = $this->db->get_where('order_product', array('order_id' => (int)$order_id));

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}

			$country_query = $this->db->get_where('country', array('country_id' => (int)$order_query->row['payment_country_id']));

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->get_where('zone', array('zone_id' => (int)$order_query->row['payment_zone_id']));

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->get_where('country', array('country_id' => (int)$order_query->row['shipping_country_id']));

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->get_where('zone', array('zone_id' => (int)$order_query->row['shipping_zone_id']));

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			if ($order_query->row['affiliate_id']) {
				$affiliate_id = $order_query->row['affiliate_id'];
			} else {
				$affiliate_id = 0;
			}

			$this->load->model('sale/affiliate');

			$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
				$language_filename = $language_info['filename'];
				$language_directory = $language_info['directory'];
			} else {
				$language_code = '';
				$language_filename = '';
				$language_directory = '';
			}

			return array_merge($order_query->row, array(
				'payment_zone_code'       => $payment_zone_code,
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'reward'                  => $reward,
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
			));
		} else {
			return false;
		}
	}

	public function getOrders($data = array()) {
		$s1 = $this->db->select('os.name')->from('order_status os')->where('os.order_status_id = ' . $this->db->protect_identifiers('o.order_status_id'))->where('os.language_id', (int)$this->config->get('config_language_id'))->select_string();

		$query = $this->db->select("o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, ({$s1}) AS status, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified", false)->from('order o');

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
			$query->where('o.order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$query->where('o.order_status_id > ', 0);
		}

		if (!empty($data['filter_order_id'])) {
			$query->where('o.order_id', (int)$data['filter_order_id']);
		}

		if (!empty($data['filter_customer'])) {
			$query->like("LCASE(CONCAT(o.firstname, ' ', o.lastname))", utf8_strtolower($data['filter_customer']));
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(o.date_added)', $data['filter_date_added']);
		}

		if (!empty($data['filter_total'])) {
			$query->where('o.total', (float)$data['filter_total']);
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'o.order_id';
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

	public function getOrderProducts($order_id) {
		$query = $this->db->get_where('order_product', array('order_id', (int)$order_id));

		return $query->rows;
	}

	public function getOrderOption($order_id, $order_option_id) {
		$query = $this->db->get_where('order_option', array('order_id', (int)$order_id, 'order_option_id' => (int)$order_option_id));

		return $query->row;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->get_where('order_option', array('order_id', (int)$order_id, 'order_product_id' => (int)$order_product_id));

		return $query->rows;
	}

	public function getOrderDownloads($order_id, $order_product_id) {
		$query = $this->db->get_where('order_download', array('order_id', (int)$order_id, 'order_product_id' => (int)$order_product_id));

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->get_where('order_voucher', array('order_id', (int)$order_id));

		return $query->rows;
	}

	public function getOrderVoucherByVoucherId($voucher_id) {
		$query = $this->db->get_where('order_voucher', array('voucher_id', (int)$voucher_id));

		return $query->row;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->from('order_total')->where('order_total', array('order_id', (int)$order_id))->order_by('sort_order', 'ASC');

		return $query->rows;
	}

	public function getTotalOrders($data = array()) {
      	$query = $this->db->select('COUNT(*) AS total')->from('order');

		if (isset($data['filter_order_status_id']) && !is_null($data['filter_order_status_id'])) {
			$query->where('order_status_id', (int)$data['filter_order_status_id']);
		} else {
			$query->where('order_status_id > ', 0);
		}

		if (!empty($data['filter_order_id'])) {
			$query->where('order_id', (int)$data['filter_order_id']);
		}

		if (!empty($data['filter_customer'])) {
			$query->like("CONCAT(firstname, ' ', lastname)", $data['filter_customer']);
		}

		if (!empty($data['filter_date_added'])) {
			$query->where('DATE(date_added)', $data['filter_date_added']);
		}

		if (!empty($data['filter_total'])) {
			$query->where('total', (float)$data['filter_total']);
		}

		return $query->get()->row['total'];
	}

	public function getTotalOrdersByStoreId($store_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('order', array('store_id' => (int)$store_id));

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($order_status_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('order', array('order_status_id' => (int)$order_status_id, 'order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function getTotalOrdersByLanguageId($language_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('order', array('language_id' => (int)$language_id, 'order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('order', array('currency_id' => (int)$currency_id, 'order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function getTotalSales() {
      	$query = $this->db->select('SUM(total) AS total')->get_where('order', array('order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function getTotalSalesByYear($year) {
      	$query = $this->db->select('SUM(total) AS total')->get_where('order', array('YEAR(date_added)' => (int)$year, 'order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($this->request->get['order_id']);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->select('MAX(invoice_no) AS invoice_no')->from('order')->where('invoice_prefix', $order_info['invoice_prefix'])->get();

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->db->update('order', array('invoice_no' => (int)$invoice_no, 'invoice_prefix' => $order_info['invoice_prefix']), array('order_id' => (int)$order_id));

			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}

	public function addOrderHistory($order_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('order', array(), array('order_id' => (int)$order_id));

		$this->db->set('order_id', (int)$order_id);
		$this->db->set('order_status_id', (int)$data['order_status_id']);
		$this->db->set('notify', isset($data['notify']) ? (int)$data['notify'] : 0);
		$this->db->set('comment', strip_tags($data['comment']));
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->insert('order_history');

		$order_info = $this->getOrder($order_id);

		// Send out any gift voucher mails
		if ($this->config->get('config_complete_status_id') == $data['order_status_id']) {
			$this->load->model('sale/voucher');

			$results = $this->getOrderVouchers($order_id);

			foreach ($results as $result) {
				$this->model_sale_voucher->sendVoucher($result['voucher_id']);
			}
		}

      	if ($data['notify']) {
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$subject = sprintf($language->get('text_subject'), $order_info['store_name'], $order_id);

			$message  = $language->get('text_order') . ' ' . $order_id . "\n";
			$message .= $language->get('text_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

			$order_status_query = $this->db->get_where('order_status', array('order_status_id' => (int)$data['order_status_id'], 'language_id' => (int)$order_info['language_id']));

			if ($order_status_query->num_rows) {
				$message .= $language->get('text_order_status') . "\n";
				$message .= $order_status_query->row['name'] . "\n\n";
			}

			if ($order_info['customer_id']) {
				$message .= $language->get('text_link') . "\n";
				$message .= html_entity_decode($order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			if ($data['comment']) {
				$message .= $language->get('text_comment') . "\n\n";
				$message .= strip_tags(html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			}

			$message .= $language->get('text_footer');

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
			$mail->AddAddresses($order_info['email']);
			$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
		}
	}

	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->select('oh.date_added, os.name AS status, oh.comment, oh.notify')
			->from('order_history oh')
			->join('order_status os', 'oh.order_status_id = os.order_status_id')
			->where(array('oh.order_id' => (int)$order_id, 'os.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('oh.date_added', 'ASC')
			->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalOrderHistories($order_id) {
	  	$query = $this->db->select('COUNT(*) AS total')->get_where('order_history', array('order_id' => (int)$order_id));

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
	  	$query = $this->db->select('COUNT(*) AS total')->get_where('order_history', array('order_status_id' => (int)$order_status_id));

		return $query->row['total'];
	}

	public function getEmailsByProductsOrdered($products, $start, $end) {
		$query = $this->db->distinct()->select('email')->from('order o')
			->join('order_product op', 'o.order_id = op.order_id')
			->where_in('op.product_id', $products)
			->where('o.order_status_id <> ', 0);

		return $query->get()->rows;
	}

	public function getTotalEmailsByProductsOrdered($products) {
		$query = $this->db->select('COUNT(DISTINCT email) AS total', false)->from('order o')
			->join('order_product op', 'o.order_id = op.order_id')
			->where_in('op.product_id', $products)
			->where('o.order_status_id <> ', 0);

		return $query->get()->row['total'];
	}
}
?>