<?php
/**
 * 
 *
 */
class Zend_View_Helper_PhoneFormatAdd extends Zend_View_Helper_Abstract {
     
        public function phoneFormatAdd($info)
        {
        	if (isset($info['phone_add']) && strlen(trim($info['phone_add']))>0){
        		list($info['phone_p1'],$info['phone_p2'])=explode('-',$info['phone_add']);
        	}else {
        		return;
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
