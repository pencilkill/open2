<?php
class ModelDesignBanner extends Model {
	public function getBanner($banner_id) {
		$query = $this->db->from('banner_image bi')
			->join('banner_image_description bid', 'bi.banner_image_id  = bid.banner_image_id')
			->where(array('bi.banner_id' => (int)$banner_id, 'bid.language_id' => (int)$this->config->get('config_language_id')))
			->get();

		return $query->rows;
	}
}
?>