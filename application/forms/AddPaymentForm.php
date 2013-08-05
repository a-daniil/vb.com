<?php 

class Form_AddPaymentForm extends Zend_Form
{	
	/* Константы сообщений об ошибках */
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const NOT_INT = "'%value%' не является целым числом.";
	const NOT_SPECIFIED = "Выберите одно из значений выпадающего списка.";
	const NOT_GREATER = "Значение меньше минмально возможного.";
	const NOT_LESS = "Значение больше максимально возможного.";
	const NOT_RANGE = "Допустимый дипазон значений от %d до %d.";
	
	protected $content;	
	
	function __construct( $content = null ) {	
		$this->content = $content;
		
		parent::__construct();
	}
	
	function init () {
		$this->addElement('select', 'type', array(
			'required'     => true,
			'multiOptions' => $this->content->paysystems->toArray(),
			'validators' => array(
				array (
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				))),
			),
			'label'        => 'Способ оплаты:',
				'decorators' => array(
					'ViewHelper',
					'Errors',
					array('Label', array('tag' => 'div')),
					array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'add-ank-form-element'))
				)				
		));
		
		$this->addElement( 'submit', 'submit', array(
			'label' => 'Оплатить',
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag',array('tag'=>'div', 'class' => 'add-ank-form-element-submit')),
			)
		));
	}
	
}