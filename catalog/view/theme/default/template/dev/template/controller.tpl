<?php echo '<?php'?>

class <?php echo $className?> extends Controller{
	public $_model = array();

	public $_language = array();

	private $error = array();
<?php foreach ($methods as $method){?>

	<?php echo $method['access']?> function <?php echo $method['method']?>(){
		// ...
	}
<?php }?>

}

?>