<?php 

class Zend_View_Helper_GetNegativeVoices extends Zend_View_Helper_Abstract {

	public function getNegativeVoices ( $owner_id = null )
	{
		if ( !is_null($owner_id) ) {
			$review = new Model_Review();
			$count = $review->getNegativeVoices( $owner_id );

			if ( $count ) {
				return "-" . $count;
			}
		} else {
			return "";
		}
	}
}
