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
  <div class="box">
	<div class="left"></div>
	<div class="right"></div>
	<div class="heading">
	<h1><img src="view/image/information.png" alt="" /><?php echo $heading_title; ?></h1>
	<div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a
		onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
	</div>
	<div class="content">
	<form action="<?php echo $action; ?>" method="post"	enctype="multipart/form-data" id="form">
	<div id="tabs" class="htabs"><a href="#tab-general"><?php echo $tab_general; ?></a><a href="#tab-data"><?php echo $tab_data; ?></a></div>
	<div id="tab-general">
	<div id="languages" class="htabs">
		<?php foreach ($languages as $language) { ?>
		<a href="#language<?php echo $language['language_id']; ?>"><img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /> <?php echo $language['name']; ?></a>
		<?php } ?>
    </div>
  	<?php foreach ($languages as $language) { ?>
		<div id="language<?php echo $language['language_id']; ?>">
		<table class="form">
			<tr>
				<td><span class="required">*</span> <?php echo $entry_title; ?></td>
				<td><input name="faq_description[<?php echo $language['language_id']; ?>][title]" size="100" value="<?php echo isset($faq_description[$language['language_id']]) ? $faq_description[$language['language_id']]['title'] : ''; ?>" />
					<?php if (isset($error_title[$language['language_id']])) { ?> <span	class="error"><?php echo $error_title[$language['language_id']]; ?></span><?php } ?>
				</td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_description; ?></td>
				<td><textarea rows="" cols="" name="faq_description[<?php echo $language['language_id']; ?>][description]" class="fck"><?php echo isset($faq_description[$language['language_id']]) ? $faq_description[$language['language_id']]['description'] : ''; ?></textarea>
				<?php if (isset($error_description[$language['language_id']])) { ?> <span class="error"><?php echo $error_description[$language['language_id']]; ?></span><?php } ?>
				</td>
			</tr>
			<tr>
				<td><?php echo $entry_keyword; ?></td>
				<td><input type="text" name="faq_description[<?php echo $language['language_id']; ?>][keyword]" size="100" value="<?php echo $faq_description[$language['language_id']]['keyword']; ?>" /></td>
			</tr>
		</table>
		</div>
	<?php } ?>
	</div>
	<div id="tab-data">
	<table class="form">
		<tr>
			<td><?php echo $entry_store; ?></td>
			<td>
			<div class="scrollbox"><?php $class = 'even'; ?>
			<div class="<?php echo $class; ?>"><?php if (in_array(0, $faq_store)) { ?>
			<input type="checkbox" name="faq_store[]" value="0" checked="checked" />
			<?php echo $text_default; ?> <?php } else { ?> <input type="checkbox"
				name="faq_store[]" value="0" /> <?php echo $text_default; ?> <?php } ?>
			</div>
			<?php foreach ($stores as $store) { ?> <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
			<div class="<?php echo $class; ?>"><?php if (in_array($store['store_id'], $faq_store)) { ?>
			<input type="checkbox" name="faq_store[]"
				value="<?php echo $store['store_id']; ?>" checked="checked" /> <?php echo $store['name']; ?>
				<?php } else { ?> <input type="checkbox" name="faq_store[]"
				value="<?php echo $store['store_id']; ?>" /> <?php echo $store['name']; ?>
				<?php } ?></div>
				<?php } ?></div>
			</td>
		</tr>
		<tr>
			<td><?php echo $entry_status; ?></td>
			<td><select name="status">
			<?php if ($status) { ?>
				<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
				<option value="0"><?php echo $text_disabled; ?></option>
				<?php } else { ?>
				<option value="1"><?php echo $text_enabled; ?></option>
				<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
				<?php } ?>
			</select></td>
		</tr>
		<tr>
			<td><?php echo $entry_sort_order; ?></td>
			<td><input name="sort_order" value="<?php echo $sort_order; ?>"
				size="1" /></td>
		</tr>
	</table>
	</div>
	</form>
	</div>
  </div>
</div>
<script type="text/javascript"><!--
$('#tabs a').tabs();
$('#languages a').tabs();
//--></script>
<?php echo $footer; ?>