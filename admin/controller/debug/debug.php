<?php
class ControllerDebugDebug extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public function index(){
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$this->load->model('localisation/zone');

		$this->data['cities'] = $this->model_localisation_zone->getZonesByCountryId(206);

		$this->template = 'debug/debug.tpl';

		$this->response->setOutput($this->render());
	}

  	private function validate(){
  		return strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false;
  	}
}
?>