<?php

class Zend_View_Helper_IsHavePhotoCheck extends Zend_View_Helper_Abstract {
	
	public function isHavePhotoCheck( $aid ) {
		$anketa = new Model_AnketsTest();
		$info = $anketa->getById($aid);
		
		$photo_check = array_filter(unserialize($info['photo_check']));
		
		if ( count($photo_check) > 0 ) {
			return true;
		} else {
			return false;
		}		
	}
}