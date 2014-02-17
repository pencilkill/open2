<?php
class ModelPaymentPayMate extends Model {
  	public function getMethod($address, $total) {
		$this->load->language('payment/paymate');

		$query = $this->db->from('zone_to_geo_zone')
			->where(array('geo_zone_id' => (int)$this->config->get('paymate_geo_zone_id'), 'country_id' => (int)$address['country_id']))
			->where("(zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')", NULL ,false)
			->get();

		if ($this->config->get('paymate_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('paymate_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$currencies = array(
			'AUD',
			'NZD',
			'USD',
			'EUR',
			'GBP'
		);

		if (!in_array(strtoupper($this->currency->getCode()), $currencies)) {
			$status = false;
		}

		$method_data = array();

		if ($status) {
      		$method_data = array(
        		'code'       => 'paymate',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('paymate_sort_order')
      		);
    	}

    	return $method_data;
  	}
}
?>
