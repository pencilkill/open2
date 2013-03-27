<?php
class ModelCatalogFaq extends Model {

	public function getFaq($faq_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.faq_id = '".(int)$faq_id."' AND n.status = '1'");

		return $query->row;
	}

	public function getFaqs($start=0,$limit=10,$kw=null) {
		$sql = "SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status = '1'";

		if($kw){
			$sql .= " AND (nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')";
		}

		$sql .= " ORDER BY n.sort_order DESC,n.date_added DESC LIMIT " . (int)$start . "," . (int)$limit;

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalFaqs($kw=null) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status = '1'";

		if($kw){
			$sql .= " AND (nd.title LIKE '%".$kw."%' OR nd.keyword LIKE '%".$kw."%')";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getLastFaq($faq_id, $sort_order, $date_added) {
		$sql = "SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status='1'";

		$sql .= " AND ((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) >= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order > '" . $sort_order . "')) AND n.faq_id <> '" . $faq_id . "'";

		$sql .= " ORDER BY n.sort_order ASC,n.date_added ASC LIMIT 0,1";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getNextFaq($faq_id, $sort_order, $date_added) {
		$sql = "SELECT * FROM " . DB_PREFIX . "faq n LEFT JOIN " . DB_PREFIX . "faq_description nd ON (n.faq_id = nd.faq_id) WHERE nd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND n.status='1' ";

		$sql .= " AND ((n.sort_order = '" . $sort_order . "' AND UNIX_TIMESTAMP(n.date_added) <= UNIX_TIMESTAMP('" . $date_added . "')) OR (n.sort_order < '" . $sort_order . "')) AND n.faq_id <> '" . $faq_id . "'";

		$sql .= " ORDER BY n.sort_order DESC,n.date_added DESC LIMIT 0,1";

		$query = $this->db->query($sql);

		return $query->row;
	}

}
?>