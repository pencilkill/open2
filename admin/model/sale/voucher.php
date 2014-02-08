<?php
class ModelSaleVoucher extends Model {
	public function addVoucher($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$this->db->insert('voucher', $data);
	}

	public function editVoucher($voucher_id, $data) {
      	$this->db->udpate('voucher', $data, array('voucher_id' =>(int)$voucher_id));
	}

	public function deleteVoucher($voucher_id) {
      	$this->db->delete('voucher', array('voucher_id' =>(int)$voucher_id));
		$this->db->delete('voucher_history', array('voucher_id' =>(int)$voucher_id));
	}

	public function getVoucher($voucher_id) {
      	$query = $this->db->distinct()->get_where('voucher', array('voucher_id' => (int)$voucher_id));

		return $query->row;
	}

	public function getVoucherByCode($code) {
      	$query = $this->db->distinct()->get_where('voucher', array('code' => $code));

		return $query->row;
	}

	public function getVouchers($data = array()) {
		$s1 = $this->db->select('vtd.name')->from('voucher_theme_description vtd')
			->where('vtd.voucher_theme_id = ' . $this->db->protect_identifiers('v.voucher_theme_id'))
			->where('vtd.language_id', (int)$this->config->get('config_language_id'))
			->select_string();

		$query = $this->db->select("v.voucher_id, v.code, v.from_name, v.from_email, v.to_name, v.to_email, ({$s1}) AS theme, v.amount, v.status, v.date_added", false)->from('voucher v');

		$sort_data = array(
			'v.code',
			'v.from_name',
			'v.from_email',
			'v.to_name',
			'v.to_email',
			'v.theme',
			'v.amount',
			'v.status',
			'v.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'v.date_added';
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

	public function sendVoucher($voucher_id) {
		$voucher_info = $this->getVoucher($voucher_id);

		if ($voucher_info) {
			if ($voucher_info['order_id']) {
				$order_id = $voucher_info['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			// If voucher belongs to an order
			if ($order_info) {
				$this->load->model('localisation/language');

				$language = new Language($order_info['language_directory']);
				$language->load($order_info['language_filename']);
				$language->load('mail/voucher');

				// HTML Mail
				$template = new Template();

				$template->data['title'] = sprintf($language->get('text_subject'), $voucher_info['from_name']);

				$template->data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$template->data['text_from'] = sprintf($language->get('text_from'), $voucher_info['from_name']);
				$template->data['text_message'] = $language->get('text_message');
				$template->data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher_info['code']);
				$template->data['text_footer'] = $language->get('text_footer');

				$this->load->model('sale/voucher_theme');

				$voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme($voucher_info['voucher_theme_id']);

				if ($voucher_info && file_exists(DIR_IMAGE . $voucher_theme_info['image'])) {
					$template->data['image'] = HTTP_IMAGE . $voucher_theme_info['image'];
				} else {
					$template->data['image'] = '';
				}

				$template->data['store_name'] = $order_info['store_name'];
				$template->data['store_url'] = $order_info['store_url'];
				$template->data['message'] = nl2br($voucher_info['message']);

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
				$mail->AddAddresses($voucher_info['to_email']);
				$mail->Subject = html_entity_decode(sprintf($language->get('text_subject'), $voucher_info['from_name']), ENT_QUOTES, 'UTF-8');
				$mail->MsgHTML($template->fetch('mail/voucher.tpl'));
				$mail->send();

			// If voucher does not belong to an order
			}  else {
				$this->language->load('mail/voucher');

				$template = new Template();

				$template->data['title'] = sprintf($this->language->get('text_subject'), $voucher_info['from_name']);

				$template->data['text_greeting'] = sprintf($this->language->get('text_greeting'), $this->currency->format($voucher_info['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$template->data['text_from'] = sprintf($this->language->get('text_from'), $voucher_info['from_name']);
				$template->data['text_message'] = $this->language->get('text_message');
				$template->data['text_redeem'] = sprintf($this->language->get('text_redeem'), $voucher_info['code']);
				$template->data['text_footer'] = $this->language->get('text_footer');

				$this->load->model('sale/voucher_theme');

				$voucher_theme_info = $this->model_sale_voucher_theme->getVoucherTheme($voucher_info['voucher_theme_id']);

				if ($voucher_info && file_exists(DIR_IMAGE . $voucher_theme_info['image'])) {
					$template->data['image'] = HTTP_IMAGE . $voucher_theme_info['image'];
				} else {
					$template->data['image'] = '';
				}

				$template->data['store_name'] = $this->config->get('config_name');
				$template->data['store_url'] = HTTP_CATALOG;
				$template->data['message'] = nl2br($voucher_info['message']);

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
				$mail->AddAddresses($voucher_info['to_email']);
				$mail->Subject = html_entity_decode(sprintf($this->language->get('text_subject'), $voucher_info['from_name']), ENT_QUOTES, 'UTF-8');
				$mail->MsgHTML($template->fetch('mail/voucher.tpl'));
				$mail->send();
			}
		}
	}

	public function getTotalVouchers() {
      	return $this->db->count_all('voucher');
	}

	public function getTotalVouchersByVoucherThemeId($voucher_theme_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('voucher', array('voucher_theme_id' => (int)$voucher_theme_id));

		return $query->row['total'];
	}

	public function getVoucherHistories($voucher_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->select("vh.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, vh.amount, vh.date_added", false)
			->from('voucher_history vh')
			->join('order o', 'vh.order_id = o.order_id')
			->where('vh.voucher_id', (int)$voucher_id)
			->order_by('vh.date_added', 'ASC')
			->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalVoucherHistories($voucher_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('voucher_history', array('voucher_id' => (int)$voucher_id));

		return $query->row['total'];
	}
}
?>