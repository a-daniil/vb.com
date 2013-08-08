<?php 

class Zend_View_Helper_GetRatioVoices extends Zend_View_Helper_Abstract {

	public function getRatioVoices( $owner_id = null )
	{
		if ( !is_null($owner_id) ) {
			$review = new Model_Review();
			$avg_ratio = $review->getRatioVoices( $owner_id );

			if ( $avg_ratio ) {
				return round($avg_ratio, 1);
			}
		} else {
			return "";
		}
	}
}
