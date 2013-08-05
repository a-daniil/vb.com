<?php 

class Form_EditMessForm extends Zend_Form
{
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const SUCCESS = "Настроки успешно сохранены";
	const FAILED = "Не удалось сохранить настройки";
	const NOT_GREATER = "Минимум за один день";
	const NOT_INT = "Введите  целое число";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";
	
	public function init() {		
		
		$this->addElement('checkbox', 'balance', array(
			'label'    => 'о нехватке баланса за',			
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label'),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service', 'id' => 'balance-element'))
			)
		));		
	
		$this->addElement('text', 'days_info', array(
				'filter' => array('StringTrim'),
				'size' => 2,
				'maxlength' => 3,
				'id' => 'days_info',
				'validators' => array(
						array(
								'NotEmpty', false, array('messages' => array(
										Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
								))),
						array(  
								'Int', false, array('messages' => array(
										Zend_Validate_Int::NOT_INT => self::NOT_INT
								))),
						array (
								'GreaterThan', false, array(0, 'messages' => array(
										Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_GREATER
								))),
				),
				'required' => true,
				'label'    => 'дней до отключения приоритетного показа анкет',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label'),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service', 'id' => "days-info-element"))
				)
		));				
		
		$elements[] = 'comments';
		$this->addElement('checkbox', 'comments', array(
				'label'    => 'о новых комментариях к вашим анкетам',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label'),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
				)
		));	
		
		$elements[] = 'moderation';
		$this->addElement('checkbox', 'moderation', array(
				'label'    => 'о проверке фотографий и модерации анкет',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label'),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
				)
		));
		
		$elements[] = 'messages';
		$this->addElement('checkbox', 'messages', array(
				'label'    => 'о новых сообщениях в переписке',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label'),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
				)
		));
		
		$elements[] = 'news';
		$this->addElement('checkbox', 'news', array(
				'label'    => 'о новостях на портале PutanaStars.com',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label'),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
				)
		));
		
		$this->addElement( 'submit', 'submit', array(
				'label' => 'Сохранить',
				'decorators' => array(
					'ViewHelper',
					array('HtmlTag',array('tag'=>'div', 'class' => 'form-element-submit')),
				)
		));
		
		$this->addDisplayGroup(
			$elements,
			'group',
			array("legend" => '')
		);
		
		$this->setDisplayGroupDecorators(array(
			'FormElements',
			'Fieldset',
			'Description',
			array(array('row'=>'HtmlTag'), array('tag' => 'div', 'class' => 'form-fieldset', 'id' => 'edit-mess-fieldset'))
		));
		
		
	}
}