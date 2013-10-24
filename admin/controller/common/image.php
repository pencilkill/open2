<?php
class ControllerCommonImage extends Controller {
	private $error = array();
	protected $preload_language=array('common/image');
	protected $preload_model=array('tool/image');

	public function index() {
		$data = array();

		if ($this->validate()) {
			$filename = uniqid().'.'.end(explode('.', basename($this->request->files['image']['name'])));

			if (@move_uploaded_file($this->request->files['image']['tmp_name'], DIR_IMAGE . $filename)) {
	  			chmod(DIR_IMAGE . $filename, 0777);

				$data['file'] = $filename;
				$data['src'] = $this->model_tool_image->resize($filename, 100, 100);
				$data['success'] = $this->language->get('text_success');
			}
		} else {
			$data['error'] = $this->error['message'];
		}

		$this->response->setOutput(json_encode($data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'common/image')) {
			$this->error['message'] = $this->language->get('error_permission');
		}

		if (isset($this->request->files['image'])) {
			if (is_uploaded_file($this->request->files['image']['tmp_name'])) {
	  			if ((strlen(utf8_decode($this->request->files['image']['name'])) < 1) || (strlen(utf8_decode($this->request->files['image']['name'])) > 255)) {
        			$this->error['message'] = $this->language->get('error_filename');
	  			}

		    	$allowed = array(
		      		'image/jpeg',
		      		'image/pjpeg',
					'image/png',
					'image/x-png',
					'image/gif',
					'application/pdf'
		    	);

				if (!in_array($this->request->files['image']['type'], $allowed)) {
          			$this->error['message'] = $this->language->get('error_filetype');
        		}

				if ($this->request->files['image']['error'] != UPLOAD_ERR_OK) {
					$this->error['message'] = $this->language->get('error_upload_' . $this->request->files['image']['error']);
				}
			}
		} else {
			$this->error['message'] = $this->language->get('error_required');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>