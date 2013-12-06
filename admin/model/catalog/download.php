<?php
class ModelCatalogDownload extends Model {
	public function addDownload($data) {
      	$this->db->insert('download', $data);

      	$download_id = $this->db->getLastId();

      	foreach ($data['download_description'] as $language_id => $value) {
      		$value['download_id'] = (int)$download_id;

      		$value['language_id'] = (int)$language_id;

      		$this->db->insert('download_description', $value);
      	}
	}

	public function editDownload($download_id, $data) {
		if (!empty($data['update'])) {
			$download_info = $this->getDownload($download_id);

			if ($download_info) {
      			$this->db->update('order_download', $data, array('filename' => $download_info['filename']));
			}
		}

        $this->db->update('download', $data, array('download_id' => (int)$download_id));

      	$this->db->delete('download_description', array('download_id' => (int)$download_id));

      	foreach ($data['download_description'] as $language_id => $value) {
            $value['download_id'] = (int)$download_id;

      		$value['language_id'] = (int)$language_id;

        	$this->db->insert('download_description', $value);
      	}
	}

	public function deleteDownload($download_id) {
      	$this->db->delete('download', array('download_id' => (int)$download_id));
	  	$this->db->delete('download_description', array('download_id' => (int)$download_id));
	}

	public function getDownload($download_id) {
		$query = $this->db->get_where('download', array('download_id' => (int)$download_id));

		return $query->row;
	}

	public function getDownloads($data = array()) {
		$query = $this->db->from('download d')->join('download_description dd', 'd.download_id = dd.download_id')->where('dd.language_id', (int)$this->config->get('config_language_id'));

		$sort_data = array(
			'dd.name',
			'd.remaining'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'dd.name';
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

	public function getDownloadDescriptions($download_id) {
		$download_description_data = array();

		$query = $this->db->get_where('download_description', array('download_id' => (int)$download_id));

		foreach ($query->rows as $result) {
			$download_description_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $download_description_data;
	}

	public function getTotalDownloads() {
		return $this->db->count_all('download');
	}
}
?>