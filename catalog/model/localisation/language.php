<?php
class ModelLocalisationLanguage extends Model {
	public function getLanguage($language_id) {
		$query = $this->db->get_where('language', array('language_id' => (int)$language_id));

		return $query->row;
	}

	public function getLanguages() {
		$language_data = $this->cache->get('language');

		if (!$language_data) {
			$language_data = array();

			$query = $this->db->from('language')->order_by('sort_order', 'ASC')->order_by('name', 'ASC');

    		foreach ($query->rows as $result) {
      			$language_data[$result['language_id']] = $result;
    		}

			$this->cache->set('language', $language_data);
		}

		return $language_data;
	}
}
?>