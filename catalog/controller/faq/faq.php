<?php
class ControllerFaqFaq extends Controller {
	protected $preload_language = array('faq/faq');

	protected $preload_model = array('catalog/faq');

	private $error = array();

	public function index() {
   		$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
        	'separator' => false
      	);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('faq/faq'),
        	'separator' => $this->language->get('text_separator')
      	);

		$url = '';

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
			$url .= '&page=' . $this->request->get['page'];
		} else {
			$page = 1;
		}

		if(isset($this->request->get['keyword'])){
        	$keyword = $this->request->get['keyword'];
        	$url .= '&keyword=' . $this->request->get['keyword'];
        }else{
        	$keyword = null;
        }

        $total=$this->model_catalog_faq->getTotalFaqs($keyword);

        if($total){
        	$this->document->setTitle($this->language->get('heading_title'));

        	$this->data['faqs'] = array();

			$results = $this->model_catalog_faq->getFaqs(($page - 1) * $this->config->get('config_admin_limit'), $this->config->get('config_admin_limit'), $keyword);

			foreach($results as $result){
				$this->data['faqs'][] = array(
					'faq_id' => $result['faq_id'],
					'title'=>$result['title'],
					'keyword'=>$result['keyword'],
					'href'=> $this->url->link('faq/faq', 'faq_id='.$result['faq_id']),
					'date_added'=>date("Y-m-d",strtotime($result['date_added']))
				);
			}

			$pagination = new Pagination();
			$pagination->total = $total;
			$pagination->page = $page;
			$pagination->limit = $this->config->get('config_admin_limit');
			$pagination->text_prev = $this->language->get('text_pagination_prev');
			$pagination->text_next = $this->language->get('text_pagination_next');
			$pagination->url = $this->url->link('faq', $url . '&page={page}');

			$this->data['pagination'] = $pagination->render();

        	if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/faq/faqs.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/faq/faqs.tpl';
			} else {
				$this->template = 'default/template/faq/faqs.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());
		}else{
			$this->document->setTitle($this->language->get('heading_title'));

        	$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_founds.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_founds.tpl';
			} else {
				$this->template = 'default/template/error/not_founds.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

			$this->response->setOutput($this->render());
		}
  	}

  	public function faq() {
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
      		'href'      => $this->url->link('common/home'),
       		'text'      => $this->language->get('text_home'),
       		'separator' => FALSE
   		);

   		$this->data['breadcrumbs'][] = array(
      		'href'      => $this->url->link('news'),
       		'text'      => $this->language->get('heading_title'),
       		'separator' => $this->language->get('text_separator')
   		);

		if (isset($this->request->get['faq_id'])) {
			$faq_id = $this->request->get['faq_id'];
		} else {
			$faq_id = 0;
		}

		$faq_info = $this->model_catalog_faq->getFaq($faq_id);

		if ($faq_info) {
	  		$this->document->setTitle($faq_info['title']);

      		$this->data['breadcrumbs'][] = array(
        		'href'      => $this->url->link('faq/faq', 'faq_id=' . $this->request->get['faq_id']),
        		'text'      => $faq_info['title'],
        		'separator' => false
      		);

      		$this->data['title'] = $faq_info['title'];

      		$this->data['keyword'] = $faq_info['keyword'];

      		$this->data['date_added'] = date("Y-m-d",strtotime($faq_info['date_added']));

      		$this->data['description'] = html_entity_decode($faq_info['description']);

			$last_faq = $this->model_catalog_faq->getLastFaq($faq_id, $faq_info['sort_order'], $faq_info['date_added']);

			if($last_faq){
				$this->data['last_href'] = $this->url->link('faq/faq', 'faq_id=' . $last_faq['faq_id']);
			}

			$next_faq = $this->model_catalog_faq->getNextFaq($faq_id, $faq_info['sort_order'], $faq_info['date_added']);

			if($next_faq){
				$this->data['next_href'] = $this->url->link('faq/faq', 'faq_id=' . $next_faq['faq_id']);
			}

			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/faq/faq.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/faq/faq.tpl';
			} else {
				$this->template = 'default/template/faq/faq.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

	  		$this->response->setOutput($this->render());
    	} else {
      		$this->document->setTitle($this->language->get('heading_title'));

      		$this->data['breadcrumbs'][] = array(
				'href'      => $this->url->link('faq/faq', 'faq_id=' . $this->request->get['faq_id']),
        		'text'      => $this->language->get('text_error'),
        		'separator' => $this->language->get('text_separator')
      		);

      		$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}

			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'
			);

	  		$this->response->setOutput($this->render());
    	}
  	}
}
?>