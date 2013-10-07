<?php
/**
 * @author sam@ozchamp.net
 */
class ControllerCommonAjax extends Controller{
	private $error=array();

	public function index(){
		// Nothing instresting happened in default action
	}

	public function twzone(){
		$return = '';

		if(! isset($this->request->get['zone_id'])){
			return $return;
		}

		$this->load->model('localisation/tw_zone');

		$twzones = $this->model_localisation_tw_zone->getZonesByCityId($this->request->get['zone_id']);	// Actually, getting counties By TW zone_id

		if(isset($this->request->get['opt']) && $this->request->get['opt']){
			$kf = 'county_id';
			$nf = 'name';
			$def = array();

			if(isset($this->request->get['kf'])) $kf = $this->request->get['kf'];
			if(isset($this->request->get['nf'])) $nf = $this->request->get['nf'];
			if(isset($this->request->get['def'])) $def = is_array($this->request->get['def']) ? $this->request->get['def'] : array($this->request->get['def']);

			foreach($twzones as $twzone){
				if(in_array($twzone[$kf], $def)){
					$return .= '<option value="'.$twzone[$kf].'" selected="selected">'.$twzone[$nf].'</option>';
				}else{
					$return .= '<option value="'.$twzone[$kf].'">'.$twzone[$nf].'</option>';
				}
			}
		}else{
			$return = json_encode($return);
		}

		$this->response->setOutput($return);
	}

}
?>