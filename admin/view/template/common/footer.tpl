</div>
<div id="footer"><?php echo $text_footer; ?></div>
<script type="text/javascript">
<!-- 日期選擇 -->
$(function(){
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('.datetime').datetimepicker({
	dateFormat: 'yy-mm-dd',
	timeFormat: 'h:m'
});
$('.time').timepicker({timeFormat: 'h:m'});
});
</script>
<!--上傳圖片-->
<script type="text/javascript">
jQuery(function($) {
	$('.imageUpload').each(function(){
		var image_id=$(this).attr('id');
		if(image_id){
			var hidden_id=image_id.replace(/_preview$/,'');
			var file_id=hidden_id+'_upload';
			imageUploader('#'+file_id, '#'+hidden_id, '#'+image_id);
		}
	});
});

//模板對應, Fck::image()
function addImageUploader(name, image, preview){
	var prefix = arguments[3] ? arguments[3] : name.replace(/(\[|\])/gi, '_');

    html = '<div class="image">';
    html += '<input type="hidden" name="' + name + '" value="' + image + '" id="' + prefix + '" />';
    html += '<img src="' + preview + '" alt="" id="' + prefix + '_preview" class="imageUpload"/>';
    html += '<br />';
    html += '<a onclick="$(\'#' + prefix + '_preview\').attr(\'src\', \'view/image/no_image-100x100.jpg\'); $(\'#' + prefix + '\').attr(\'value\', \'\');"><?php echo $text_clear?></a>';
    html += '&nbsp;&nbsp;|&nbsp;&nbsp;';
    html += '<a id="' + prefix + '_upload"><?php echo $text_browse; ?></a>';
    html += '</div>';
	html += '<script type="text/javascript">';
	html +=	'imageUploader(\'#' + prefix + '_upload\', \'#' + prefix + '\', \'#' + prefix + '_preview\')';
	html += '<\/script>';

    return html;

}

function imageUploader(upload, image, preview) {
	new AjaxUpload(upload, {
		action: 'index.php?route=common/image&token=<?php echo $token; ?>',
		name: 'image',
		autoSubmit: true,
		responseType: 'json',
		onSubmit: function(file, extension) {
			//$(upload).after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
			$(upload).attr('disabled', true);
		},
		onComplete: function(file, json) {
			$(upload).attr('disabled', false);

			if (json['success']) {
				//alert(json['success']);

				$(preview).attr('src', json.src);
				$(image).attr('value', json.file);
			}

			if (json['error']) {
				alert(json['error']);
			}

			$('.loading').remove();
		}
	});
}
</script>
<!--上傳文件-->
<script type="text/javascript">
/*
jQuery(function($) {
	$('.fileUpload').each(function(){
		var image_id=$(this).attr('id');
		if(image_id){
			var hidden_id=image_id.replace(/_preview$/,'');
			var file_id=hidden_id+'_upload';
			imageUploader('#'+file_id, '#'+hidden_id, '#'+image_id);
		}
	});
});
function fileUploader(upload, filename, mask) {
	new AjaxUpload(upload, {
		action: 'index.php?route=catalog/download/upload&token=<?php echo $token; ?>',
		name: 'file',
		autoSubmit: true,
		responseType: 'json',
		onSubmit: function(file, extension) {
			$(upload).after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
			$(upload).attr('disabled', true);
		},
		onComplete: function(file, json) {
			$(upload).attr('disabled', false);

			if (json['success']) {
				alert(json['success']);

				$(filename).attr('value', json['filename']);
				$(mask).attr('value', json['mask']);
			}

			if (json['error']) {
				alert(json['error']);
			}

			$('.loading').remove();
		}
	});
}
*/
</script>

<script type="text/javascript">
<!-- 異步編輯 -->
$(function(e){
	//阻止表單回車事件
	($('#form :text').length == 1) && $('#form').append('<input type="text" style="display:none;"/>');
	//表單綁定編輯
	$('input[postId]').blur(function (){edit($(this));});
	$('select[postId]').change(function (){edit($(this));})
});
function edit(o){
	$.each(o, function(_i, _o){
		var _o=$(_o), t=_o.attr('postType'), k=_o.attr('postId'), fk=_o.attr('postFk');
		if(t && k){
			$.post('index.php?route=common/ajax/update&t=' + t + '&k=' + k + (fk ? '&fk=' + fk : '') + '&token=<?php echo $token; ?>', _o.serializeArray(), function(data){return data;});
		}
	});
}
</script>
<!-- FCK格式化 -->
<?php echo Fck::htmlReplaceAll('fck');?>
<!-- //FCK格式化 -->
</body>
</html>