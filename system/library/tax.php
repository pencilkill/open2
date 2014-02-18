<?php
final class Tax {
	private $shipping_address;
	private $payment_address;
	private $store_address;

	public function __construct($registry) {
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->db = $registry->get('db');
		$this->session = $registry->get('session');

		if (isset($this->session->data['shipping_country_id']) || isset($this->session->data['shipping_zone_id'])) {
			$this->setShippingAddress($this->session->data['shipping_country_id'], $this->session->data['shipping_zone_id']);
		} elseif ($this->config->get('config_tax_default') == 'shipping') {
			$this->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		if (isset($this->session->data['payment_country_id']) || isset($this->session->data['payment_zone_id'])) {
			$this->setPaymentAddress($this->session->data['payment_country_id'], $this->session->data['payment_zone_id']);
		} elseif ($this->config->get('config_tax_default') == 'payment') {
			$this->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		$this->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
  	}

	public function setShippingAddress($country_id, $zone_id) {
		$this->shipping_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

	public function setPaymentAddress($country_id, $zone_id) {
		$this->payment_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

	public function setStoreAddress($country_id, $zone_id) {
		$this->store_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}

  	public function calculate($value, $tax_class_id, $calculate = true) {
		if ($tax_class_id && $calculate) {
			$amount = $this->getTax($value, $tax_class_id);

			return $value + $amount;
		} else {
      		return $value;
    	}
  	}

  	public function getTax($value, $tax_class_id) {
		$amount = 0;

		$tax_rates = $this->getRates($value, $tax_class_id);

		foreach ($tax_rates as $tax_rate) {
			$amount += $tax_rate['amount'];
		}

		return $amount;
  	}

	public function getRateName($tax_rate_id) {
		$tax_query = $this->db->select('name')->get_where('tax_rate', array('tax_rate_id' => (int)$tax_rate_id));

		if ($tax_query->num_rows) {
			return $tax_query->row['name'];
		} else {
			return false;
		}
	}

    public function getRates($value, $tax_class_id) {
		$tax_rates = array();

		if ($this->customer->isLogged()) {
			$customer_group_id = $this->customer->getCustomerGroupId();
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		if ($this->shipping_address) {
			$tax_query = $this->db->select('tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority')
				->from('tax_rule tr1')
				->join('tax_rate tr2', 'tr1.tax_rate_id = tr2.tax_rate_id')
				->join('tax_rate_to_customer_group tr2cg', 'tr2.tax_rate_id = tr2cg.tax_rate_id', 'inner')
				->join('zone_to_geo_zone z2gz', 'tr2.geo_zone_id = z2gz.geo_zone_id')
				->join('geo_zone gz', 'tr2.geo_zone_id = gz.geo_zone_id')
				->where(array('tr1.tax_class_id' => (int)$tax_class_id, 'tr1.based' => 'shipping', 'tr2cg.customer_group_id' => (int)$customer_group_id, 'z2gz.country_id' => (int)$this->shipping_address['country_id']))
				->where("(z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->shipping_address['zone_id'] . "')")
				->order_by('tr1.priority', 'ASC')
				->get();

			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = $result;
			}
		}

		if ($this->payment_address) {
			$tax_query = $this->db->select('tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority')
				->from('tax_rule tr1')
				->join('tax_rate tr2', 'tr1.tax_rate_id = tr2.tax_rate_id')
				->join('tax_rate_to_customer_group tr2cg', 'tr2.tax_rate_id = tr2cg.tax_rate_id', 'inner')
				->join('zone_to_geo_zone z2gz', 'tr2.geo_zone_id = z2gz.geo_zone_id')
				->join('geo_zone gz', 'tr2.geo_zone_id = gz.geo_zone_id')
				->where(array('tr1.tax_class_id' => (int)$tax_class_id, 'tr1.based' => 'payment', 'tr2cg.customer_group_id' => (int)$customer_group_id, 'z2gz.country_id' => (int)$this->payment_address['country_id']))
				->where("(z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->payment_address['zone_id'] . "')")
				->order_by('tr1.priority', 'ASC')
				->get();

			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = $result;
			}
		}

		if ($this->store_address) {
			$tax_query = $this->db->select('tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority')
				->from('tax_rule tr1')
				->join('tax_rate tr2', 'tr1.tax_rate_id = tr2.tax_rate_id')
				->join('tax_rate_to_customer_group tr2cg', 'tr2.tax_rate_id = tr2cg.tax_rate_id', 'inner')
				->join('zone_to_geo_zone z2gz', 'tr2.geo_zone_id = z2gz.geo_zone_id')
				->join('geo_zone gz', 'tr2.geo_zone_id = gz.geo_zone_id')
				->where(array('tr1.tax_class_id' => (int)$tax_class_id, 'tr1.based' => 'payment', 'tr2cg.customer_group_id' => (int)$customer_group_id, 'z2gz.country_id' => (int)$this->store_address['country_id']))
				->where("(z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->store_address['zone_id'] . "')")
				->order_by('tr1.priority', 'ASC')
				->get();


			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = $result;
			}
		}

		$tax_rate_data = array();

		foreach ($tax_rates as $tax_rate) {
			if (isset($tax_rate_data[$tax_rate['tax_rate_id']])) {
				$amount = $tax_rate_data[$tax_rate['tax_rate_id']]['amount'];
			} else {
				$amount = 0;
			}

			if ($tax_rate['type'] == 'F') {
				$amount += $tax_rate['rate'];
			} elseif ($tax_rate['type'] == 'P') {
				$amount += ($value / 100 * $tax_rate['rate']);
			}

			$tax_rate_data[$tax_rate['tax_rate_id']] = array(
				'tax_rate_id' => $tax_rate['tax_rate_id'],
				'name'        => $tax_rate['name'],
				'rate'        => $tax_rate['rate'],
				'type'        => $tax_rate['type'],
				'amount'      => $amount
			);
		}

		return $tax_rate_data;
	}

  	public function has($tax_class_id) {
		return isset($this->taxes[$tax_class_id]);
  	}
}
?>