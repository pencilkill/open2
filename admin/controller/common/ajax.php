<?php
/**
 * @author sam@ozchamp.net
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

			$this->db->update(DB_PREFIX . $t, $_POST, array($fk=>$k));		//更新數據

			$this->cache->delete($t);		//清除緩存，即時生效
		}
	}

	public function delete(){

	}

	public function sys(){

	}
	/**
	 * fck format html tags which has class argument $this->request->get['c']
	 * output javascript code
	 * @see core class fck
	 */
	public function fck(){
		$js='';
		if(isset($this->request->get['c']) && $classname=$this->request->get['c']){
			$js = urlencode(Fck::htmlReplaceAll($classname));
		}
		$this->response->setOutput($js);
	}

}
?>