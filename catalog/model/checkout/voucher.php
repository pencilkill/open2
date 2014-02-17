<?php
class ModelCheckoutVoucher extends Model {
	public function addVoucher($order_id, $data) {
		$this->db->set('order_id', (int)$order_id);
		$this->db->set('status', 1);
		$this->db->set('date_added', date('Y-m-d H:i:s'));

      	$this->db->insert('voucher', $data);

		return $this->db->getLastId();
	}

	public function getVoucher($code) {
		$status = true;

		$voucher_query = $this->db->select('*, vtd.name AS theme')
			->from('voucher v')
			->join('voucher_theme vt', 'v.voucher_theme_id = vt.voucher_theme_id')
			->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')
			->where(array('v.code' => $code, 'vtd.language_id' => (int)$this->config->get('config_language_id'), 'v.status' => 1))
			->get();

		if ($voucher_query->num_rows) {
			if ($voucher_query->row['order_id']) {
				$order_query = $this->db->get_where('order', array('order_id' => (int)$voucher_query->row['order_id'], 'order_status_id' => (int)$this->config->get('config_complete_status_id')));

				if (!$order_query->num_rows) {
					$status = false;
				}

				$order_voucher_query = $this->db->get_where('order_voucher', array('order_id' => (int)$voucher_query->row['order_id'], 'voucher_id' => (int)$voucher_query->row['voucher_id']));

				if (!$order_voucher_query->num_rows) {
					$status = false;
				}
			}

			$voucher_history_query = $this->db->select('SUM(amount) AS total')
				->from('voucher_history vh')
				->where(array('vh.voucher_id' => (int)$voucher_query->row['voucher_id']))
				->group_by('vh.voucher_id')
				->get();

			if ($voucher_history_query->num_rows) {
				$amount = $voucher_query->row['amount'] + $voucher_history_query->row['total'];
			} else {
				$amount = $voucher_query->row['amount'];
			}

			if ($amount <= 0) {
				$status = false;
			}
		} else {
			$status = false;
		}

		if ($status) {
			return array_merge($voucher_query->row, array(
				'amount'           => $amount
			));
		}
	}

	public function confirm($order_id) {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->load->model('localisation/language');

			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/voucher');

			$voucher_query = $this->db->select('*, vtd.name AS theme')
				->from('voucher v')
				->join('voucher_theme vt', 'v.voucher_theme_id = vt.voucher_theme_id')
				->join('voucher_theme_description vtd', "vt.voucher_theme_id = vtd.voucher_theme_id AND vtd.language_id = '" . (int)$order_info['language_id'] . "'")
				->where(array('v.order_id' => (int)$order_id))
				->get();

			foreach ($voucher_query->rows as $voucher) {
				// HTML Mail
				$template = new Template();

				$template->data['title'] = sprintf($language->get('text_subject'), $voucher['from_name']);

				$template->data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$template->data['text_from'] = sprintf($language->get('text_from'), $voucher['from_name']);
				$template->data['text_message'] = $language->get('text_message');
				$template->data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher['code']);
				$template->data['text_footer'] = $language->get('text_footer');

				if (file_exists(DIR_IMAGE . $voucher['image'])) {
					$template->data['image'] = HTTP_IMAGE . $voucher['image'];
				} else {
					$template->data['image'] = '';
				}

				$template->data['store_name'] = $order_info['store_name'];
				$template->data['store_url'] = $order_info['store_url'];
				$template->data['message'] = nl2br($voucher['message']);

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/voucher.tpl')) {
					$html = $template->fetch($this->config->get('config_template') . '/template/mail/voucher.tpl');
				} else {
					$html = $template->fetch('default/template/mail/voucher.tpl');
				}

				$mail = new Mail();

				$mail->setFrom($this->config->get('config_email'), $order_info['store_name']);
				$mail->AddAddresses($voucher['to_email']);
				$mail->Subject = html_entity_decode(sprintf($language->get('text_subject'), $voucher['from_name']), ENT_QUOTES, 'UTF-8');
				$mail->MsgHTML($html);
				$mail->send();
			}
		}
	}

	public function redeem($voucher_id, $order_id, $amount) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$this->db->insert('voucher_history', array('voucher_id' => (int)$voucher_id, 'order_id' => (int)$order_id, 'amount' => (float)$amount));
	}
}
?>