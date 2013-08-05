<?

class Zend_View_Helper_ToModerSalonNum extends Zend_View_Helper_Abstract {
	
	public function toModerSalonNum () {
		
		$salons = new Model_SalonsTest();
		$res = $salons->getToModerNum();
		
		if ( $res ) {
			return "(+" . $res . ")" ;
		} else {
			return $res;
		}
	}
}