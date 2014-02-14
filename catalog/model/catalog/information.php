<?php
class ModelCatalogInformation extends Model {
	public function getInformation($information_id) {
		$query = $this->db->distinct()->from('information i')
			->join('information_description id', 'i.information_id = id.information_id')
			->join('information_to_store i2s', 'i.information_id = i2s.information_id')
			->where(array('i.information_id' => (int)$information_id, 'id.language_id' => (int)$this->config->get('config_language_id'), 'i2s.store_id' => (int)$this->config->get('config_store_id'), 'i.status' => 1))
			->get();

		return $query->row;
	}

	public function getInformations() {
		$query = $this->db->from('information i')
			->join('information_description id', 'i.information_id = id.information_id')
			->join('information_to_store i2s', 'i.information_id = i2s.information_id')
			->where(array('id.language_id' => (int)$this->config->get('config_language_id'), 'i2s.store_id' => (int)$this->config->get('config_store_id'), 'i.status' => 1))
			->order_by('i.sort_order', 'ASC')
			->order_by('LCASE(id.title)', 'ASC')
			->get();

		return $query->rows;
	}

	public function getInformationLayoutId($information_id) {
		$query = $this->db->get_where('information_to_layout', array('information_id' => (int)$information_id, 'store_id' => (int)$this->config->get('config_store_id')));

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_information');
		}
	}
}
?>