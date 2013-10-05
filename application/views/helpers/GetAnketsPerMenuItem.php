<?

class Zend_View_Helper_GetAnketsPerMenuItem extends Zend_View_Helper_Abstract {

	
	public function getAnketsPerMenuItem ( $performer )
	{
		$ankets = new Model_AnketsTest();
		$count = $ankets->getAnketsCountByPerformerId($performer);

		return $count;
	}
}