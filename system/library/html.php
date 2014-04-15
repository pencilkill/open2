<?php
/**
 * @author Sam, sam@ozchamp.net
 */
final class html{
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