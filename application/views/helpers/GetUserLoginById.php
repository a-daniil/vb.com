<?php 

class Zend_View_Helper_GetUserLoginById extends Zend_View_Helper_Abstract {

	public function getUserLoginById ( $user_id = null )
	{
		if ( !is_null($user_id) ) {
			$user = new Model_UsersTest();
			$login = $user->getLogin( $user_id );

			if ( $login ) {
				return $login;
			}
		} else {
			return "";
		}
	}

}