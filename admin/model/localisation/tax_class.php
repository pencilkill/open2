<?php
class ModelLocalisationTaxClass extends Model {
	public function addTaxClass($data) {
		$this->set('date_added', date('Y-m-d H:i:s'));
		$this->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->insert('tax_class', $data);

		$tax_class_id = $this->db->getLastId();

		if (isset($data['tax_rule'])) {
			foreach ($data['tax_rule'] as $tax_rule) {
				$tax_rule['tax_class_id'] = (int)$tax_class_id;

				$this->db->insert('tax_rule', $tax_rule);
			}
		}

		$this->cache->delete('tax_class');
	}

	public function editTaxClass($tax_class_id, $data) {
		$this->set('date_modified', date('Y-m-d H:i:s'));
		$this->db->update('tax_class', $data, array('tax_class_id' => (int)$tax_class_id));

		$this->db->delete('tax_rule', array('tax_class_id' => (int)$tax_class_id));

		if (isset($data['tax_rule'])) {
			foreach ($data['tax_rule'] as $tax_rule) {
				$tax_rule['tax_class_id'] = (int)$tax_class_id;

				$this->db->insert('tax_rule', $tax_rule);
			}
		}

		$this->cache->delete('tax_class');
	}

	public function deleteTaxClass($tax_class_id) {
		$this->db->delete('tax_class', array('tax_class_id' => (int)$tax_class_id));
		$this->db->delete('tax_rule', array('tax_class_id' => (int)$tax_class_id));

		$this->cache->delete('tax_class');
	}

	public function getTaxClass($tax_class_id) {
		$query = $this->db->get_where('tax_class', array('tax_class_id' => (int)$tax_class_id));

		return $query->row;
	}

	public function getTaxClasses($data = array()) {
    	if ($data) {
			$query = $this->db->from('tax_class');

      		if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by('title', 'DESC');
			} else {
				$query->order_by('title', 'ASC');
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
		} else {
			$tax_class_data = $this->cache->get('tax_class');

			if (!$tax_class_data) {
				$query = $this->db->get('tax_class');

				$tax_class_data = $query->rows;

				$this->cache->set('tax_class', $tax_class_data);
			}

			return $tax_class_data;
		}
	}

	public function getTotalTaxClasses() {
		return $this->db->count_all('tax_class');
	}

	public function getTaxRules($tax_class_id) {
		$query = $this->db->get_where('tax_rule', array('tax_class_id' => (int)$tax_class_id));

		return $query->rows;
	}

	public function getTotalTaxRulesByTaxRateId($tax_rate_id) {
      	$query = $this->db->select('COUNT(DISTINCT ' . $this->db->protect_identifiers('tax_class_id') . ') AS total', false)->get_where('tax_rule', array('tax_rate_id' => (int)$tax_rate_id));

		return $query->row['total'];
	}
}
?>