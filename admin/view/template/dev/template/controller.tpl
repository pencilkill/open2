<?php echo '<?php'?>

class <?php echo $className?> extends Controller{
	public $preload_model = array();

	public $preload_language = array();

	private $error = array();
<?php foreach ($methods as $method){?>

	<?php echo $method['access']?> function <?php echo $method['method']?>(){
		// ...
	}
<?php }?>

	private function validateForm() {
		if (!$this->user->hasPermission('modify', '<?php echo $cii?>')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			if (!isset($this->error['warning'])) {
				$this->error['warning'] = $this->language->get('error_required_data');
			}
			return FALSE;
		}
	}

	private function validateDelete() {
		if (!$this->user->hasPermission('modify', '<?php echo $cii?>')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>