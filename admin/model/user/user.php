<?php
class ModelUserUser extends Model {
	public function addUser($data) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);

		$this->db-set('salt', $salt);
		$this->db-set('password', sha1($salt . sha1($salt . sha1($data['password']))));
		$this->db-set('date_added', date('Y-m-d H:i:s'));

		unset($data['password']);

		$this->db->insert('user', $data);
	}

	public function editUser($user_id, $data) {
		if ($data['password']) {
			$salt = substr(md5(uniqid(rand(), true)), 0, 9);

			$this->db-set('salt', $salt);
			$this->db-set('password', sha1($salt . sha1($salt . sha1($data['password']))));

		}

		unset($data['password']);

		$this->db->update('user', $data, array('user_id' => (int)$user_id));
	}

	public function editPassword($user_id, $password) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);

		$this->db-set('salt', $salt);
		$this->db-set('password', sha1($salt . sha1($salt . sha1($password))));

		$this->db->update('user', array('code' => ''), array('user_id' => (int)$user_id));
	}

	public function editCode($email, $code) {
		$this->db->update('user', array('code' => $code), array('email' => $email));
	}

	public function deleteUser($user_id) {
		$this->db->delete('user', array('user_id' => (int)$user_id));
	}

	public function getUser($user_id) {
		$query = $this->db->get_where('user', array('user_id' => (int)$user_id));

		return $query->row;
	}

	public function getUserByUsername($username) {
		$query = $this->db->get_where('user', array('username' => $username));

		return $query->row;
	}

	public function getUserByCode($code) {
		$query = $this->db->get_where('user', array('code' => $code, 'code != ' => ''));

		return $query->row;
	}

	public function getUsers($data = array()) {
		$query = $this->db->from('user');

		$sort_data = array(
			'username',
			'status',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = 'username';
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

	public function getTotalUsers() {
      	return $this->db->count_all('user');
	}

	public function getTotalUsersByGroupId($user_group_id) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('user', array('user_group_id' => (int)$user_group_id));

		return $query->row['total'];
	}

	public function getTotalUsersByEmail($email) {
      	$query = $this->db->select('COUNT(*) AS total')->get_where('user', array('email' => $email));

		return $query->row['total'];
	}
}
?>