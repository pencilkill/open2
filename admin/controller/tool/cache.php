<?php
class ControllerToolCache extends Controller {
	protected $_language = array('tool/cache');

	private $error = array();

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'tool/cache')) {
			$this->cache->clear($this->request->post['files']);
			$this->session->data['success'] = $this->language->get('text_success');
		}

		if (isset($this->session->data['error'])) {
			$this->data['error_warning'] = $this->session->data['error'];

			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/cache', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
      		);

      	$this->data['delete'] = $this->url->link('tool/cache', 'token=' . $this->session->data['token'], 'SSL');

      	$this->data['caches'] = $this->cache->getCaches();

      	$this->template = 'tool/cache.tpl';

      	$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}
}
?>