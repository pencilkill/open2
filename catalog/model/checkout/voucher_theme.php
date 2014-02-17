<?php
class ModelCheckoutVoucherTheme extends Model {
	public function getVoucherTheme($voucher_theme_id) {
		$query = $this->db->from('voucher_theme vt')
			->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')
			->where(array('vt.voucher_theme_id' => (int)$voucher_theme_id, 'vtd.language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getVoucherThemes($data = array()) {
      	if ($data) {
      		$query = $this->db->from('voucher_theme vt')
      			->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')
      			->where(array('vtd.language_id' => (int)$this->config->get('config_language_id')));

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by('vtd.name', 'DESC');
			} else {
				$query->order_by('vtd.name', 'ASC');
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
			$voucher_theme_data = $this->cache->get('voucher_theme.' . (int)$this->config->get('config_language_id'));

			if (!$voucher_theme_data) {
				$query = $this->db->from('voucher_theme vt')
	      			->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')
	      			->where(array('vtd.language_id' => (int)$this->config->get('config_language_id')))
					->order_by('vtd.name', 'DESC')
					->get();

				$voucher_theme_data = $query->rows;

				$this->cache->set('voucher_theme.' . (int)$this->config->get('config_language_id'), $voucher_theme_data);
			}

			return $voucher_theme_data;
		}
	}
}
?>