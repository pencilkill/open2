<?php
/**
 * @author sam@ozchamp.net
 */
class ControllerCommonAjax extends Controller{
	private $error=array();

	public function index(){
		// Nothing instresting happened in default action
	}

	public function twzones(){
		$zones_data = array();
		if(isset($this->request->get['city_id']) && (int)$this->request->get['city_id']){
			$zones_data = $this->model_localisation_twzone->getZonesByCityId((int)$this->request->get['city_id']);
		}

		$zones = '';
		if(isset($this->request->get['city_id']) && $this->request->get['opts']){
			$kf = 'zone_id';
			$nf = 'zone_name';
			$def = null;

			if(isset($this->request->get['kf']) && $this->request->get['kf']){
				$kf = $this->request->get['kf'];
			}
			if(isset($this->request->get['nf']) && $this->request->get['nf']){
				$nf = $this->request->get['nf'];
			}
			if(isset($this->request->get['def']) && $this->request->get['def']){
				$def = $this->request->get['def'];
			}

			foreach($zones_data as $zone){
				if($def==$zone[$kf]){
					$zones .= '<option value="'.$zone[$kf].'" selected="selected">'.$zone[$nf].'</option>';
				}else{
					$zones .= '<option value="'.$zone[$kf].'">'.$zone[$nf].'</option>';
				}
			}
		}else{
			$zones = json_encode($zones_data);
		}

		$this->response->setOutput($zones);
	}

}
?>