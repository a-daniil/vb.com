<?php 

class Zend_View_Helper_GetPositiveVoices extends Zend_View_Helper_Abstract {

	public function getPositiveVoices ( $owner_id = null )
	{
		if ( !is_null($owner_id) ) {
			$review = new Model_Review();
			$count = $review->getPositiveVoices( $owner_id );

			if ( $count ) {
				return "+" . $count;
			}
		} else {
			return "";
		}
	}
}
