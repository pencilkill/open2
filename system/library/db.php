<?php
/**
 *
 * @author Sam <mail.song.de.qiang@gmail.com>
 * This DB class was not the opencart orignal DB class
 * It is using codeigniter DAO which was a little changed to be compatible with opencart
 * Try to read the driver method named 'query' and the result class method named 'initialize'
 *
 * Most of all DAO method access except cache cause the opencart has cache system owned itself
 *
 */
class DB {
	private $driver=null;
	private $params=array();

	public function __construct($driver, $hostname, $username, $password, $database, $dbprefix = '', $char_set = 'UTF-8') {
		require_once(DIR_DATABASE.'CI_function.php');

		require_once(DIR_DATABASE.'DB_driver.php');

 		require_once(DIR_DATABASE.'DB_active_rec.php');

		if ( ! class_exists('CI_DB'))
		{
			eval('class CI_DB extends CI_DB_active_record { }');
		}

		require_once(DIR_DATABASE.'drivers/'.$driver.'/'.$driver.'_driver.php');

		$CI_DB_new_driver = 'CI_DB_'.$driver.'_driver';

		$params = array(
			'hostname'	=> $hostname,
			'username'	=> $username,
			'password'	=> $password,
			'database'	=> $database,
			'dbprefix'  => $dbprefix,
			'char_set'  => $char_set
		);
		//
		$this->params = $params;
		/*
		 * CI_DB_mysql_driver 繼承 CI_DB
		 */
		$this->driver = new $CI_DB_new_driver($params);

		$this->driver->initialize();

		if (! $this->driver->conn_id) {
			exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
		}
  	}

  	public function countAffected() {
    	return $this->driver->affected_rows();
  	}

  	public function getLastId() {
  		return $this->driver->insert_id();
  	}

	public function __call($method,$args){
	   return call_user_func_array(array($this->driver,$method),$args);
	}
}
?>