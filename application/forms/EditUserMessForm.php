<?php 

class Form_EditUserMessForm extends Zend_Form
{
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const SUCCESS = "Настроки успешно сохранены";
	const FAILED = "Не удалось сохранить настройки";
	const SUCCESS_COLOR = "green";
	const FAILED_COLOR = "red";
	
	public function init() {
		
		$elements[] = 'comments';
		$this->addElement('checkbox', 'comments', array(
			'label'    => 'О новых комментариях к вашим анкетам',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label'),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
			)
		));	
		
		$elements[] = 'messages';
		$this->addElement('checkbox', 'messages', array(
			'label'    => 'О новых сообщениях в переписке',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label'),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'form-element-service'))
			)
		));
		
		$elements[] = 'news';
		$this->addElement('checkbox', 'news', array(
			'label'    => 'О новостях на портале Vbordele.com',
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