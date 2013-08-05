<?php 

class Zend_View_Helper_IsHaveRequiredSalonPhotos extends Zend_View_Helper_Abstract {
	
	public function isHaveRequiredSalonPhotos( $sid, $count ) {
		$salon = new Model_SalonsTest();
		$info = $salon->getById($sid);
		
		$photolist = array_filter(unserialize($info['photolist']));
		unset($photolist['preview']);
		
		if ( count($photolist) >= $count ) {
			return true;
		} else {
			return false;
		}	
	}
}