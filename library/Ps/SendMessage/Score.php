<?
/*
 * Send messages to user while moderation
 */
class Ps_SendMessage_Score extends Ps_SendMessage_SMABSTRACT
{
	private function __construct(){}

	static function sendMessage($status, $info, $user_id) {

		switch ( $status ) {
			case 1 :
				$subject = "Присвоен статус \"Клиенты рекомендуют\"";
				$body = "По результатам оценок посетителей вашей анкете был присвоен статус \"Клиенты рекомендуют\".<br/>Анкета: <a href='http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}'>http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}</a><br />";

				$send = true;
				break;

			case 0 :
				$subject = "Cнят статус \"Клиенты рекомендуют\"";
				$body = "По результатам оценок посетителей у вашей анкеты был снят статус \"Клиенты рекомендуют\".<br/>Анкета: <a href='http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}'>http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}</a><br />";
				
				$send = true;
				break;
		}

		if ( $send ) {
			self::send( $info, $subject, $body, $user_id );
		}
	}

}