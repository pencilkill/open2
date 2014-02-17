<?php
class ModelCatalogReview extends Model {
	public function addReview($product_id, $data) {
		$this->db->set('customer_id', (int)$this->customer->getId());
		$this->db->set('product_id', (int)$product_id);
		$this->db->set('date_added', date('Y-m-d H:i:s'));

		$this->db->insert('review', $data);
	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->select('r.review_id, r.author, r.rating, r.text, p.product_id, pd.name, p.price, p.image, r.date_added')
			->from('review r')
			->join('product p', 'r.product_id = p.product_id')
			->join('product_description pd', 'p.product_id = pd.product_id')
			->where(array('p.product_id' => (int)$product_id, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p.status' => 1, 'r.status' => 1, 'pd.language_id' => (int)$this->config->get('config_language_id')))
			->order_by('r.date_added', 'DESC')
			->limit((int)$limit, (int)$start);

		return $query->rows;
	}

	public function getAverageRating($product_id) {
		$query = $this->db->select('AVG(rating) AS total')
			->from('review')
			->where(array('status' => 1, 'product_id' => (int)$product_id))
			->group_by('product_id')
			->get();

		if (isset($query->row['total'])) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalReviews() {
		$query = $this->db->select('COUNT(*) AS total')
			->from('review r')
			->join('product p', 'r.product_id = p.product_id')
			->where(array('p.date_available <= ' => date('Y-m-d H:i:s'), 'p.status' => 1, 'r.status' => 1));

		return $query->get()->row['total'];
	}

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->select('COUNT(*) AS total')
			->from('review r')
			->join('product p', 'r.product_id = p.product_id')
			->join('product_description pd', 'p.product_id = pd.product_id')
			->where(array('p.product_id' => (int)$product_id, 'p.date_available <= ' => date('Y-m-d H:i:s'), 'p.status' => 1, 'r.status' => 1, 'pd.language_id' => (int)$this->config->get('config_language_id')));

		return $query->get()->row['total'];
	}
}
?>