<?php
class ModelAccountOrder extends Model {
	public function getOrder($order_id) {
		$order_query = $this->db->get_where('order', array('order_id' => (int)$order_id, 'customer_id' => (int)$this->customer->getId(), 'order_status_id > ' => 0));

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

			return array_merge($order_query->row, array(
				'payment_zone_code'       => $payment_zone_code,
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
			));
		} else {
			return false;
		}
	}

	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 1;
		}

		$query = $this->db->select('o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value')
			->from('order o')
			->join('order_status os', 'o.order_status_id = os.order_status_id')
			->where(array('o.customer_id' => (int)$this->customer->getId(), 'o.order_status_id > ' => 0, 'os.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('o.order_id', 'DESC')
			->limit((int)$limit, (int)$start)
			->get();

		return $query->rows;
	}

	public function getOrderProducts($order_id) {
		$query = $this->db->get_where('order_product', array('order_id' => (int)$order_id));

		return $query->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->get_where('order_option', array('order_id' => (int)$order_id, 'order_product_id' => (int)$order_product_id));

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->get_where('order_voucher', array('order_id' => (int)$order_id));

		return $query->rows;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->from('order_total')->where(array('order_id' => (int)$order_id))->order_by('sort_order', 'ASC')->get();

		return $query->rows;
	}

	public function getOrderHistories($order_id) {
		$query = $this->db->select('date_added, os.name AS status, oh.comment, oh.notify')
			->from('order_history oh')
			->join('order_status os', 'oh.order_status_id = os.order_status_id')
			->where(array('oh.order_id' => (int)$order_id, 'oh.notify' => 1, 'os.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('oh.date_added', 'ASC')
			->get();

		return $query->rows;
	}

	public function getOrderDownloads($order_id) {
		$query = $this->db->from('order_download')->where('order_id', (int)$order_id)->order_by('name', 'ASC')->get();

		return $query->rows;
	}

	public function getTotalOrders() {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('order', array('customer_id' => (int)$this->customer->getId(), 'order_status_id > ' => 0));

		return $query->row['total'];
	}

	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('order_product', array('order_id' => (int)$order_id));

		return $query->row['total'];
	}

	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('order_voucher', array('order_id' => (int)$order_id));

		return $query->row['total'];
	}

}
?>