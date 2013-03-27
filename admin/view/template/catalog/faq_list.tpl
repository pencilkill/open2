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
	    <h1><img src="view/image/information.png" alt="" /><?php echo $heading_title; ?></h1>
	    <div class="buttons"><a onclick="location = '<?php echo $insert; ?>'" class="button"><span><?php echo $button_insert; ?></span></a><a onclick="$('form').submit();" class="button"><span><?php echo $button_delete; ?></span></a></div>
	  </div>
	  <div class="content">
	    <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
	      <table class="list">
	        <thead>
	          <tr>
	            <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
	            <td class="left"><?php if ($sort == 'nd.title') { ?>
	              <a href="<?php echo $sort_title; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_title; ?></a>
	              <?php } else { ?>
	              <a href="<?php echo $sort_title; ?>"><?php echo $column_title; ?></a>
	              <?php } ?></td>
	            <td class="right"><?php if ($sort == 'n.sort_order') { ?>
	              <a href="<?php echo $sort_sort_order; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_sort_order; ?></a>
	              <?php } else { ?>
	              <a href="<?php echo $sort_sort_order; ?>"><?php echo $column_sort_order; ?></a>
	              <?php } ?></td>
	            <td class="right"><?php if ($sort == 'n.status') { ?>
	              <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
	              <?php } else { ?>
	              <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
	              <?php } ?></td>
	            <td class="right"><?php echo $column_action; ?></td>
	          </tr>
	        </thead>

	        <tbody>
	          <?php if ($faqs) { ?>
	          <?php foreach ($faqs as $faq) { ?>
	          <tr>
	            <td style="text-align: center;"><?php if ($faq['selected']) { ?>
	              <input type="checkbox" name="selected[]" value="<?php echo $faq['faq_id']; ?>" checked="checked" />
	              <?php } else { ?>
	              <input type="checkbox" name="selected[]" value="<?php echo $faq['faq_id']; ?>" />
	              <?php } ?></td>

	            <td class="left"><?php echo $faq['title']; ?></td>
	            <td class="right"><input postId="<?php echo $faq['faq_id']; ?>" postType="faq" name="sort_order" type="text" size="4" value="<?php echo $faq['sort_order']; ?>"></td>
	            <td class="right">
	            	<select name="status" postId="<?php echo $faq['faq_id']; ?>" postType="faq">
						<option value="0"><?php echo $text_disabled; ?></option>
						<option value="1" <?php if($faq['status']=='1') echo 'selected="selected"'?>><?php echo $text_enabled; ?></option>
					</select>
				</td>
	            <td class="right"><?php foreach ($faq['action'] as $action) { ?>
	              [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
	              <?php } ?></td>
	          </tr>
	          <?php } ?>
	          <?php } else { ?>
	          <tr>
	            <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
	          </tr>
	          <?php } ?>
	        </tbody>
	      </table>
	    </form>
	    <div class="pagination"><?php echo $pagination; ?></div>
	  </div>
  </div>
</div>
<?php echo $footer; ?>