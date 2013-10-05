<?

class Zend_View_Helper_GetAnketsPerMenuItem extends Zend_View_Helper_Abstract {

	
	public function getAnketsPerMenuItem ( $performer, $type )
	{
		if ( $type == 1 ) {	
			$ankets = new Model_AnketsTest();
			$count = $ankets->getAnketsCountByPerformerId($performer);
		} elseif ( $type == 2 ) {
			$salons = new Model_SalonsTest();
			$count = $salons->getViewedSalonsCount();
		}

		return $count;
	}
}