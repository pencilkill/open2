<?php
class ModelCatalogNews extends Model {
	public function getNews($news_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.news_id='".(int)$news_id."'");

		return $query->row;
	}

	public function getNewses($start=0,$limit=10, $kw=null) {
		$sql = "SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status='1' ORDER BY n.sort_order DESC,n.date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

		if($kw){
			$sql .= " AND (nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')";
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalNews($kw=null) {
      	$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status = '1'";

      	if($kw){
			$sql .= " AND (nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getLastNews($news_id, $sort_order, $date_added) {
		$sql = "SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status='1'";

		$sql .= " AND ((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) >= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order > '" . $sort_order . "')) AND n.news_id <> '" . $news_id . "'";

		$sql .= " ORDER BY n.sort_order ASC,n.date_added ASC LIMIT 0,1";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getNextNews($news_id, $sort_order, $date_added) {
		$sql = "SELECT * FROM " . DB_PREFIX . "news n LEFT JOIN " . DB_PREFIX . "news_description nd ON (n.news_id = nd.news_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status='1' ";

		$sql .= " AND ((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) <= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order < '" . $sort_order . "')) AND n.news_id <> '" . $news_id . "'";

		$sql .= " ORDER BY n.sort_order DESC,n.date_added DESC LIMIT 0,1";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getNewsLayoutId($news_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "news_to_layout WHERE news_id = '" . (int)$news_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_news');
		}
	}
}
?>