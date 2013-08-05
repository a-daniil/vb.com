<?php

class Form_ChangeUserTypeForm extends Zend_Form
{	
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const SUCCESS = "Тип пользователя успешно изменен.";
	const FAILED = "Вы ввели не верный пароль либо пользователь не соответствует критериям";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";
	const NOT_SELECTED = "Выберите одно из значений выпадающего списка.";
	
	const FLAG_MODER = 8;
	const FLAG_TECH = 4;
	
	public function init() {		
		
		$this->addElement('password', 'admin_password', array(
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY		
				)))
			),
			'required' => true,
			'label'    => 'Пароль администратора:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('tag' => 'div')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
			)
		));
		
		$this->addElement('select', 'flag', array(
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SELECTED
				)))
			),
			'required' => true,
			'multiOptions' => array(
				'0' => 'Выбрать',
				self::FLAG_MODER => 'Модератор',
				self::FLAG_TECH => 'Технический администратор'
			),
			'label' => 'Тип пользователя:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('tag' => 'div')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
			)
		));
	
		$this->addElement( 'submit', 'submit', array(
				'label' => 'Сохранить',
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
	
}