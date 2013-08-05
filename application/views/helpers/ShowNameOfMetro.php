<?

class Zend_View_Helper_ShowNameOfMetro extends Zend_View_Helper_Abstract {
	
	public function showNameOfMetro ( $id )
	{
		$metro = new Model_Metro();
		return $metro->getName( $id );
	}
}
