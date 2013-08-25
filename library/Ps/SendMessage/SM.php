<?

/*
 * Send messages to user while moderation
 */

class Ps_SendMessage_SM extends Ps_SendMessage_SMABSTRACT
{
	private function __construct(){}

	static function sendMessage($status, $comm, $info, $user_id) {

		switch ( $status ) {

			case 12 :
				$subject = "Анкета отклонена модератором";
				$body = "Анкета отклонена модератором. Причина – не полные данные.<br />";

				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;

			case 11 :
				$subject = "Анкета отклонена модератором";
				$body = "Анкета отклонена модератором. Причина – Проверочное фото не прошло проверку.<br />";

				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;

			case 30 :
				$send = false;
				break;

			case 1 :
				$subject = "Анкета отклонена модератором";
				$body = "Анкета отклонена модератором. Причина – Анкета нарушает правила портала.<br />";

				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;

			case 2 :
				$subject = "Анкета отклонена модератором";
				$body = "Анкета отклонена модератором, телефон занесен в черный список. Причина – Анкета нарушает правила портала. <br />";
				
				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;

		}

		$body .= "Анкета: <a href='http://putanastars.com/anketa/{$info['name']}-{$info['id']}'>http://putanastars.com/anketa/{$info['name']}-{$info['id']}</a><br />";

		if ( $send ) {
			self::send( $info, $subject, $body, $user_id );	
		}
	}

}