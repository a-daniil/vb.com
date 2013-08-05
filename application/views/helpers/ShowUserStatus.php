<?php 

class Zend_View_Helper_ShowUserStatus extends Zend_View_Helper_Abstract {
	
	public function showUserStatus ( $status )
	{
		switch ($status) {
			case '0' :
				 return 'Заблокирован';
				break;
			case '1' :
				 return 'Активен';
				break;
			default:
				return 'Активен';
				break;				
		}
	} 
	
}