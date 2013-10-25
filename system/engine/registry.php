<?php
final class Registry {
	private static $instance;

	private $data = array();

	public function __construct(){
		self::$instance = & $this;
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : NULL);
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function has($key) {
    	return isset($this->data[$key]);
  	}

  	public static function instance(){
  		return self::$instance;
  	}
}
?>