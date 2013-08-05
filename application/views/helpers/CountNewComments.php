<?php 

class Zend_View_Helper_CountNewComments extends Zend_View_Helper_Abstract {
	
	public function countNewComments ( $user_id = null )
	{
		if ( !is_null($user_id) ) {		
			$comments = new Model_CommentsTest();
			$count = $comments->getNewComments( $user_id );
		
			if ( $count ) {
				return " (" . $count . ")";
			}
		} else {
			return "";
		}
	}
	
}
