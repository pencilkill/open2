<?php echo '<?php'?>

// ...
<?php foreach ($texts as $key => $val){?>
$_['<?php echo $key?>']  = '<?php echo $val?>';
<?php }?>
<?php echo '?>'?>