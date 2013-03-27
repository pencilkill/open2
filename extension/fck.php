<?php
/**
 * @author Sam, sam@ozchamp.net
 */
require_once DIR_PATH . "ckeditor/ckeditor.php";

final class Fck extends CKEditor{
	public function __construct($cfg=array()){
		parent::__construct();

		$_cfg = array();

		$_cfg['baseHref'] = HTTP_PATH ;
		$_cfg['filebrowserBrowseUrl'] = HTTP_PATH . "ckfinder/ckfinder.html";
		$_cfg['filebrowserImageBrowseUrl'] = HTTP_PATH . "ckfinder/ckfinder.html?Type=Images";
		$_cfg['filebrowserFlashBrowseUrl'] = HTTP_PATH . "ckfinder/ckfinder.html?Type=Flash";
		$_cfg['filebrowserUploadUrl'] = HTTP_PATH . "ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files";
		$_cfg['filebrowserImageUploadUrl'] = HTTP_PATH . "ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images";
		$_cfg['filebrowserFlashUploadUrl'] = HTTP_PATH . "ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash";

		$this->basePath = HTTP_PATH . 'ckeditor/';

		if(isset($cfg['basePath'])){
			 $this->basePath = $cfg['basePath'];
			 unset($cfg['basePath']);
		}

		$this->config = array_merge($_cfg, $cfg);
	}

	public static function htmlEdit($name, $value) {
		$CKEditor = new self();
		return $CKEditor->editor($name, $value);
	}

	public static function htmlReplace($id) {
		$CKEditor = new self();
		return $CKEditor->replace($id);
	}

	public static function htmlReplaceAll($classname) {
		$CKEditor = new self();
		return $CKEditor->replaceAll($classname);
	}

	public static function image($name,$image,$preview, $prefix=NULL){
		$prefix = $prefix ? $prefix : strtr($name, array('['=>'_', ']'=>'_'));

		$html = '<div class="image">';
        $html .= '<input type="hidden" name="' . $name . '" value="' . $image . '" id="' . $prefix . '" />';
        $html .= '<img src="' . $preview . '" alt="" id="' . $prefix . '_preview" class="imageUpload"/>';
        $html .= '<br />';
        $html .= '<a onclick="$(\'#' . $prefix . '_preview\').attr(\'src\', \'view/image/no_image-100x100.jpg\'); $(\'#' . $prefix . '\').attr(\'value\', \'\');">清除圖像</a>';
        $html .= '&nbsp;&nbsp;|&nbsp;&nbsp;';
        $html .= '<a id="' . $prefix . '_upload">選擇圖像</a>';
		$html .= '</div>';

		return $html;
	}

}
?>