<?php
/**
 * 
 *
 */
class Zend_View_Helper_PhoneFormat extends Zend_View_Helper_Abstract {
    
        public function phoneFormat($info)
        {
        	if (isset($info['phone']) && strlen(trim($info['phone']))>0){
        		list($info['phone_p1'],$info['phone_p2'])=explode('-',$info['phone']);
        	}
        	$phone = '';
        	$phone .= '+7 (';
        	$phone .= $info['phone_p1'].') ';
        	$phone .= mb_substr($info['phone_p2'], 0,3).'-';
        	$phone .= mb_substr($info['phone_p2'], 3,2).'-';
        	$phone .= mb_substr($info['phone_p2'], 5,2);
        	return $phone;//.' : '.$info['phone_p1'];
        }
}
?>
