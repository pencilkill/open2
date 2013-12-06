<?php
class ModelCatalogReview extends Model {
	public function addReview($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
		$this->db->insert('review', $data);

		$this->cache->delete('product');
	}

	public function editReview($review_id, $data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$this->db->update('review', $data, array('review_id' => (int)$review_id));

		$this->cache->delete('product');
	}

	public function deleteReview($review_id) {
		$this->db->delete('review', array('review_id' => (int)$review_id));

		$this->cache->delete('product');
	}

	public function getReview($review_id) {
		$s1 = $this->db->select('pd.name')->from('product_description pd')->where('pd.product_id=' . $this->db->protected_identifiers('r.product_id'))->where('pd.language_id' , (int)$this->config->get('config_language_id'))->select_string();

		$query = $this->db->distinct()->select('*, (' . $s1 . ') AS product')->from('review r')->where('r.review_id', (int)$review_id)->get();

		return $query->row;
	}

	public function getReviews($data = array()) {
		$query = $this->db->select('r.review_id, pd.name, r.author, r.rating, r.status, r.date_added')->from('review r')->join('product_description pd', 'r.product_id = pd.product_id')->where('pd.language_id', (int)$this->config->get('config_language_id'));

		$sort_data = array(
			'pd.name',
			'r.author',
			'r.rating',
			'r.status',
			'r.date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'r.date_added';
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
	}

	public function getTotalReviews() {
		return $this->db->count_all('review');
	}

	public function getTotalReviewsAwaitingApproval() {
		return $this->db->where('status', 0)->count_all_results('review');
	}
}
?>