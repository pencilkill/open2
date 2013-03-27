<?php
final class MySQL {
	private $connection;
	private $active_record;
	private $driver=null;

	public function __construct($hostname, $username, $password, $database,$active_record =true) {
		require_once(DIR_DATABASE.'database/CI_constant.php');
		require_once(DIR_DATABASE.'database/CI_function.php');
		require_once(DIR_DATABASE.'database/database/DB_driver.php');
		if ($active_record)
		{
 			require_once(CI_BASEPATH.'database/DB_active_rec'.EXT);

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
		require_once(CI_BASEPATH.'database/drivers/mysql/mysql_driver.php');
		$params = array(
						'hostname'	=> $hostname,
						'username'	=> $username,
						'password'	=> $password,
						'database'	=> $database
		);
		/*
		 * CI_DB_mysql_driver 繼承 CI_DB
		 */
		$this->driver = new CI_DB_mysql_driver($params);
		$this->driver->initialize();
		$this->connection = $this->driver->conn_id;
		$this->active_record  =(boolean)$active_record;
		if (!$this->connection) {
			exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
		}
  	}

  	public function query($sql) {
		$sql_query = $this->driver->query($sql);
  		if(is_object($sql_query)&& $sql_query instanceof CI_DB_result){
			$query = new stdClass();
			$query->row = $sql_query->row_array();
			$query->rows = $sql_query->result_array();
			$query->num_rows = $sql_query->num_rows();

			return $query;
  		}else{
  			return true;
  		}
  	}

	public function escape($value) {
		if($this->active_record){
			return $value;
		}
		return mysql_real_escape_string($value, $this->connection);
	}

  	public function countAffected() {
    	return mysql_affected_rows($this->connection);
  	}

  	public function getLastId() {
  		return $this->driver->insert_id();
  	}

	public function __destruct() {
		//mysql_close($this->connection);
	}

	public function __call($method,$args){
	   return call_user_func_array(array($this->driver,$method),$args);
	}
}
?>