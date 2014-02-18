<?php
class ModelLocalisationCurrency extends Model {
	public function getCurrencyByCode($currency) {
		$query = $this->db->distinct()->get_where('currency', array('code' => $currency));

		return $query->row;
	}

	public function getCurrencies() {
		$currency_data = $this->cache->get('currency');

		if (!$currency_data) {
			$currency_data = array();

			$query = $this->db->from('currency')->order_by('title', 'ASC');

			foreach ($query->get()->rows as $result) {
      			$currency_data[$result['code']] = $result;
    		}

			$this->cache->set('currency', $currency_data);
		}

		return $currency_data;
	}
}
?>