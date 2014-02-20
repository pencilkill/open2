<?php
if(strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false){
	define('DB_DRIVER', 'mysql');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', '123456');
	define('DB_DATABASE', 'opencart');
	define('DB_PREFIX', '');
}elseif(strpos(strtolower($_SERVER['HTTP_HOST']),'works.tw')!==false){
	define('DB_DRIVER', 'mysql');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', '');
	define('DB_PASSWORD', '');
	define('DB_DATABASE', '');
	define('DB_PREFIX', '');
}else{
	define('DB_DRIVER', 'mysql');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', '');
	define('DB_PASSWORD', '');
	define('DB_DATABASE', '');
	define('DB_PREFIX', '');
}
?>