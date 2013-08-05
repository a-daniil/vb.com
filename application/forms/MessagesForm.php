<?php

class Form_MessagesForm extends Zend_Form
{
	function init() {
		$this->addElement( 'submit', 'submit', array(
				'label' => 'Удалить отмеченные',
				'decorators' => array(
						'ViewHelper',
						array('HtmlTag',array('tag'=>'div', 'class' => 'form-element-submit')),
				)
		));
		
		$this->setDisplayGroupDecorators(array(
				'FormElements',
				'Fieldset',
				'Description',
				array(array('row'=>'HtmlTag'), array('tag' => 'div', 'class' => 'form-fieldset'))
		));
	}
	
	public function isValid($data)
	{
		$isValid = parent::isValid($data);
	
		foreach ( $_POST as $key => $value ) {
			if ( preg_match('/del_/', $key) ) {
				list($prefix, $id) = explode('_', $key);
				$where[] = $id;
			}
		}
	    
		if ( empty($where) ) {
			$isValid = false;
		} 	
			
		return $isValid;		
	}
}