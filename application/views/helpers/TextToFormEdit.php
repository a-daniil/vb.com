<?php
/**
 * 
 *
 */
class Zend_View_Helper_TextToFormEdit extends Zend_View_Helper_Abstract {
	
	public function textToFormEdit($text)
	{
	  //return $text;	
		   if( is_array($text) ){
		       return $text;
		   }else{
               return htmlspecialchars(stripslashes($text),ENT_COMPAT | ENT_DISALLOWED); 
		   }
           //return stripslashes($text); 
    }
}
?>
