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
	
	config.baseHref = '';
	config.basePath = 'ckeditor/';
	config.filebrowserBrowseUrl = 'ckfinder/ckfinder.html.php';
	config.filebrowserImageBrowseUrl = 'ckfinder/ckfinder.html.php?Type=Images';
	config.filebrowserFlashBrowseUrl = 'ckfinder/ckfinder.html.php?Type=Flash';
	config.filebrowserUploadUrl = 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
	config.filebrowserImageUploadUrl = 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
	config.filebrowserFlashUploadUrl = 'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
};

