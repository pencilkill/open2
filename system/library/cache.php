<?php
class Cache {
	const DIRECTORY_CACHE = DIR_CACHE;
	const PREFIX_CACHE = 'cache';

	private $expire = 3600;

	public function get($key) {
		$files = glob(self::DIRECTORY_CACHE . self::PREFIX_CACHE . '.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
			$cache = file_get_contents($files[0]);

			$data = unserialize($cache);

			foreach ($files as $file) {
				$time = substr(strrchr($file, '.'), 1);

      			if ($time < time()) {
					if (file_exists($file)) {
						unlink($file);
					}
      			}
    		}

			return $data;
		}
	}

  	public function set($key, $value) {
    	$this->delete($key);

		$file = self::DIRECTORY_CACHE . self::PREFIX_CACHE . '.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $this->expire);

		$handle = fopen($file, 'w');

    	fwrite($handle, serialize($value));

    	fclose($handle);
  	}

  	public function delete($key) {
		$files = glob(self::DIRECTORY_CACHE . self::PREFIX_CACHE . '.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
    		foreach ($files as $file) {
      			if (file_exists($file)) {
					unlink($file);
				}
    		}
		}
  	}

  	public function clear($files) {
		if ($files) {
    		foreach ($files as $file) {
    			$file = self::DIRECTORY_CACHE . $file;
      			if (file_exists($file)) {
					unlink($file);
				}
    		}
		}
  	}

  	public function getCaches() {
		$files = array();

		foreach(glob(self::DIRECTORY_CACHE . self::PREFIX_CACHE . '.*.*') as $file){
			$files[] = strtr($file, array(self::DIRECTORY_CACHE => ''));
		}

		return $files ? $files : array();
  	}
}
?>