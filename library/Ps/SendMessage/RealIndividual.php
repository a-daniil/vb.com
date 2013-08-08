<?
/*
 * Send messages to user while moderation
 */
class Ps_SendMessage_RealIndividual extends Ps_SendMessage_SMABSTRACT
{
	private function __construct(){}

	static function sendMessage($status, $info, $user_id) {

		switch ( $status ) {
			case 1 :
				$subject = "Ваша анкета по оценкам пользователей получила статус реальная индивидуалка.";
				$body = "Ваша анкета по оценкам пользователей получила статус реальная индивидуалка.<br/>Анкета: <a href='http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}'>http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}</a><br />";

				$send = true;
				break;

			case 0 :
				$subject = "С вашей анкеты по оценкам пользователей был снят статус реальная индивидуалка.";
				$body = "С вашей анкеты по оценкам пользователей был снят статус реальная индивидуалка.<br/>Анкета: <a href='http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}'>http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}</a><br />";
				
				$send = true;
				break;
		}

		if ( $send ) {
			self::send( $info, $subject, $body, $user_id );
		}
	}
}