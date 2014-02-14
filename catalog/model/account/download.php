<?php
class ModelAccountDownload extends Model {
	public function getDownload($order_download_id) {
		$query = $this->db->from('order_download od')
			->join('order o', 'od.order_id = o.order_id')
			->get_where(array('o.customer_id' => (int)$this->customer->getId(), 'o.order_status_id > ' => 0, 'o.order_status_id' => (int)$this->config->get('config_complete_status_id'), 'od.order_download_id' => (int)$order_download_id, 'od.remaining > ' => 0));

		return $query->row;
	}

	public function getDownloads($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->select('o.order_id, o.date_added, od.order_download_id, od.name, od.filename, od.remaining')
			->from('order_download od')
			->join('order o', 'od.order_id = o.order_id')
			->where(array('o.customer_id' => (int)$this->customer->getId(), 'o.order_status_id > ' => 0, 'o.order_status_id' => (int)$this->config->get('config_complete_status_id')))
			->order_by('o.date_added', 'DESC')
			->limit((int)$limit, (int)$start)
			->get();

		return $query->rows;
	}

	public function updateRemaining($order_download_id) {
		$this->db->set('remaining', '(remaining - 1)', false);

		$this->db->update('order_download', NULL, array('order_download_id' => (int)$order_download_id));
	}

	public function getTotalDownloads() {
		$query = $this->db->select('COUNT(*) AS total')
			->from('order_download od')
			->join('order o', 'od.order_id = o.order_id')
			->get_where(array('o.customer_id' => (int)$this->customer->getId(), 'o.order_status_id > ' => 0, 'o.order_status_id' => (int)$this->config->get('config_complete_status_id')));

		return $query->row['total'];
	}
}
?>