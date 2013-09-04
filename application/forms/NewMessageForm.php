<?php

class Form_NewMessageForm extends Zend_Form
{
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const NOT_SELECTED = "Укажите получателя.";
	const SUCCESS = "Сообщение отправлено успешно";
	const FAILED = "Не удалось отправить сообщение";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";

	const TO_ADMIN = "TO_ADMIN";
	const TO_MODER = "TO_MODERATION";

	const FROM_ADMIN = "FROM_ADMIN";
	const FROM_MODER = "FROM_MODERTATION";

	protected $_uid;
	protected $_login;

	public function __construct( $uid = false, $login = false ) {
		$this->_uid = $uid;
		$this->_login = $login;
		
		parent::__construct();
	}

	function init() {
		if ( $this->_uid && $this->_login ) {
			$this->addElement('select', 'send_to', array(
				'required' => true,
				'multiOptions' => array(
					$this->_uid => $this->_login
				),
				'label' => 'Кому:',
				'decorators' => array(
					'ViewHelper',
					'Errors',
					array('Label', array('tag' => 'div')),
					array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
				)
			));
		} else {
			$this->addElement('select', 'send_to', array(
					'validators' => array(
							array(
									'Regex', false, array('pattern' => '/^TO_(.*)$/', 'messages' => array(
											Zend_Validate_Regex::NOT_MATCH => self::NOT_SELECTED
									)))
					),
					'required' => true,
					'multiOptions' => array(
							'0' => 'Выбрать',
							self::TO_MODER => 'Модерация анкет',
							self::TO_ADMIN => 'Финансовые вопросы'
					),
					'label' => 'Кому:',
					'decorators' => array(
							'ViewHelper',
							'Errors',
							array('Label', array('tag' => 'div')),
							array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
					)
			));
		}

		$this->addElement('text', 'subject', array(
			'filter' => array('StrignTrim'),
			'validators' => array(
			 	 array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,
			'label'    => 'Тема сообщения:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('tag' => 'div')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
			)
		));

		$this->addElement('textarea', 'body', array(
			'rows' => 10,
			'cols' => 85,
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,	
			'label'    => 'Сообщение',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'form-about')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element'))
			)
		));	

		$file = new Zend_Form_Element_File('upload');
		$file->setLabel('Прикрепить фото:')
			->setDestination('../public/user_messages_photos');
		$this->addElement($file);

		$this->addElement( 'submit', 'submit', array(
			'label' => 'Отправить',
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