<?php
class ModelCheckoutCoupon extends Model {
	public function getCoupon($code) {
		$status = true;

		$coupon_query = $this->db->from('coupon')
			->where(array('code' => $code, 'status' => 1))
			->where("((date_start = '0000-00-00' OR date_start < '" . date('Y-m-d H:i:s') . "') AND (date_end = '0000-00-00' OR date_end > '" . date('Y-m-d H:i:s') . "'))", NULL, false)
			->get();

		if ($coupon_query->num_rows) {
			if ($coupon_query->row['total'] >= $this->cart->getSubTotal()) {
				$status = false;
			}

			$coupon_history_query = $this->db->select('COUNT(*) AS total')->get_where('coupon_history ch', array('ch.coupon_id' => (int)$coupon_query->row['coupon_id']));

			if ($coupon_query->row['uses_total'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_total'])) {
				$status = false;
			}

			if ($coupon_query->row['logged'] && !$this->customer->getId()) {
				$status = false;
			}

			if ($this->customer->getId()) {
				$coupon_history_query = $this->db->select('COUNT(*) AS total')->get_where('coupon_history ch', array('ch.coupon_id' => (int)$coupon_query->row['coupon_id'], 'ch.customer_id' => (int)$this->customer->getId()));

				if ($coupon_query->row['uses_customer'] > 0 && ($coupon_history_query->row['total'] >= $coupon_query->row['uses_customer'])) {
					$status = false;
				}
			}

			$coupon_product_data = array();

			$coupon_product_query = $this->db->get_where('coupon_product', array('coupon_id' => (int)$coupon_query->row['coupon_id']));

			foreach ($coupon_product_query->rows as $result) {
				$coupon_product_data[] = $result['product_id'];
			}

			if ($coupon_product_data) {
				$coupon_product = false;

				foreach ($this->cart->getProducts() as $product) {
					if (in_array($product['product_id'], $coupon_product_data)) {
						$coupon_product = true;

						break;
					}
				}

				if (!$coupon_product) {
					$status = false;
				}
			}
		} else {
			$status = false;
		}

		if ($status) {
			return array_merge($coupon_query->row, array(
				'product'       => $coupon_product_data,
			));
		}
	}

	public function redeem($coupon_id, $order_id, $customer_id, $amount) {
		$this->db->insert('coupon_history', array(
			'coupon_id' => (int)$coupon_id,
			'order_id' => (int)$order_id,
			'customer_id' => (int)$customer_id,
			'amount' => (float)$amount,
			'date_added' => date('Y-m-d H:i:s')
		));
	}
}
?>