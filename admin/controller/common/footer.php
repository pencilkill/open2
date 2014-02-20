<?php
class ControllerCommonFooter extends Controller {
	protected $_language = array('common/footer');

	protected function index() {
		$this->data['text_footer'] = sprintf($this->language->get('text_footer'), $this->config->get('config_name'));

		$islogged = $this->user->isLogged();

		$this->data['islogged'] = $islogged;

		if($islogged){
			$this->data['token'] = $this->session->data['token'];
		}

		if (file_exists(DIR_SYSTEM . 'config/svn/svn.ver')) {
			$this->data['text_footer'] .= '.r' . trim(file_get_contents(DIR_SYSTEM . 'config/svn/svn.ver'));
		}

		$this->template = 'common/footer.tpl';

    	$this->render();
  	}
}
?>