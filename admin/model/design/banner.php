<?php
class ModelDesignBanner extends Model {
	public function addBanner($data) {
		$this->db->insert('banner', $data);

		$banner_id = $this->db->getLastId();

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $banner_image) {
				$banner_image['banner_id'] = (int)$banner_id;

				$this->db->insert('banner_image', $banner_image);

				$banner_image_id = $this->db->getLastId();

				foreach ($banner_image['banner_image_description'] as $language_id => $banner_image_description) {
					$banner_image_description['banner_id'] = (int)$banner_id;

					$banner_image_description['banner_image_id'] = (int)$banner_image_id;

					$banner_image_description['language_id'] = (int)$language_id;

					$this->db->insert('banner_image_description', $banner_image_description);
				}
			}
		}
	}

	public function editBanner($banner_id, $data) {
		$this->db->update('banner', $data, array('banner_id' => (int)$banner_id));

		$this->db->delete('banner_image', array('banner_id' => (int)$banner_id));
		$this->db->delete('banner_image_description', array('banner_id' => (int)$banner_id));

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $banner_image) {
				$banner_image['banner_id'] = (int)$banner_id;

				$this->db->insert('banner_image', $banner_image);

				$banner_image_id = $this->db->getLastId();

				foreach ($banner_image['banner_image_description'] as $language_id => $banner_image_description) {
					$banner_image_description['banner_id'] = (int)$banner_id;

					$banner_image_description['banner_image_id'] = (int)$banner_image_id;

					$banner_image_description['language_id'] = (int)$language_id;

					$this->db->insert('banner_image_description', $banner_image_description);
				}
			}
		}
	}

	public function deleteBanner($banner_id) {
		$this->db->delete('banner', array('banner_id' => (int)$banner_id));
		$this->db->delete('banner_image', array('banner_id' => (int)$banner_id));
		$this->db->delete('banner_image_description', array('banner_id' => (int)$banner_id));
	}

	public function getBanner($banner_id) {
		$query = $this->db->distinct()->get_where('banner', array('banner_id' => (int)$banner_id));

		return $query->row;
	}

	public function getBanners($data = array()) {
		$query = $this->db->from('banner');

		$sort_data = array(
			'name',
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

	public function getBannerImages($banner_id) {
		$banner_image_data = array();

		$banner_image_query = $this->db->get_where('banner_image', array('banner_id' => (int)$banner_id));

		foreach ($banner_image_query->rows as $banner_image) {
			$banner_image_description_data = array();

			$banner_image_description_query = $this->db->get_where('banner_image_description', array('banner_id' => (int)$banner_id, 'banner_image_id' => (int)$banner_image['banner_image_id']));

			foreach ($banner_image_description_query->rows as $banner_image_description) {
				$banner_image_description_data[$banner_image_description['language_id']] = array('title' => $banner_image_description['title']);
			}

			$banner_image_data[] = array(
				'banner_image_description' => $banner_image_description_data,
				'link'                     => $banner_image['link'],
				'image'                    => $banner_image['image']
			);
		}

		return $banner_image_data;
	}

	public function getTotalBanners() {
      	return $this->db->count_all('banner');
	}
}
?>