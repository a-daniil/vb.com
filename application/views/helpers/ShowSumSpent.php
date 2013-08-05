<?php 

class Zend_View_Helper_ShowSumSpent extends Zend_View_Helper_Abstract {
	
	public function showSumSpent ( $filter = null )
	{
		$users = new Model_UsersTest();
		return $users->getSumSpent( $filter );
	} 
	
}