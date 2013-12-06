<?php
class ModelLocalisationCurrency extends Model {
	public function addCurrency($data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->insert('currency', $data);

		if ($this->config->get('config_currency_auto')) {
			$this->updateCurrencies(true);
		}

		$this->cache->delete('currency');
	}

	public function editCurrency($currency_id, $data) {
		$this->db->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('currency', $data, array('currency_id' => (int)$currency_id));

		$this->cache->delete('currency');
	}

	public function deleteCurrency($currency_id) {
		$this->db->delete('currency', array('currency_id' => (int)$currency_id));

		$this->cache->delete('currency');
	}

	public function getCurrency($currency_id) {
		$query = $this->db->distinct()->get_where('currency', array('currency_id' => (int)$currency_id));

		return $query->row;
	}

	public function getCurrencyByCode($currency) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "currency WHERE code = " . $this->db->escape($currency) . "");

		return $query->row;
	}

	public function getCurrencies($data = array()) {
		if ($data) {
			$query = $this->db->from('currency');

			$sort_data = array(
				'title',
				'code',
				'value',
				'date_modified'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sort = $data['sort'];
			} else {
				$sort = 'title';
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
		} else {
			$currency_data = $this->cache->get('currency');

			if (!$currency_data) {
				$currency_data = array();

				$query = $this->db->from('currency')->order_by('title', 'ASC')->get();

				foreach ($query->rows as $result) {
      				$currency_data[$result['code']] = array(
        				'currency_id'   => $result['currency_id'],
        				'title'         => $result['title'],
        				'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
      				);
    			}

				$this->cache->set('currency', $currency_data);
			}

			return $currency_data;
		}
	}

	public function updateCurrencies($force = false) {
		if (extension_loaded('curl')) {
			$data = array();

			if ($force) {
				$query = $this->db->from('currency')->where(array('code != ', $this->config->get('config_currency')))->get();
			} else {
				$query = $this->db->from('currency')->where(array('code != ', $this->config->get('config_currency'), 'date_modified < ' => date('Y-m-d H:i:s', strtotime('-1 day'))))->get();
			}

			foreach ($query->rows as $result) {
				$data[] = $this->config->get('config_currency') . $result['code'] . '=X';
			}

			$curl = curl_init();

			curl_setopt($curl, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

			$content = curl_exec($curl);

			curl_close($curl);

			$lines = explode("\n", trim($content));

			foreach ($lines as $line) {
				$currency = utf8_substr($line, 4, 3);
				$value = utf8_substr($line, 11, 6);

				if ((float)$value) {
					$this->db->update('currency', array('value' => (float)$value, 'date_modified' => date('Y-m-d H:i:s')), array('code' => $currency));
				}
			}

			$this->db->update('currency', array('value' => 1.00000, 'date_modified' => date('Y-m-d H:i:s')), array('code' => $this->config->get('config_currency')));

			$this->cache->delete('currency');
		}
	}

	public function getTotalCurrencies() {
		return $this->db->count_all('currency');
	}
}
?>