<?php

class Ps_Notifier_Comment_Exception extends Zend_Exception{}

class Ps_Notifier_Comment extends Ps_Notifier_Sender {

	const MESSAGE = "У вашей анкеты ( %s ) новый комментарий.<br/>";

	public function send( $uid, $params = null )
	{
		$link = $this->getAnketLink($params);
		$mail = $this->getUserMail($uid);
		if ( !$mail ) {
			throw new Ps_Notifier_Comment_Exception('User '. $uid . ' don\'t have email in DB');
		}

		$content = Zend_Registry::get('content');
		$config = Zend_Registry::get('config');
		$header ="Content-type: text/html; charset=utf-8 \r\n";	
		$header.="From: ".$content->mail->from->new_comment." \r\n";
		
		$text = sprintf(self::MESSAGE, $link);
		$text.= "<p>Письмо отправлено автоматически отвечать на него не нужно.<br/>";
		$text.= "С уважением, робот <a href='http://". $config->domen ."'>".$config->domen."</a>";
		$subj = $content->mail->subj->new_comment;

		$result = mail($mail, $subj, $text, $header);
		if ( !$result ) {
			throw new Ps_Notifier_Comment_Exception('Can\'t send comment email');
		}
	}

	private function getAnketLink( $params )
	{
		$comment = new Model_CommentsTest();
		$owner_id = $comment->getOwnerId($params);

		$ankets = new Model_AnketsTest();
		$ankets = $ankets->getById($owner_id);

		/* get domen name */
		$config = Zend_Registry::get('config');
		$domen = $config->domen;

		$link = "http://{$domen}/anketa/" . $ankets['name_eng'] . "-" . $ankets['id'];

		return "<a href='". $link . "'>" . $link . "</a>";
	}
}