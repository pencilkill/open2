<?php
class ModelUserUserGroup extends Model {
	public function addUserGroup($data) {
		$data['permission'] = isset($data['permission']) ? serialize($data['permission']) : '';

		$this->db->insert('user_group', $data);
	}

	public function editUserGroup($user_group_id, $data) {
		$data['permission'] = isset($data['permission']) ? serialize($data['permission']) : '';

		$this->db->update('user_group', $data, array('user_group_id', (int)$user_group_id));
	}

	public function deleteUserGroup($user_group_id) {
		$this->db->delete('user_group', array('user_group_id', (int)$user_group_id));
	}

	public function addPermission($user_id, $type, $page) {
		$user_query = $this->db->distinct()->select('user_group_id')->get_where('user', array('user_id' => (int)$user_id));

		if ($user_query->num_rows) {
			$user_group_query = $this->db->distinct()->get_where('user_group', array('user_group_id' => (int)$user_query->row['user_group_id']));

			if ($user_group_query->num_rows) {
				$data = unserialize($user_group_query->row['permission']);

				$data[$type][] = $page;

				$this->db->update('user_group', array('permission' => serialize($data)), array('user_group_id' => (int)$user_query->row['user_group_id']));
			}
		}
	}

	public function getUserGroup($user_group_id) {
		$query = $this->db->distinct()->get_where('user_group', array('user_group_id', (int)$user_group_id));

		$user_group = array(
			'name'       => $query->row['name'],
			'permission' => unserialize($query->row['permission'])
		);

		return $user_group;
	}

	public function getUserGroups($data = array()) {
		$query = $this->db->from('user_group');

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$query->order_by('name', 'DESC');
		} else {
			$query->order_by('name', 'ASC');
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

	public function getTotalUserGroups() {
      	return $this->db->count_all('user_group');
	}
}
?>