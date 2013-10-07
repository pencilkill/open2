<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.7.1.min.js"></script>
</head>
<body>
<select name="zone_id">
	<?php foreach($cities as $city):?>
	<option value="<?php echo $city['zone_id']?>"><?php echo $city['name']?></option>
	<?php endforeach;?>
</select>
<select name="city"></select>
<script type="text/javascript">
jQuery(function($){
	$('[name="zone_id"]').on('change', function(){
		var url = 'index.php?route=common/ajax/twzone&opt=1&nf=name&kf=name&zone_id=' + $(this).val() + '&def=' + encodeURIComponent('壯圍鄉 ');
		$('[name="city"]').load(url);
	}).trigger('change');
});
</script>
</body>
</html>