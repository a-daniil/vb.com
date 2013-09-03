<?php

class Zend_View_Helper_CountNewReviews extends Zend_View_Helper_Abstract {

	public function countNewReviews ( $user_id = null )
	{
		if ( !is_null($user_id) ) {
			$reviews = new Model_Review();
			$count = $reviews->countNewReviews( $user_id );

			if ( $count ) {
				return " (" . $count . ")";
			}
		} else {
			return "";
		}
	}

}