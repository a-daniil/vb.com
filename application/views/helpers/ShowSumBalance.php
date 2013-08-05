<?php 

class Zend_View_Helper_ShowSumBalance extends Zend_View_Helper_Abstract {
	
	public function showSumBalance ( $filter = null )
	{
		$users = new Model_UsersTest();
		return $users->getSumBalance( $filter );
	} 
	
}