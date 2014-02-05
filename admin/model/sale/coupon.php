<?php
class ModelSaleCoupon extends Model {
	public function addCoupon($data) {
		$this->db->set('date_added', date('Y-m-d H:i:s'));
      	$this->db->insert('coupon',  $data);

      	$coupon_id = $this->db->getLastId();

		if (isset($data['coupon_product'])) {
      		foreach ($data['coupon_product'] as $product_id) {
      			$this->db->set('coupon_id', (int)$coupon_id);
      			$this->db->set('product_id', (int)$product_id);
        		$this->db->insert('coupon_product');
      		}
		}
	}

	public function editCoupon($coupon_id, $data) {
		$this->db->update('coupon', $data, array('coupon_id' => $coupon_id));

		$this->db->delete('coupon_product', array('coupon_id' => (int)$coupon_id));

		if (isset($data['coupon_product'])) {
      		foreach ($data['coupon_product'] as $product_id) {
      			$this->db->set('coupon_id', (int)$coupon_id);
      			$this->db->set('product_id', (int)$product_id);
				$this->db->insert('coupon_product');
      		}
		}
	}

	public function deleteCoupon($coupon_id) {
      	$this->db->delete('coupon', array('coupon_id' => (int)$coupon_id));
      	$this->db->delete('coupon', array('coupon_product' => (int)$coupon_id));
      	$this->db->delete('coupon', array('coupon_history' => (int)$coupon_id));
	}

	public function getCoupon($coupon_id) {
      	$query = $this->db->distinct()->get_where('coupon', array('coupon_id' => (int)$coupon_id));

		return $query->row;
	}

	public function getCouponByCode($code) {
      	$query = $this->db->distinct()->get_where('coupon', array('code' => $code));

		return $query->row;
	}

	public function getCoupons($data = array()) {
		$query = $this->db->select('coupon_id, name, code, discount, date_start, date_end, status')->from('coupon');

		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'name';
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

	public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();

		$query = $this->db->get_where('coupon_product', array('coupon_id' => (int)$coupon_id));

		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}

	public function getTotalCoupons() {
      	return $this->db->count_all('coupon');
	}

	public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->select("ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added")
			->from('coupon_history ch')
			->join('customer c', 'ch.customer_id = c.customer_id')
			->where('ch.coupon_id', (int)$coupon_id)
			->order_by('ch.date_added', 'ASC')
			->limit((int)$limit, (int)$start);

		return $query->get()->rows;
	}

	public function getTotalCouponHistories($coupon_id) {
	  	$query = $this->db->select('COUNT(*) AS total')->from('coupon_history')->where('coupon_id', (int)$coupon_id);

		return $query->get()->row['total'];
	}
}
?>