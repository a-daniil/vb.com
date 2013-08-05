<?php

class Zend_View_Helper_RenderOnchangeableSelect extends Zend_View_Helper_Abstract {
	
	public function renderOnchangeableSelect ($param_name,$options,$default_option=null, $class=null)
	{
		$return = '<select onchange="this.form.submit();" name="'.$param_name.'" '.(($class)?('class = "'.$class.'"'):'').' >';
	
		foreach ($options as $key => $value) {
			$return.='<option '.(($default_option && $default_option == $key)?'selected':'').' value="'.$key.'">'.$value.'</option>';
		}
	
		$return.='</select>';
		return $return;
	}
	
}