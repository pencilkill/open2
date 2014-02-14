<?php
class ModelAffiliateAffiliate extends Model {
	public function addAffiliate($data) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);
		$this->db->set('salt', $salt);
		$this->db->set('password', sha1($salt . sha1($salt . sha1($data['password']))));
		unset($data['password']);
		$this->db->set('code', uniqid());
		$this->db->set('status', 1);
		$this->db->set('date_added', date('Y-m-d H:i:s'));


      	$this->db->insert('affiliate', $data);

		$this->language->load('mail/affiliate');

		$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));

		$message  = sprintf($this->language->get('text_welcome'), $this->config->get('config_name')) . "\n\n";
		$message .= $this->language->get('text_approval') . "\n";
		$message .= $this->url->link('affiliate/login', '', 'SSL') . "\n\n";
		$message .= $this->language->get('text_services') . "\n\n";
		$message .= $this->language->get('text_thanks') . "\n";
		$message .= $this->config->get('config_name');

		$mail = new Mail();

		$mail->setFrom($this->config->get('config_email'), $this->config->get('config_name'));
		$mail->AddAddresses($this->request->post['email']);
		$mail->Subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		$mail->MsgHTML(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
		$mail->send();
	}

	public function editAffiliate($data) {
      	$this->db->update('affiliate', $data, array('affiliate_id' => (int)$this->affiliate->getId()));
	}

	public function editPayment($data) {
      	$this->db->update('affiliate', $data, array('affiliate_id' => (int)$this->affiliate->getId()));
	}

	public function editPassword($email, $password) {
		$salt = substr(md5(uniqid(rand(), true)), 0, 9);
		$this->db->set('salt', $salt);
		$this->db->set('password', sha1($salt . sha1($salt . sha1($password))));

      	$this->db->update('affiliate', $data, array('email' => $email));
	}

	public function getAffiliate($affiliate_id) {
		$query = $this->db->get_where('affiliate', array('affiliate_id' => (int)$affiliate_id));

		return $query->row;
	}

	public function getAffiliateByEmail($email) {
		$query = $this->db->get_where('affiliate', array('email' => $email));

		return $query->row;
	}

	public function getAffiliateByCode($code) {
		$query = $this->db->get_where('affiliate', array('code' => $code));

		return $query->row;
	}

	public function getTotalAffiliatesByEmail($email) {
		$query = $this->db->select('COUNT(*) AS total')->get_where('affiliate', array('LOWER(email)' => strtolower($email)));

		return $query->row['total'];
	}
}
?>