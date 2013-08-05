<?php 

class Zend_View_Helper_ShowPositionSalon extends Zend_View_Helper_Abstract {
	public function showPositionSalon ( $per_page ) 
	{	
		$salons = new Model_SalonsTest();		
		$salons = $salons->getSalonsCountByPriority();
		$pages = ceil($salons / $per_page);	
		
		if ( $pages == 1 ) {
			return "Салон показывается на {$pages}-ой странице";
		}
				
		return "Салон показывается c 1-й по {$pages}-ю страницу";		
	}
}