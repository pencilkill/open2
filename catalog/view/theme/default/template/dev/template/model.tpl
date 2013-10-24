<?php echo '<?php'?>

class <?php echo $className?> extends Model{
<?php foreach ($methods as $method){?>

	<?php echo $method['access']?> function <?php echo $method['method']?>(){
		// ...
	}
<?php }?>

}
?>