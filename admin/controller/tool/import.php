<?php
class ControllerToolImport extends Controller {
	protected $_language = array('tool/import');

	protected $_model = array('catalog/product');

	private $error = array();

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			ini_set('display_errors', false);

			error_reporting(-1);

			set_error_handler(function($code, $string, $file, $line){
				if(array_search($code, array(E_NOTICE, E_WARNING)) === false) throw new ErrorException($string, null, $code, $file, $line);
			});

			register_shutdown_function(function(){
				$error = error_get_last();

				if(null !== $error)
		        {
		        	$output = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		        	$output .= '<script type="text/javascript">';
		        	$output .= 'alert("錯誤：建議您做少量多次匯入，避免操作失敗！");';
					$output .= 'window.parent.location.href = window.parent.location.href;';
		        	$output .= '</script>';

		        	ob_clean();
		        	echo $output;
		        	exit();
		        }
			});

			try
			{
				$importDir = DIR_IMAGE . '/import/productRestore/';

				is_dir($importDir) || mkdir($importDir, 0777, true);

				if (is_uploaded_file($this->request->files['import']['tmp_name'])) {

					$importFile = $importDir . $this->request->files['import']['name'];

					is_file($importFile) && unlink($importFile);

					move_uploaded_file($this->request->files['import']['tmp_name'], $importFile);

					$this->fileImport($importFile);

					$this->session->data['success'] = $this->language->get('text_success');

				} else {
					$this->session->data['error_warning'] = $this->language->get('error_empty');
				}

				$output = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	        	$output .= '<script type="text/javascript">';
				$output .= 'window.parent.location.href = window.parent.location.href;';
	        	$output .= '</script>';

	        	ob_clean();
	        	echo $output;
	        	exit();
			}
			catch(Exception $e)
			{
			    die('[' . $e->getCode() . '] ' . $e->getMessage(). ' in file ' . $e->getFile() . ' line ' . $e->getLine());
			}
		}

		if (isset($this->session->data['error_warning'])) {
			$this->data['error_warning'] = $this->session->data['error_warning'];

			unset($this->session->data['error_warning']);
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
			'href'      => $this->url->link('tool/import', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
      	);

      	$this->data['tmpl_new'] = html_entity_decode($this->url->link('catalog/product/insert', 'token=' . $this->session->data['token'], 'SSL'), ENT_QUOTES, 'UTF-8');

      	$this->data['import'] = $this->url->link('tool/import', 'token=' . $this->session->data['token'], 'SSL');

      	if(is_file(DIR_IMAGE . 'productImportTemplate.xlsx')){
      		$this->data['tmpl_csv'] = HTTPS_IMAGE . 'productImportTemplate.xlsx';
      	}

      	if(is_file(DIR_IMAGE . 'productImportTemplate.doc')){
      		$this->data['tmpl_doc'] = HTTPS_IMAGE . 'productImportTemplate.doc';
      	}


      	$this->template = 'tool/import.tpl';
      	$this->children = array(
		'common/header',
		'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function fileImport($file) {
		//return true;

		$category_id = (int)pathinfo($file, PATHINFO_FILENAME);

		if(empty($category_id)) return false;

		ini_set('memory', '1024M');
		ini_set('max_execution_time', '36000');

		static $start = 2;
		static $limit = 99;

		$import = $this->request->post;

		$excel = new Excel();

		$excel->initialized($file);

		$totalRow = (int)$excel->allRow;

		$importRow = $totalRow - 1;

		if(empty($importRow)) return true;

		$beginRow = $start;

		$endRow = $start;

		do{
			$endRow = ($limit && (($beginRow + $limit) < $totalRow)) ? ($beginRow + $limit) : $totalRow;

			$data = $excel->fetch($beginRow, $endRow);

			$this->dataImport($category_id, $data, $import);

			$beginRow = $endRow + 1;

		}while($endRow < $totalRow);

		return true;

	}

	public function dataImport($category_id, $data, $import) {
		//return true;

		//$this->db->trans_begin();

		foreach($data as $row){
			$add = array();

			if(isset($row['model'])){
				$add['model'] = $row['model'];
			}

			$add['product_category'] = array($category_id);

			if($add['model']){
				$query = $this->db->select('product_id')->get_where('product', array('LCASE(model)' => strtolower($add['model'])));

				$num_rows = $query->num_rows;

				switch ($num_rows) {
					case 0:
						$this->model_catalog_product->addProduct($this->array_merge_recursive_distinct($import, $add));
					break;

					case 1:
						$product_id = array_shift($query->row);

						$product = $this->model_catalog_product->copyProduct($product_id, true);

						unset($product['product_id']);

						$this->model_catalog_product->editProduct($product_id, $this->array_merge_recursive_distinct($product, $add));

					break;

					case ($num_rows > 1):
						// nothing interesting happened;
					break;

					default:
						;
					break;
				}
			}

		}

		//$this->db->trans_commit();

	}

	private function array_merge_recursive_distinct () {
		$arrays = func_get_args();
		$base = array_shift($arrays);
		if(!is_array($base)) $base = empty($base) ? array() : array($base);
		foreach($arrays as $append) {
			if(!is_array($append)) $append = array($append);
			foreach($append as $key => $value) {
				if(!array_key_exists($key, $base) and !is_numeric($key)) {
					$base[$key] = $append[$key];
					continue;
				}
				if(is_array($value) or is_array($base[$key])) {
					$base[$key] = $this->array_merge_recursive_distinct($base[$key], $append[$key]);
				} else if(is_numeric($key)) {
					if(!in_array($value, $base)) $base[] = $value;
				} else {
					$base[$key] = $value;
				}
			}
		}
		return $base;
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'tool/import')) {
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