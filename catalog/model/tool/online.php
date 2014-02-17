<?php
class ModelToolOnline extends Model {
	public function whosonline($ip, $customer_id, $url, $referer) {
		$this->db->where("(UNIX_TIMESTAMP(`date_added`) + 3600) < UNIX_TIMESTAMP('" . date('Y-m-d H:i:s') . "')")->delete('customer_online');

		$this->db->replace('customer_online', array(
			'ip' => $ip,
			'customer_id' => (int)$customer_id,
			'url' => $url,
			'referer' => $referer,
			'date_added' => date('Y-m-d H:i:s')
		));
	}
}
?>