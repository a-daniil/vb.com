<?php

class Form_ShowMessagesForm extends Zend_Form
{
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const SUCCESS = "Сообщение отправлено успешно";
	const FAILED = "Не удалось отправить сообщение";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";
	
	function __construct() {
		$this->setAttrib('enctype', 'multipart/form-data');
		
		parent::__construct();
	}
		
	function init() {		
		$this->addElement('textarea', 'answer', array(
			'class' => 'input-xxlarge',
			'rows' => 9,
			'cols' => 41,
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,
			'label'    => 'Ответ:',
			'decorators' => array(
					'ViewHelper',
					'Errors',
					array('Label', array('class' => 'control-label', 'style' => 'width: 180px')),
					array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group')),
			)
		));

		$file = new Zend_Form_Element_File('upload');
		$file->setLabel('Прикрепить фото:')
			->setDestination('../public/user_messages_photos');
		$this->addElement($file);

		$this->addElement (new Zend_Form_Element_Button('delete', array(
			'class' => 'btn blue',
			'label'	=> 'Удалить',
			'id'	=>	'delete-button',
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag',array('tag'=>'div', 'class' => 'form-actions')),
			)
		)));

		$this->addElement( 'submit', 'submit', array(
			'class' => 'btn blue',
			'label' => 'Ответить',
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag',array('tag'=>'div', 'class' => 'form-actions')),
			)
		));

		$this->addDisplayGroup(array('delete', 'submit'), 'submitButtons', array(
				'decorators' => array(
					'FormElements',
					array('HtmlTag',array('tag'=>'div', 'class' => 'form-actions')),
			),
		));
	}
}