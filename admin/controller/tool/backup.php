<?php
class ControllerToolBackup extends Controller {
	protected $preload_language = array('tool/backup');

	protected $preload_model = array('tool/backup', 'catalog/product');

	private $error = array();

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'tool/backup')) {
			if (is_uploaded_file($this->request->files['restore']['tmp_name'])) {
				$content = file_get_contents($this->request->files['restore']['tmp_name']);
			} else {
				$content = false;
			}

			if ($content) {
				$this->model_tool_backup->restore($content);

				$this->session->data['success'] = $this->language->get('text_success');

				$this->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
			} else {
				$this->error['warning'] = $this->language->get('error_empty');
			}
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
			'href'      => $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
      		);

      		$this->data['formelement'] = $this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'], 'SSL');

      		$this->data['restore'] = $this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL');

      		$this->data['backup'] = $this->url->link('tool/backup/backup', 'token=' . $this->session->data['token'], 'SSL');

      		$this->data['import'] = $this->url->link('tool/backup/import', 'token=' . $this->session->data['token'], 'SSL');

      		$this->data['tables'] = $this->model_tool_backup->getTables();

      		$this->template = 'tool/backup.tpl';
      		$this->children = array(
		'common/header',
		'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function backup() {
		if (!isset($this->request->post['backup'])) {
			$this->session->data['error'] = $this->language->get('error_backup');

			$this->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
		} elseif ($this->user->hasPermission('modify', 'tool/backup')) {
			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/octet-stream');
			$this->response->addheader('Content-Disposition: attachment; filename=' . date('Y-m-d_H-i-s', time()).'_backup.sql');
			$this->response->addheader('Content-Transfer-Encoding: binary');

			$this->response->setOutput($this->model_tool_backup->backup($this->request->post['backup']));
		} else {
			$this->session->data['error'] = $this->language->get('error_permission');

			$this->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
		}
	}

	public function import() {
		if (!isset($this->request->files['import'])) {
			$this->session->data['error'] = $this->language->get('error_backup');

		} elseif ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->user->hasPermission('modify', 'tool/backup')) {
			if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
				//$content = file_get_contents($this->request->files['import']['tmp_name']);
				$content = true;
			} else {
				$content = false;
			}

			if ($content) {
				ini_set('memory', '1024M');
				ini_set('max_execution_time', '3600');

				$excel = new Excel();

				$excel->initialized($this->request->files['import']['tmp_name']);

				$totalRow=(int)$excel->allRow;

				$beginRow = 2;

				$limit = 99;

				do{
					$endRow= ($limit && (($beginRow + $limit) < $totalRow)) ? ($beginRow + $limit) : $totalRow;

					$data=$excel->fetch($beginRow, $endRow);

					$this->dataInsert($data, $import);

					unset($data);

					$beginRow=$endRow + 1;

				}while($endRow < $totalRow);

				$this->session->data['success'] = $this->language->get('text_success');

			} else {
				$this->error['warning'] = $this->language->get('error_empty');
			}

		} else {
			$this->session->data['error'] = $this->language->get('error_permission');

		}
		$this->redirect($this->url->link('tool/backup', 'token=' . $this->session->data['token'], 'SSL'));
	}

	public function dataInsert($data, $import) {
		//		默認關閉
		return true;

		$this->db->trans_begin();

		foreach($data as $row){
			$add=$this->request->post;

			//			數據整合 ...

			//			echo '<pre>';print_r($add);echo '</pre>';
			//			exit();

			$this->model_catalog_product->addProduct($add);

			unset($add);
		}

		$this->db->trans_commit();

		unset($data);

		return true;
	}

	public function dataDelete() {
		//		默認關閉
		return true;

		ini_set('memory', '1024M');
		ini_set('max_execution_time', '3600');

		$query=$this->db->query("SELECT product_id FROM " . DB_PREFIX . "product");

		foreach ($query->rows as $row){
			$this->model_catalog_product->deleteProduct($row['product_id']);
		}

		return true;
	}
}
?>