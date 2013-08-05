<?php

class Zend_View_Helper_IsHaveRequiredPhotos extends Zend_View_Helper_Abstract {
	
	public function isHaveRequiredPhotos( $aid, $count ) {
		$anketa = new Model_AnketsTest();
		$info = $anketa->getById($aid);
		
		$photolist = array_filter(unserialize($info['photolist']));
		unset($photolist['preview']);
		
		if ( count($photolist) >= $count ) {
			return true;
		} else {
			return false;
		}	
	}
}