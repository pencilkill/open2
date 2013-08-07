<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/backup.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a id="productImport" class="button"><?php echo $button_import; ?></a></div>
    </div>
    <div class="content">
      <iframe id="iframeImport" name="iframeImport" src="about:blank" frameborder='0' style="display:none;"></iframe>
	    <form target="iframeImport" action="<?php echo $import; ?>" method="post" enctype="multipart/form-data" id="import">
	      <table class="form">
	        <tr>
	          <td></td>
	          <td>
	          <span  style="color:red;"><?php echo $text_import_tip?></span>
	          <?php if(isset($tmpl_csv)){?>
	          <a target="_blank" href="<?php echo $tmpl_csv?>" style="margin-left:20px;"><?php echo $text_temp_csv?></a>
	          <?php }?>
	          <?php if(isset($tmpl_doc)){?>
	          <a target="_blank" href="<?php echo $tmpl_doc?>" style="margin-left:20px;"><?php echo $text_temp_doc?></a>
	          <?php }?>
	          </td>
	        </tr>
	        <tr>
	          <td><?php echo $entry_import; ?></td>
	          <td><input type="file" name="import" /><img id="loadImport" src="view/image/loading-1.gif" style="display:none;"/></td>
	        </tr>
	        <tr style="display:none">
	          <td><?php echo $entry_tmpl; ?></td>
	          <td><div id="productTemplate"></div></td>
	        </tr>
	      </table>
	    </form>
    </div>
  </div>
</div>
<?php // 使用 iframe 完成整個頁面加載?>
<iframe id="iframeTemplate" name="iframeTemplate" src="<?php echo $tmpl_new?>" frameborder='0' style="display:none;"></iframe>
<script type="text/javascript">
jQuery(function($){
	$('#iframeTemplate').load(function(){
		$(window.frames["iframeTemplate"].document).ready(function(){
			$('#productTemplate').html($(window.frames["iframeTemplate"].document).find('#form').html());
			$('#productImport').attr('onclick', "$('#import').submit();$('#loadImport').show();");
		});
	});
});
</script>
<?php echo $footer; ?>