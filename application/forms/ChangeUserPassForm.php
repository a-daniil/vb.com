<?php 

class Form_ChangeUserPassForm extends Zend_Form
{	
	const NOT_SAME = 'Пароли не совпадают';
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const SUCCESS = "Пароль успешно изменен";
	const FAILED = "Вы ввели не верный пароль";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";	
	
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
		
		$this->addElement('password', 'new_pass', array(
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,
			'label'    => 'Новый пароль:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('tag' => 'div')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
			)
		));
		
		$this->addElement('password', 'new_pass_repeat', array(
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,			
			'label'    => 'Новый пароль еще раз:',
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
	
	public function isValid($data)
	{
		$isValid = parent::isValid($data);
	
		if ( $this->getElement('new_pass')->getValue() != $this->getElement('new_pass_repeat')->getValue() ) {
			$this->getElement('new_pass')->addError('Пароли не совпадают');
			$this->getElement('new_pass_repeat')->addError('Пароли не совпадают');
			$isValid = false;
		}
	
		return $isValid;
	}
	
}