<?php
/**
 * 
 *
 */
class Zend_View_Helper_TextToView extends Zend_View_Helper_Abstract {
	
	public function textToView($text)
	{
	  //return $text;	

           //return htmlspecialchars(stripslashes($text),ENT_COMPAT | ENT_DISALLOWED); 
           return stripslashes($text); 
                }
}
?>
