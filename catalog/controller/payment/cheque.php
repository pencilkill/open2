<?php
class ControllerPaymentCheque extends Controller {
	protected $_language = array('payment/cheque');

	protected $_model = array('checkout/order');

	protected function index() {
		$this->data['payable'] = $this->config->get('cheque_payable');
		$this->data['address'] = nl2br($this->config->get('config_address'));

		$this->data['continue'] = $this->url->link('checkout/success');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/cheque.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/cheque.tpl';
		} else {
			$this->template = 'default/template/payment/cheque.tpl';
		}	
		
		$this->render(); 
	}
	
	public function confirm() {
		$comment  = $this->language->get('text_payable') . "\n";
		$comment .= $this->config->get('cheque_payable') . "\n\n";
		$comment .= $this->language->get('text_address') . "\n";
		$comment .= $this->config->get('config_address') . "\n\n";
		$comment .= $this->language->get('text_payment') . "\n";
		
		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('cheque_order_status_id'), $comment, true);
	}
}
?>