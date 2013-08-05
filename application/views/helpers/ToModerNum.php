<?

class Zend_View_Helper_ToModerNum extends Zend_View_Helper_Abstract {
	
	public function toModerNum () {
		
		$ankets = new Model_AnketsTest();
		$res = $ankets->getToModerNum();
		
		if ( $res ) {
			return "(+" . $res . ")" ;
		} else {
			return $res;
		}
	}
}