<?

/*
 * Notifier by email 
 */

class Notifier_Email_Exception extends Zend_Exception{}
class Notifier_Email_No_User_Exception extends Zend_Exception{}
class Notifier_Email_No_Type_Exception extends Notifier_Email_Exception{}
class Notifier_Email_Unknown_Type_Exception extends Notifier_Email_Exception{}

class Ps_Notifier_Email
{
	const COMMENT = 'comment';
	const MODERATION = 'moderation';
	
	protected $_uid;
	protected $_type;
	protected $_sender;
	
	public function __construct( $uid, $type = null ) 
	{	
		if ( !$uid ) {
			throw new Notifier_Email_No_User_Exception('Please putt the user id');
		}		
		
		if ( is_null($type) ) {
			throw new Notifier_Email_No_Type_Exception('Type of notification is required');
		}
		
		switch ( $type ) {
			case self::COMMENT :
				$this->_uid = $uid;
				$this->_type = $type;
				$this->_sender = new Ps_Notifier_Comment();
				break;
				
			case self::MODERATION :
				$this->_uid = $uid;
				$this->_type = $type;
				$this->_sender = new Ps_Notifier_Moderation();
				break;
				
			default :
				throw new Notifier_Email_Unknown_Type_Exception('Please putt right type of notification');
				break;
		}		
	}
	
	public function send( $params = null) 
	{
		$this->_sender->send($this->_uid, $params);
	}
	
	public static function isSend( $uid, $type )
	{
		$user_config = new Model_UsersConfig();
		$config = $user_config->getUserMessagesConfig($uid);
		
		if ( empty($config) ) {
			return false;	
		}

		switch ( $type ) {
			case self::COMMENT :
				if ( $config['comments'] ) {
					return true;
				}
				break;

			case self::MODERATION :
				if ( $config['moderation'] ) {
					return true;
				}
				break;

			default :
				throw new Notifier_Email_Unknown_Type_Exception('Please putt right type of notification');
				break;
		}
	}
}