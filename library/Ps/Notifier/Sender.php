<?php 

abstract class Ps_Notifier_Sender {
	
	protected function getUserMail( $uid )
	{
		$user = new Model_UsersTest();
		return $user->getEmail($uid);
	}
	
}