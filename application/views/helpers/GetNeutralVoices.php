<?php 

class Zend_View_Helper_GetNeutralVoices extends Zend_View_Helper_Abstract {

	public function getNeutralVoices ( $owner_id = null )
	{
		if ( !is_null($owner_id) ) {
			$review = new Model_Review();
			$count = $review->getNeutralVoices( $owner_id );

			if ( $count ) {
				return $count;
			}
		} else {
			return "";
		}
	}
}
