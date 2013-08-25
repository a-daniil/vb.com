<?

/*
 * Send messages to user while moderation
 */

class Ps_SendMessage_SMABSTRACT  
{
	protected function send( $info, $subject, $body, $user_id ) {
		$messages = new Model_Messages();
		$result = $messages->addMessage(
				$info['user_id'],
				$subject,
				$body,
				$user_id
		);

		/*
		if ( $result && Ps_Notifier_Email::isSend( $info['user_id'], Ps_Notifier_Email::MODERATION )) {
			$body .= "<br/>Письмо отправлено автоматически, отвечать на него не нужно.<br/>
					C уважением, робот <a href='http://putanastars.com'>PutanaStars.com</a>";

			$notifier = new Ps_Notifier_Email( $info['user_id'], Ps_Notifier_Email::MODERATION );
			$notifier->send( array (
					'subject' => $subject,
					'body' => $body,
					'anket_id' => $info['id']
			));
		}
		*/
	}

}