<?php 

class Zend_View_Helper_ShowSumAnkets extends Zend_View_Helper_Abstract {
	
	public function showSumAnkets ( $filter = null )
	{
		$users = new Model_UsersTest();
		return $users->getSumAnket( $filter );
	} 
	
}