<?php
class ModelCatalogNews extends Model {
	public function getNews($news_id) {
		$query = $this->db->from('news n')
			->join('news_description nd', 'n.news_id = nd.news_id')
			->where(array('nd.language_id' => (int)$this->config->get('config_language_id'), 'n.news_id' => (int)$news_id))
			->get();

		return $query->row;
	}

	public function getNewses($start=0,$limit=10, $kw=null) {
		$query = $this->db->from('news n')
			->join('news_description nd', 'n.news_id = nd.news_id')
			->where(array('nd.language_id' => (int)$this->config->get('config_language_id'), 'n.status' => 1));

		if($kw){
			$query->where("nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')");
		}

		$query->order_by('n.sort_order', 'DESC')->order_by('n.date_added', 'DESC');

		return $query->get()->rows;
	}

	public function getTotalNews($kw=null) {
      	$query = $this->db->select('COUNT(*) AS total')->from('news n')
			->join('news_description nd', 'n.news_id = nd.news_id')
			->where(array('nd.language_id' => (int)$this->config->get('config_language_id'), 'n.status' => 1));

		if($kw){
			$query->where("nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')");
		}

		return $query->get()->row['total'];
	}

	public function getLastNews($news_id, $sort_order, $date_added) {
		$query = $this->db->from('news n')
			->join('news_description nd', 'n.news_id = nd.news_id')
			->where(array('nd.language_id' => (int)$this->config->get('config_language_id'), 'n.status' => 1))
			->where("((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) >= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order > '" . $sort_order . "')) AND n.news_id <> '" . $news_id . "'", NULL, false)
			->order_by('n.sort_order', 'ASC')
			->order_by('n.date_added', 'ASC')
			->limit(1, 0);


		return $query->get()->row;
	}

	public function getNextNews($news_id, $sort_order, $date_added) {
		$query = $this->db->from('news n')
			->join('news_description nd', 'n.news_id = nd.news_id')
			->where(array('nd.language_id' => (int)$this->config->get('config_language_id'), 'n.status' => 1))
			->where("((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) <= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order < '" . $sort_order . "')) AND n.news_id <> '" . $news_id . "'", NULL, false)
			->order_by('n.sort_order', 'DESC')
			->order_by('n.date_added', 'DESC')
			->limit(1, 0);

		return $query->get()->row;
	}

	public function getNewsLayoutId($news_id) {
		$query = $this->db->get_where('news_to_layout', array('news_id' => (int)$news_id, 'store_id' => (int)$this->config->get('config_store_id')));

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_news');
		}
	}
}
?>