<?php

class Zend_View_Helper_GetRightUri extends Zend_View_Helper_Abstract {
	
	public function getRightUri( $request, $escape_filter = false, $escape_page = false ) {
		$uri = "?";
		
		foreach ( $request as $k => $v) {
			if ( $k == 'filter' && $escape_filter ) {
				continue;
			}
			
			if ( $k == 'p' && $escape_page ) {
				continue;
			}
			$uri .=  $k . "=" . $v . "&";
		}
		
		return $uri;
	}
}