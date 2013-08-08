<?

/*
 * Send messages to user while moderation
 */

class Ps_SendMessage_NotOriginalPhoto extends Ps_SendMessage_SMABSTRACT
{
	private function __construct(){}

	static function sendMessage($status, $info, $user_id) {

		switch ( $status ) {

			case 10 :
				$subject = "Несколько Ваших клиентов сообщили, что вы испозьзуете чужие фотографии";
				$body = "Несколько Ваших клиентов сообщили, что вы испозьзуете чужие фотографии в анкете <a href='http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}'>http://vb.arcada-team.ru/anketa/{$info['name']}-{$info['id']}</a><br />";
				
				$send = true;
				break;
		}

		if ( $send ) {
			self::send( $info, $subject, $body, $user_id );
		}
	}

}