<? 

class Zend_View_Helper_ToModerNumComment extends Zend_View_Helper_Abstract {
	
	public function toModerNumComment ()
	{				
		$comment = new Model_CommentsTest();
		$res = $comment->getToModerNum();
		
		if ( $res ) {
			return "(+" . $res . ")" ;
		} else {
			return $res;
		}
	}
}
