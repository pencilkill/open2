<?php error_reporting(E_ALL ^E_NOTICE);ini_set('display_errors', 0);extract($_GET);header('content-type: application/x-javascript');?>
<?php
/**

This is an example for app which is not under webroot,
e.g webroot/admin/index.php
ckeditor aways works well for app which is under webroot directly
e.g webroot/index.php

Notice: both the ckeditor and ckfinder are customized to compatible with relative url
please check both of them by comparing the original version if you have any question

<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="../ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">CKEDITOR.config.customConfig = '../ckeditor/config.js.php?uri=../'</script>
*/
?>
/**
 * @license Copyright (c) 2003-2014, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */
CKEDITOR.editorConfig = function( config ) {
	/*
	Define changes to default configuration here. For example:
	config.language = 'fr';
	config.uiColor = '#AADC6E';
	*/
	config.baseHref = '<?php echo $uri?>';
	config.basePath = '<?php echo $uri?>ckeditor/';
	config.filebrowserBrowseUrl = '<?php echo $uri?>ckfinder/ckfinder.html.php';
	config.filebrowserImageBrowseUrl = '<?php echo $uri?>ckfinder/ckfinder.html.php?Type=Images';
	config.filebrowserFlashBrowseUrl = '<?php echo $uri?>ckfinder/ckfinder.html.php?Type=Flash';
	config.filebrowserUploadUrl = '<?php echo $uri?>ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
	config.filebrowserImageUploadUrl = '<?php echo $uri?>ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
	config.filebrowserFlashUploadUrl = '<?php echo $uri?>ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};