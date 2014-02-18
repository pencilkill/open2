<?php
class ModelCheckoutOrder extends Model {
	public function addOrder($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->set('date_modified', date('Y-m-d H:i:s'));

		$this->db->insert('order', $data);

		$order_id = $this->db->getLastId();

		foreach ($data['products'] as $product) {
			$this->db->set('order_id', (int)$order_id);
			$this->db->insert('order_product', $product);

			$order_product_id = $this->db->getLastId();

			foreach ($product['option'] as $option) {
				$this->db->set('order_id', (int)$order_id);
				$this->db->set('order_product_id', (int)$order_product_id);

				$this->db->insert('order_option', $option);
			}

			foreach ($product['download'] as $download) {
				$this->db->set('order_id', (int)$order_id);
				$this->db->set('order_product_id', (int)$order_product_id);
				$this->db->set('order_product_id', (int)($download['remaining'] * $product['quantity']));

				$this->db->insert('order_download', $download);
			}
		}

		foreach ($data['vouchers'] as $voucher) {
			$this->db->set('order_id', (int)$order_id);

			$this->db->insert('order_voucher', $voucher);
		}

		foreach ($data['totals'] as $total) {
			$this->db->set('order_id', (int)$order_id);

			$this->db->insert('order_total', $total);
		}

		return $order_id;
	}

	public function getOrder($order_id) {
		$s1 = $this->db->select('os.name')->from('order_status os')->where('os.order_status_id = o.order_status_id AND os.language_id = o.language_id', NULL, false)->select_string();

		$order_query = $this->db->select("*, ({$s1}) AS order_status", false)->get_where('order o', array('o.order_id' => (int)$order_id));

		if ($order_query->num_rows) {
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
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
			));
		} else {
			return false;
		}
	}

	public function confirm($order_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && !$order_info['order_status_id']) {
			// Fraud Detection
			if ($this->config->get('config_fraud_detection')) {
				$this->load->model('checkout/fraud');

				$risk_score = $this->model_checkout_fraud->getFraudScore($order_info);

				if ($risk_score > $this->config->get('config_fraud_score')) {
					$order_status_id = $this->config->get('config_fraud_status_id');
				}
			}

			// Blacklist
			$status = false;

			$this->load->model('account/customer');

			if ($order_info['customer_id']) {
				$results = $this->model_account_customer->getIps($order_info['customer_id']);

				foreach ($results as $result) {
					if ($this->model_account_customer->isBlacklisted($result['ip'])) {
						$status = true;

						break;
					}
				}
			} else {
				$status = $this->model_account_customer->isBlacklisted($order_info['ip']);
			}

			if ($status) {
				$order_status_id = $this->config->get('config_order_status_id');
			}

			$this->db->update('order', array('order_status_id' => (int)$order_status_id, 'date_modified' => date('Y-m-d H:i:s')), array('order_id' => (int)$order_id));

			$this->db->insert('order_history', array('order_id' => (int)$order_id, 'order_status_id' => (int)$order_status_id, 'notify' => 1, 'comment' => ($comment && $notify) ? $comment : '', 'date_added' => date('Y-m-d H:i:s')));

			$order_product_query = $this->db->get_where('order_product', array('order_id' => (int)$order_id));

			foreach ($order_product_query->rows as $order_product) {
				$this->db->set('quantity', '(quantity - ' . (int)$order_product['quantity'] . ')', false);

				$this->db->update('product', NULL, array('product_id' => (int)$order_product['product_id'], 'subtract' => 1));

				$order_option_query = $this->db->get_where('order_option', array('order_id' => (int)$order_id, array('order_product_id' => (int)$order_product['order_product_id'])));

				foreach ($order_option_query->rows as $option) {
					$this->db->set('quantity', '(quantity - ' . (int)$order_product['quantity'] . ')', false);

					$this->db->update('product_option_value', NULL, array('product_option_value_id' => (int)$option['product_option_value_id'], 'subtract' => 1));
				}
			}

			$this->cache->delete('product');

			// Downloads
			$order_download_query = $this->db->get_where('order_download', array('order_id' => (int)$order_id));

			// Gift Voucher
			$this->load->model('checkout/voucher');

			$order_voucher_query = $this->db->get_where('order_voucher', array('order_id' => (int)$order_id));

			foreach ($order_voucher_query->rows as $order_voucher) {
				$voucher_id = $this->model_checkout_voucher->addVoucher($order_id, $order_voucher);

				$this->db->update('order_voucher', array('voucher_id' => (int)$voucher_id, array('order_voucher_id' => (int)$order_voucher['order_voucher_id'])));
			}

			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->model_checkout_voucher->confirm($order_id);
			}

			// Order Totals
			$order_total_query = $this->db->from('order_total')->where(array('order_id' => (int)$order_id))->order_by('sort_order', 'ASC')->get();

			foreach ($order_total_query->rows as $order_total) {
				$this->load->model('total/' . $order_total['code']);

				if (method_exists($this->{'model_total_' . $order_total['code']}, 'confirm')) {
					$this->{'model_total_' . $order_total['code']}->confirm($order_info, $order_total);
				}
			}

			// Send out order confirmation mail
			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/order');

			$order_status_query = $this->db->get_where('order_status', array('order_status_id' => (int)$order_status_id, 'language_id' => (int)$order_info['language_id']));

			if ($order_status_query->num_rows) {
				$order_status = $order_status_query->row['name'];
			} else {
				$order_status = '';
			}

			$subject = sprintf($language->get('text_new_subject'), $order_info['store_name'], $order_id);

			// HTML Mail
			$template = new Template();

			$template->data['title'] = sprintf($language->get('text_new_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

			$template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));
			$template->data['text_link'] = $language->get('text_new_link');
			$template->data['text_download'] = $language->get('text_new_download');
			$template->data['text_order_detail'] = $language->get('text_new_order_detail');
			$template->data['text_instruction'] = $language->get('text_new_instruction');
			$template->data['text_order_id'] = $language->get('text_new_order_id');
			$template->data['text_date_added'] = $language->get('text_new_date_added');
			$template->data['text_payment_method'] = $language->get('text_new_payment_method');
			$template->data['text_shipping_method'] = $language->get('text_new_shipping_method');
			$template->data['text_email'] = $language->get('text_new_email');
			$template->data['text_telephone'] = $language->get('text_new_telephone');
			$template->data['text_ip'] = $language->get('text_new_ip');
			$template->data['text_payment_address'] = $language->get('text_new_payment_address');
			$template->data['text_shipping_address'] = $language->get('text_new_shipping_address');
			$template->data['text_product'] = $language->get('text_new_product');
			$template->data['text_model'] = $language->get('text_new_model');
			$template->data['text_quantity'] = $language->get('text_new_quantity');
			$template->data['text_price'] = $language->get('text_new_price');
			$template->data['text_total'] = $language->get('text_new_total');
			$template->data['text_footer'] = $language->get('text_new_footer');
			$template->data['text_powered'] = $language->get('text_new_powered');

			$template->data['logo'] = HTTP_IMAGE . $this->config->get('config_logo');
			$template->data['store_name'] = $order_info['store_name'];
			$template->data['store_url'] = $order_info['store_url'];
			$template->data['customer_id'] = $order_info['customer_id'];
			$template->data['link'] = $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;

			if ($order_download_query->num_rows) {
				$template->data['download'] = $order_info['store_url'] . 'index.php?route=account/download';
			} else {
				$template->data['download'] = '';
			}

			$template->data['order_id'] = $order_id;
			$template->data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
			$template->data['payment_method'] = $order_info['payment_method'];
			$template->data['shipping_method'] = $order_info['shipping_method'];
			$template->data['email'] = $order_info['email'];
			$template->data['telephone'] = $order_info['telephone'];
			$template->data['ip'] = $order_info['ip'];

			if ($comment && $notify) {
				$template->data['comment'] = nl2br($comment);
			} else {
				$template->data['comment'] = '';
			}

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$template->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$template->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Products
			$template->data['products'] = array();

			foreach ($order_product_query->rows as $product) {
				$option_data = array();

				$order_option_query = $this->db->get_where('order_option', array('order_id' => (int)$order_id, 'order_product_id' => (int)$product['order_product_id']));

				foreach ($order_option_query->rows as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$template->data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Vouchers
			$template->data['vouchers'] = array();

			foreach ($order_voucher_query->rows as $voucher) {
				$template->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$template->data['totals'] = $order_total_query->rows;

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
			} else {
				$html = $template->fetch('default/template/mail/order.tpl');
			}

			// Text Mail
			$text  = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8')) . "\n\n";
			$text .= $language->get('text_new_order_id') . ' ' . $order_id . "\n";
			$text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
			$text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";

			if ($comment && $notify) {
				$text .= $language->get('text_new_instruction') . "\n\n";
				$text .= $comment . "\n\n";
			}

			// Products
			$text .= $language->get('text_new_products') . "\n";

			foreach ($order_product_query->rows as $product) {
				$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

				$order_option_query = $this->db->get_where('order_option', array('order_id' => (int)$order_id, 'order_product_id' => $product['order_product_id']));

				foreach ($order_option_query->rows as $option) {
					$text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($option['value']) > 20 ? utf8_substr($option['value'], 0, 20) . '..' : $option['value']) . "\n";
				}
			}

			foreach ($order_voucher_query->rows as $voucher) {
				$text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
			}

			$text .= "\n";

			$text .= $language->get('text_new_order_total') . "\n";

			foreach ($order_total_query->rows as $total) {
				$text .= $total['title'] . ': ' . html_entity_decode($total['text'], ENT_NOQUOTES, 'UTF-8') . "\n";
			}

			$text .= "\n";

			if ($order_info['customer_id']) {
				$text .= $language->get('text_new_link') . "\n";
				$text .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
			}

			if ($order_download_query->num_rows) {
				$text .= $language->get('text_new_download') . "\n";
				$text .= $order_info['store_url'] . 'index.php?route=account/download' . "\n\n";
			}

			if ($order_info['comment']) {
				$text .= $language->get('text_new_comment') . "\n\n";
				$text .= $order_info['comment'] . "\n\n";
			}

			$text .= $language->get('text_new_footer') . "\n\n";

			$mail = new Mail();

			$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
			$mail->AddAddresses($order_info['email']);
			$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
			$mail->MsgHTML(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
			$mail->send();

			// Admin Alert Mail
			if ($this->config->get('config_alert_mail')) {
				$subject = sprintf($language->get('text_new_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'), $order_id);

				// Text
				$text  = $language->get('text_new_received') . "\n\n";
				$text .= $language->get('text_new_order_id') . ' ' . $order_id . "\n";
				$text .= $language->get('text_new_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n";
				$text .= $language->get('text_new_order_status') . ' ' . $order_status . "\n\n";
				$text .= $language->get('text_new_products') . "\n";

				foreach ($order_product_query->rows as $product) {
					$text .= $product['quantity'] . 'x ' . $product['name'] . ' (' . $product['model'] . ') ' . html_entity_decode($this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']), ENT_NOQUOTES, 'UTF-8') . "\n";

					$order_option_query = $this->db->get_where('order_option', array('order_id' => (int)$order_id, 'order_product_id' => $product['order_product_id']));

					foreach ($order_option_query->rows as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
						}

						$text .= chr(9) . '-' . $option['name'] . ' ' . (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value) . "\n";
					}
				}

				foreach ($order_voucher_query->rows as $voucher) {
					$text .= '1x ' . $voucher['description'] . ' ' . $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']);
				}

				$text .= "\n";

				$text .= $language->get('text_new_order_total') . "\n";

				foreach ($order_total_query->rows as $total) {
					$text .= $total['title'] . ': ' . html_entity_decode($total['text'], ENT_NOQUOTES, 'UTF-8') . "\n";
				}

				$text .= "\n";

				if ($order_info['comment']) {
					$text .= $language->get('text_new_comment') . "\n\n";
					$text .= $order_info['comment'] . "\n\n";
				}

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
				$mail->AddAddresses($this->config->get('config_email'));
				$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
				$mail->MsgHTML(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));

				// Send to additional alert emails
				$mail->AddAddresses($this->config->get('config_alert_emails'));

				$mail->send();
			}
		}
	}

	public function update($order_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && $order_info['order_status_id']) {
			// Fraud Detection
			if ($this->config->get('config_fraud_detection')) {
				$this->load->model('checkout/fraud');

				$risk_score = $this->model_checkout_fraud->getFraudScore($order_info);

				if ($risk_score > $this->config->get('config_fraud_score')) {
					$order_status_id = $this->config->get('config_fraud_status_id');
				}
			}

			// Blacklist
			$status = false;

			$this->load->model('account/customer');

			if ($order_info['customer_id']) {

				$results = $this->model_account_customer->getIps($order_info['customer_id']);

				foreach ($results as $result) {
					if ($this->model_account_customer->isBlacklisted($result['ip'])) {
						$status = true;

						break;
					}
				}
			} else {
				$status = $this->model_account_customer->isBlacklisted($order_info['ip']);
			}

			if ($status) {
				$order_status_id = $this->config->get('config_order_status_id');
			}

			$this->db->update('order', array('order_status_id' => (int)$order_status_id, 'date_modified' => date('Y-m-d H:i:s')), array('order_id' => (int)$order_id));

			$this->db->insert('order_history', array('order_id' => (int)$order_id, 'order_status_id' => (int)$order_status_id, 'notify' => (int)$notify, 'comment' => $comment, 'date_added' => date('Y-m-d H:i:s')));

			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->load->model('checkout/voucher');

				$this->model_checkout_voucher->confirm($order_id);
			}

			if ($notify) {
				$language = new Language($order_info['language_directory']);
				$language->load($order_info['language_filename']);
				$language->load('mail/order');

				$subject = sprintf($language->get('text_update_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

				$message  = $language->get('text_update_order') . ' ' . $order_id . "\n";
				$message .= $language->get('text_update_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

				$order_status_query = $this->db->get_where('order_status', array('order_status_id' => (int)$order_status_id, 'language_id' => (int)$order_info['language_id']));

				if ($order_status_query->num_rows) {
					$message .= $language->get('text_update_order_status') . "\n\n";
					$message .= $order_status_query->row['name'] . "\n\n";
				}

				if ($order_info['customer_id']) {
					$message .= $language->get('text_update_link') . "\n";
					$message .= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id . "\n\n";
				}

				if ($comment) {
					$message .= $language->get('text_update_comment') . "\n\n";
					$message .= $comment . "\n\n";
				}

				$message .= $language->get('text_update_footer');

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
				$mail->AddAddresses($order_info['email']);
				$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
				$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
			}
		}
	}
}
?>