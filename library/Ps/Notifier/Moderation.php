<?php

class Ps_Notifier_Moderation_Exception extends Zend_Exception{}

class Ps_Notifier_Moderation extends Ps_Notifier_Sender {

	public function send( $uid, $params = null)
	{
		if ( !$params ) {
			throw new Ps_Notifier_Moderation_Exception('Don\'t have params for sending email');
		}		
		
		$mail = $this->getUserMail($uid);
		if ( !$mail ) {
			throw new Ps_Notifier_Moderation_Exception('User ' . $uid . ' don\'t have email in DB');
		}
		
		$content = Zend_Registry::get('content');
		$header ="Content-type: text/html; charset=utf-8 \r\n";	
		$header.="From: ".$content->mail->from->new_moderation." \r\n";
		
		$text = $params['body'];
		$subj = $content->mail->subj->new_moderation ." ". $params['subject'];
		
		$result = mail($mail, $subj, $text, $header);
		if ( !$result ) {
			throw new Ps_Notifier_Moderation_Exception('Can\'t send moder email');
		}
	}
}