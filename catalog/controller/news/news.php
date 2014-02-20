<?php
class ControllerNewsNews extends Controller {
	protected $_language=array('news/news');

	protected $_model=array('catalog/news');

	public function index() {

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

		$news_total=$this->model_catalog_news->getTotalNews($keyword);

		$news_list_limit = 13 ;

		if($news_total){
			$this->document->setTitle($this->language->get('heading_title'));

			$this->data['newses'] = array();

			$results = $this->model_catalog_news->getNewses(($page - 1) * $news_list_limit, $news_list_limit, $keyword);

			foreach($results as $result){
				$this->data['newses'][] = array(
					'news_id' => $result['news_id'],
					'title'=>$result['title'],
					'date_time'=>date("Y-m-d",strtotime($result['date_time'])),
					'href'=>$this->url->link('news/news','news_id=' . $result['news_id'])
				);
			}

			$pagination = new Pagination();
			$pagination->total = $news_total;
			$pagination->page = $page;
			$pagination->limit = $news_list_limit;
			$pagination->text = $this->language->get('text_pagination');
			$pagination->url = $this->url->link('news/news', $url . 'page={page}');

			$this->data['pagination'] = $pagination->render();

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/news/newses.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/news/newses.tpl';
			} else {
				$this->template = 'default/template/news/newses.tpl';
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

	public function info() {
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

		if (isset($this->request->get['news_id'])) {
			$news_id = $this->request->get['news_id'];
		} else {
			$news_id = 0;
		}

		$news_info = $this->model_catalog_news->getNews($news_id);

		if ($news_info) {
	  		$this->document->setTitle($news_info['title']);

      		$this->data['breadcrumbs'][] = array(
        		'href'      => $this->url->link('news/news', 'news_id=' . $this->request->get['news_id']),
        		'text'      => $news_info['title'],
        		'separator' => $this->language->get('text_separator')
      		);

      		$this->data['title'] = $news_info['title'];

			$this->data['date_added'] = date("Y-m-d", strtotime($news_info['date_added']));

			$this->data['description'] = html_entity_decode($news_info['description']);

			$last_news = $this->model_catalog_news->getLastNews($news_info['news_id'], $news_info['sort_order'], $news_info['date_added']);

			if($last_news){
				$this->data['next_href'] = $this->url->link('news/news', 'news_id=' . $next_news['news_id']);
			}

			$next_news = $this->model_catalog_news->getNextNews($news_info['news_id'], $news_info['sort_order'], $news_info['date_added']);

			if($next_news){
				$this->data['next_href'] = $this->url->link('news/news', 'news_id=' . $next_news['news_id']);
			}

			$this->data['continue'] = $this->url->link('common/home');

			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/news/news.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/news/news.tpl';
			} else {
				$this->template = 'default/template/news/news.tpl';
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
				'href'      => $this->url->link('news/news', 'news_id=' . $this->request->get['news_id']),
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