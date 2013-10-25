<?php
// DIR_PATH
define('DIR_PATH', strtr(dirname(__FILE__) . '/', array('\\'=>'/','\/'=>'/')));

// HTTP
define('HTTP_PATH', strtr('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'], array(strtr($_SERVER['SCRIPT_FILENAME'], array(DIR_PATH=>''))=>'')));
define('HTTP_SERVER', HTTP_PATH);
define('HTTP_IMAGE', HTTP_PATH . 'upload/');

// HTTPS
if(isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))){
define('HTTPS_PATH', strtr('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'], array(strtr($_SERVER['SCRIPT_FILENAME'], array(DIR_PATH=>''))=>'')));
}else{
define('HTTPS_PATH', HTTP_PATH);
}
define('HTTPS_SERVER', HTTPS_PATH);
define('HTTPS_IMAGE', HTTPS_PATH . 'upload/');

// DIR
define('DIR_APPLICATION', DIR_PATH . 'catalog/');
define('DIR_SYSTEM', DIR_PATH . 'system/');
define('DIR_DATABASE', DIR_PATH . 'system/database/');
define('DIR_LANGUAGE', DIR_PATH . 'catalog/language/');
define('DIR_TEMPLATE', DIR_PATH . 'catalog/view/theme/');
define('DIR_CONFIG', DIR_PATH . 'system/config/');
define('DIR_IMAGE', DIR_PATH . 'upload/');
define('DIR_CACHE', DIR_PATH . 'cache/');
define('DIR_DOWNLOAD', DIR_PATH . 'upload/download/');
define('DIR_LOGS', DIR_PATH . 'cache/logs/');

define('DIR_EXT', DIR_PATH . 'ext/');

// DB
require_once DIR_PATH . 'database.php';
?>