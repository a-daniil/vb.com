<?

class Zend_View_Helper_GetMessageFrom extends Zend_View_Helper_Abstract {

	const MY_MESSAGE = "Моё сообщение";
	const ADMIN_MESSAGE = "Администрация";

	public function getMessageFrom ( $id )
	{
		$auth = Zend_Auth::getInstance()->getIdentity();
		$user_id = $auth->id;
		
		$messages = new Model_Messages();
		$owner_id = $messages->getMessageUserId( $id );
		
		if ( $owner_id == $user_id ) {
			return self::MY_MESSAGE;
		}
		
		$users = new Model_UsersTest();
		$flag = $users->getFlags( $owner_id );
		
		if ( $flag == 1 || $flag == 8 ) {
			return self::ADMIN_MESSAGE;
		} else {
			return $users->getLogin( $owner_id );
		}
	}
}