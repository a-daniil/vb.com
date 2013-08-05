<?php 

class Zend_View_Helper_CountPostpone extends Zend_View_Helper_Abstract {
	
	public function countPostpone ( $user_id )
	{
		if ( !is_null($user_id) ) {		
			$postpone = new Model_Postpone();
			$count = $postpone->getUserPostpone( $user_id );

			if ( $count ) {
				return " (" . $count . ")";
			}
		} else {
			return "";
		}
	}
	
}