<?php 

class Zend_View_Helper_CheckDuplicateReview extends Zend_View_Helper_Abstract {

	public function checkDuplicateReview ( $ank_id, $user_id )
	{
		$reviews = new Model_Review();
		return $isDuplicate = $reviews->checkDuplicateReview($ank_id, $user_id);
		
		
	}

}