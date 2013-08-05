<?php 

class Zend_View_Helper_ShowPosition extends Zend_View_Helper_Abstract {
	public function showPosition ( $per_page, $priority = false, $per = false, $id = false ) 
	{	
		$ankets = new Model_AnketsTest();
		if ( $priority ) {
			$ankets = $ankets->getAnketsCountByPriority();
			$pages = ceil($ankets / $per_page);	
		
			if ( $pages == 1 ) {
				return "Анкета показывается на {$pages}-ой странице";
			}
				
			return "Анкета показывается c 1-й по {$pages}-ю страницу";
		} else {			
			$womens = array(1,2,3,4);
			
			if ( in_array($per, $womens) || !$per ) {
				$ankets = $ankets->fetchClearAnketsList( "performer IN (1,2,3,4)" );
			} else {
				$ankets = $ankets->fetchClearAnketsList( "performer = " . $per );
			}
			
			$pos = 0;
			foreach ( $ankets as $anket ) {
				$pos++;
				
				if ( $anket['id'] == $id) break;
			}			
			
			$page = ceil($pos / $per_page);
			
			if ( $page == 1) {
				return "Анкета показывается на {$page}-й странице({$pos}-я по счету).";				
			}
			
			return "Анкета показывается на {$page}-й странице({$pos}-я по счету) и ее сложно найти Вашим клиентам. Что бы увеличить количество просмотров рекомендуем включить <b>ПРИОРИТЕТНЫЙ</b> показ.";
		}		
	}
}