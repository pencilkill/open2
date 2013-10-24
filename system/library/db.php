<?php
class DB {
	private $connection;
	private $active_record;
	private $driver=null;

	public function __construct($driver, $hostname, $username, $password, $database, $dbprefix = '', $active_record =true) {
		require_once(DIR_DATABASE.'CI_function.php');
		require_once(DIR_DATABASE.'DB_driver.php');
		if ($active_record)
		{
 			require_once(DIR_DATABASE.'DB_active_rec.php');

			if ( ! class_exists('CI_DB'))
			{
				eval('class CI_DB extends CI_DB_active_record { }');
			}
		}
		else
		{
			if ( ! class_exists('CI_DB'))
			{
				eval('class CI_DB extends CI_DB_driver { }');
			}
		}
		require_once(DIR_DATABASE.'drivers/'.$driver.'/'.$driver.'_driver.php');

		$CI_DB_new_driver = 'CI_DB_'.$driver.'_driver';

		$params = array(
			'hostname'	=> $hostname,
			'username'	=> $username,
			'password'	=> $password,
			'database'	=> $database,
			'dbprefix'  => $dbprefix
		);
		/*
		 * CI_DB_mysql_driver 繼承 CI_DB
		 */
		$this->driver = new $CI_DB_new_driver($params);
		$this->driver->initialize();
		$this->connection = $this->driver->conn_id;
		$this->active_record  =(boolean)$active_record;
		if (!$this->connection) {
			exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
		}
  	}

  	public function query($sql) {
		$result = $this->driver->query($sql);
  		if(is_object($result)&& $result instanceof CI_DB_result){
			$query = new stdClass();
			$query->row = $result->row_array();
			$query->rows = $result->result_array();
			$query->num_rows = $result->num_rows();

			return $query;
  		}else{
  			return true;
  		}
  	}

	public function escape($value) {
		if($this->active_record){
			return $value;
		}
		return $this->driver->escape($value);
	}

  	public function countAffected() {
    	return $this->driver->affected_rows();
  	}

  	public function getLastId() {
  		return $this->driver->insert_id();
  	}

	public function __destruct() {
		//$this->driver->close($this->connection);
	}

	public function __call($method,$args){
	   return call_user_func_array(array($this->driver,$method),$args);
	}
}
?>