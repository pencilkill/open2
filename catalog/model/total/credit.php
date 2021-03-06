<?php
class ModelTotalCredit extends Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		if ($this->config->get('credit_status')) {
			$this->load->language('total/credit');

			$balance = $this->customer->getBalance();

			if ((float)$balance) {
				if ($balance > $total) {
					$credit = $total;
				} else {
					$credit = $balance;
				}

				if ($credit > 0) {
					$total_data[] = array(
						'code'       => 'credit',
						'title'      => $this->language->get('text_credit'),
						'text'       => $this->currency->format(-$credit),
						'value'      => -$credit,
						'sort_order' => $this->config->get('credit_sort_order')
					);

					$total -= $credit;
				}
			}
		}
	}

	public function confirm($order_info, $order_total) {
		$this->load->language('total/credit');

		if ($order_info['customer_id']) {
			$this->db->insert('customer_transaction', array(
				'customer_id' => (int)$order_info['customer_id'],
				'order_id' => (int)$order_info['order_id'],
				'description' => sprintf($this->language->get('text_order_id'), (int)$order_info['order_id']),
				'amount' => (float)$order_total['value'],
				'date_added' => date('Y-m-d H:i:s'),
			));
		}
	}
}
?>