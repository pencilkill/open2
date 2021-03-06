<?php
/**
 * @author Sam <mail.song.de.qiang@gmail.com>
 */
class ControllerCommonAjax extends Controller{
	private $error=array();

	public function insert(){
		return '';
	}
	/**
	 * update row(or rows)
	 */
	public function update(){
		if(isset($this->request->get['t']) && isset($this->request->get['k'])){
			$t = $this->request->get['t'];		//表名

			$k = $this->request->get['k'];		//鍵值

			$fk = isset($this->request->get['fk']) ? $this->request->get['fk'] : $t . '_id';	//鍵名

			$this->db->update($t, $_POST, array($fk=>$k));		//更新數據

			$this->cache->delete($t);		//清除緩存，即時生效
		}
	}

	public function delete(){

	}

	public function sys(){

	}

	public function county(){
		$return = '';

		if(! isset($this->request->get['zone_id'])){
			return $return;
		}

		$this->load->model('localisation/county');

		$twzones = $this->model_localisation_county->getCountiesByZoneId($this->request->get['zone_id']);	// Actually, getting counties By TW zone_id

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