<?php

class Zend_View_Helper_CountNewMessages extends Zend_View_Helper_Abstract {
	
	const USR_ADM=0;
	const COM_ADM = 1;
	const TECH_ADM = 2;
	const MODER_ADM = 3;
	
	public function countNewMessages ()
	{
		$messages = new Model_Messages();
		
		$auth=Zend_Auth::getInstance()->getIdentity();
			
		if ( !$auth ) {
			return null;
		}		
		
		$user_id = $auth->id;
		$user_admin=( $auth->flags & 1<<self::USR_ADM ) ? true : false;
		$user_com = ( $auth->flags & 1<<self::COM_ADM ) ? true : false;
		$user_tech = ( $auth->flags & 1<<self::TECH_ADM ) ? true : false;
		$user_moder = ( $auth->flags & 1<<self::MODER_ADM ) ? true : false;
		
		if ( $user_admin ) {
			return $messages->getNewMessagesCount( Form_NewMessageForm::TO_ADMIN );				
		} elseif ( $user_moder ) {
			return $messages->getNewMessagesCount( Form_NewMessageForm::TO_MODER );				
		} else {
			return $messages->getNewMessagesCount($user_id);
		}			
	}
	
}