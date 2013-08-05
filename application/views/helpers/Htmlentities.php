<?php
/**
 * 
 *
 */
class Zend_View_Helper_Htmlentities extends Zend_View_Helper_Abstract {
	
	public function htmlentities($text)
	{
	  //return $text;	

           return htmlspecialchars(stripslashes($text),ENT_COMPAT | ENT_DISALLOWED); 
            
                }
}
?>
