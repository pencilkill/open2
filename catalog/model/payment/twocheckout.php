<?php
class ModelPaymentTwoCheckout extends Model {
  	public function getMethod($address, $total) {
		$this->load->language('payment/twocheckout');

		$query = $this->db->from('zone_to_geo_zone')
			->where(array('geo_zone_id' => (int)$this->config->get('twocheckout_geo_zone_id'), 'country_id' => (int)$address['country_id']))
			->where("(zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')", NULL ,false)
			->get();

		if ($this->config->get('twocheckout_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('twocheckout_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
      		$method_data = array(
        		'code'       => 'twocheckout',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('twocheckout_sort_order')
      		);
    	}

    	return $method_data;
  	}
}
?>