<?php 

class Zend_View_Helper_GetCountVoices extends Zend_View_Helper_Abstract {

	public function getCountVoices ( $owner_id = null )
	{
		if ( !is_null($owner_id) ) {
			$review = new Model_Review();
			$count = $review->getCountVoices( $owner_id );

			if ( $count ) {
				return $count;
			}
		} else {
			return "";
		}
	}
}
