<?php
class User {
	private $user_id;
	private $username;
  	private $permission = array();

  	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');

    	if (isset($this->session->data['user_id'])) {
			$user_query = $this->db->get_where('user', array('user_id' => (int)$this->session->data['user_id'], 'status' => 1));

			if ($user_query->num_rows) {
				$this->user_id = $user_query->row['user_id'];
				$this->username = $user_query->row['username'];

      			$this->db->update('user', array('ip' => $this->request->server['REMOTE_ADDR']), array('user_id' => (int)$this->session->data['user_id']));

      			$user_group_query = $this->db->select('permission')->get_where('user_group', array('user_group_id' => (int)$user_query->row['user_group_id']));

	  			$permissions = unserialize($user_group_query->row['permission']);

				if (is_array($permissions)) {
	  				foreach ($permissions as $key => $value) {
	    				$this->permission[$key] = $value;
	  				}
				}
			} else {
				$this->logout();
			}
    	}
  	}

  	public function login($username, $password) {
    	$user_query = $this->db->from('user')
    		->where(array('username' => $username, 'status' => 1))
    		->where('(password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1(' . $this->db->escape($password) . '))))) OR password = ' . $this->db->escape(md5($password)) . ')', NULL, false)
    		->get();

    	if ($user_query->num_rows) {
			$this->session->data['user_id'] = $user_query->row['user_id'];

			$this->user_id = $user_query->row['user_id'];
			$this->username = $user_query->row['username'];

      		$user_group_query = $this->db->select('permission')->get_where('user_group', array('user_group_id' => (int)$user_query->row['user_group_id']));

	  		$permissions = unserialize($user_group_query->row['permission']);

			if (is_array($permissions)) {
				foreach ($permissions as $key => $value) {
					$this->permission[$key] = $value;
				}
			}

      		return true;
    	} else {
      		return false;
    	}
  	}

  	public function logout() {
		unset($this->session->data['user_id']);

		$this->user_id = '';
		$this->username = '';

		session_destroy();
  	}

  	public function hasPermission($key, $value) {
    	if (isset($this->permission[$key])) {
	  		return in_array($value, $this->permission[$key]);
		} else {
	  		return false;
		}
  	}

  	public function isLogged() {
    	return $this->user_id;
  	}

  	public function getId() {
    	return $this->user_id;
  	}

  	public function getUserName() {
    	return $this->username;
  	}
}
?>