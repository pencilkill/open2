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
      <h1><img src="view/image/log.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#delete').submit();" class="button"><?php echo $button_delete; ?></a></div>
    </div>
    <div class="content">
      <div class="error" style="font-size: 1.2em;"><?php echo $text_tip?></div>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="delete">
        <table class="form">
          <tr>
            <td><?php echo $entry_cache; ?></td>
            <td><div class="scrollbox" style="margin-bottom: 5px; width: 550px; height: 300px;">
                <?php $class = 'odd'; ?>
                <?php foreach ($caches as $cache) { ?>
                <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                <div class="<?php echo $class; ?>">
                  <input type="checkbox" name="files[]" value="<?php echo $cache; ?>" checked="checked" />
                  <?php echo $cache; ?></div>
                <?php } ?>
              </div>
              <a onclick="$(this).parent().find(':checkbox').attr('checked', true);"><?php echo $text_select_all; ?></a> / <a onclick="$(this).parent().find(':checkbox').attr('checked', false);"><?php echo $text_unselect_all; ?></a>
            </td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>