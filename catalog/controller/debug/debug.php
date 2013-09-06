<?php
class ControllerDebugDebug extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public function index() {
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}
		ob_start();
		$this->load->helper('abc');
		$excel = new PclZip;
		var_dump($excel);

		$output = ob_get_clean();
		$this->response->setOutput($output);
  	}

  	private function validate(){
  		return strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false;
  	}
}
?>