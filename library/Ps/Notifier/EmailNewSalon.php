<?php 

/*
 * Notifier by email
 */

class Ps_Notifier_EmailNewSalon_Exception extends Zend_Exception{}

class Ps_Notifier_EmailNewSalon
{
	public function __construct(  )
	{
		
	}
	
	public function send ( $info ) 
	{	
		$users = new Model_UsersTest();
		$moderators = $users->getModerators();
		$content = Zend_Registry::get('content');

		foreach ( $moderators as $moder ) {
			$header ="Content-type: text/html; charset=utf-8 \r\n";	
			$header.="From: ".$content->mail->from->new_moderation_salon." \r\n";
			
			$subj = "Добавлена новая анкета салона \"{$info['name']} 8-{$info['phone']} \"";
			$text = "Новая анкета салона \"{$info['name']} 8-{$info['phone']}\"";
			
			$result = mail($moder['mail'], $subj, $text, $header);
			
			if ( !$result ) {
				throw new Ps_Notifier_EmailNewSalon_Exception("Can\'t send new salon email to {$moder['mail']}");
			}
		}
	}
}