<?

/*
 * Send messages to user while moderation
 */

class Ps_SendMessage_SMA extends Ps_SendMessage_SMABSTRACT
{
	private function __construct(){}
	
	static function sendMessage($status, $comm, $info, $user_id) {
		
		switch ( $status ) {
			
			case 11 :
				$subject = "Салон отклонен модератором";
				$body = "Салон отклонен модератором. Причина – не полные данные.<br />";
				
				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;				
				break;			
			
			case 30 :
				$send = false;
				break;
				
			case 1 :
				$subject = "Салон отклонен модераторо";
				$body = "Салон отклонен модератором. Причина – Анкета нарушает правила портала.<br />";
				
				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;
				
			case 2 :
				$subject = "Салон отклонен модератором";
				$body = "Салон отклонен модератором, телефон занесен в черный список. Причина – Салон нарушает правила портала. <br />";
				
				if ( $comm ) {
					$body .= " Комментарий модератора: " . $comm . "<br />";
				}
				$send = true;
				break;
								
		}
		
		$body .= "Салон: <a href='http://putanastars.com/salon/{$info['name']}-{$info['id']}'>http://putanastars.com/salon/{$info['name']}-{$info['id']}</a><br />";
		
		if ( $send ) {
			self::send( $info, $subject, $body, $user_id );			
		}	
		
	}
	
}