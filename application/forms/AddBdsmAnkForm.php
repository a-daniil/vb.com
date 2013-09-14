<?php 

class Form_AddBdsmAnkForm extends Form_AddAnkForm
{
	public function init() {
		/*
		  Permanently set hardcoded perfomrer
		*/
		$values = $this->content->performer->toArray();
		$this->addElement('select', 'performer_el', array(
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				))),
			),
			//'required' => true,
			'multiOptions' => array_slice($values, $this->performer, 1),
			'value' => $this->performer,
			'label'    => 'Исполнитель:',
			'disable'  => true,
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));	
		/*
		  end of setting permanently hardcoded perfomer
		*/

		$this->addElement('select', 'type', array(
			'required' => true,
			'multiOptions' => $this->params['types'],
			'label'    => 'Салон:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		$this->addElement('text', 'name', array(
			'filter' => array('StringTrim'),
			'validators' => array(
			 	 array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,
			'label'    => 'Имя:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)	
		));	

		list($height_min,$height_max)=explode('-',$this->content->height);
		$this->addElement('text', 'height', array(
			'filter' => array('StringTrim'),
			'validators' => array( 
				array (
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array (
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				))),
				array (
					'GreaterThan', false, array($height_min, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => sprintf( self::NOT_RANGE, $height_min, $height_max)
				))),
				array (
					'LessThan', false, array($height_max, 'messages' => array(
						Zend_Validate_LessThan::NOT_LESS => sprintf( self::NOT_RANGE, $height_min, $height_max)
				)))
			),
			'required' => true,
			'label'    => 'Рост:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		list($weight_min,$weight_max)=explode('-',$this->content->weight);
		$this->addElement('text', 'weight', array(
			'filter' => array('StringTrim'),
			'validators' => array( 
				array(
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
				    'Int', false, array('messages' => array(
					    Zend_Validate_Int::NOT_INT => self::NOT_INT
				))),
				array(
					'GreaterThan', false, array($weight_min-1, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => sprintf( self::NOT_RANGE, $weight_min, $height_max)
				))),
				array(
					'LessThan', false, array($weight_max, 'messages' => array(
						Zend_Validate_LessThan::NOT_LESS => sprintf( self::NOT_RANGE, $weight_min, $weight_max)
				)))
			),	
			'required' => true,
			'label'    => 'Вес:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		list($age_min,$age_max)=explode('-',$this->content->age);
		$this->addElement('text', 'age', array(
			'filter' => array('StringTrim'),
			'validators' => array( 
				array(
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
				    'Int', false, array('messages' => array(
					    Zend_Validate_Int::NOT_INT => self::NOT_INT
				))),
				array(
					'GreaterThan', false, array($age_min-1, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => sprintf( self::NOT_RANGE, $age_min, $age_max)
				))),
				array(
					'LessThan', false, array($age_max+1, 'messages' => array(
						Zend_Validate_LessThan::NOT_LESS => sprintf( self::NOT_RANGE, $age_min, $age_max)
				)))
			),	
			'required' => true,
			'label'    => 'Возраст:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'clothing', array(
			'filter' => array('StringTrim'),
			'validators' => array( 				
				array(
				    'Int', false, array('messages' => array(
					    Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),	
			'label'    => 'Размер одежды:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		list($breast_min,$breast_max)=explode('|',$this->content->breast);
		$this->addElement('select', 'breast', array(
			'validators' => array(
				array(
					'GreaterThan', false, array($breast_min, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => sprintf( self::NOT_RANGE, $breast_min, $breast_max)
				))),
				array(
					'LessThan', false, array($breast_max, 'messages' => array(
						Zend_Validate_LessThan::NOT_LESS => sprintf( self::NOT_RANGE, $breast_min, $breast_max)
				)))
			),	
			'multiOptions' => $this->content->breast_form->toArray(),
			'label'    => 'Размер груди:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));	
		
		$this->addElement('select', 'exotics', array(
			'multiOptions' => $this->content->exotics->toArray(),
			'label'    => 'Экзотика:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		$this->addDisplayGroup(array(
				'name',
				'height',
				'weight',
				'age',
				'clothing',
				'breast',
				'exotics'
		),
				'info',
				array("legend" => "Параметры девушки.")
		);

		$this->addElement('text', 'phone', array(
			'filter' => array('StringTrim'),
			'validators' => array( 
				array(
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
					'Regex', false, array('pattern' => '/^([+7-8]{1,2})?([(-\s]+)?(\d{3})([)-\s]+)?(\d{3})([-\s]+)?(\d{2})([-\s]+)?(\d{2})$/', 'messages' => array(
						Zend_Validate_Regex::NOT_MATCH => self::NOT_PHONE
				)))
			),	
			'required' => true,
			'label'    => 'Телефон:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$city = $this->createElement('select', 'city', array(
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				)))
			),
			'multiOptions' => $this->content->cities->toArray(),						
			'required' => true,	
			'label'    => 'Город:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));	
		$city->setValue($this->city);		
		$this->addElement($city);
	
		$this->addElement('select', 'district', array(
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				)))
			),
			'multiOptions' => $this->district_list,			
			'required' => true,
			'label'    => 'Район:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('select', 'metro', array(
			'validators' => array(
			    array(
				    'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				)))
			),
			'multiOptions' => $this->metro_list,
			'required' => true,
			'label'    => 'Метро:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('select', 'place', array(
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				)))
			),
			'multiOptions' => $this->content->places->toArray(),
			'required' => true,
			'label'    => 'Место встречи:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$sauna = $this->addElement('checkbox', 'sauna', array(
				'label'    => "Возможен выезд в сауну",
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group-service hide-service', 'id' => 'sauna-service'))
				)
		));
		
		$this->addDisplayGroup(array(
				'phone',
				'city',
				'district',
				'metro',
				'place',
				'sauna',
		),
				'contact-info',
				array("legend" => "Контактные данные.")
		);
		
		$this->addElement('text', 'price_an', array(
				'filter' => array('StringTrim'),
				'validators' => array(
						array(
								'Int', false, array('messages' => array(
										Zend_Validate_Int::NOT_INT => self::NOT_INT
								)))
				),
				'label'    => 'Доплата за анал:',
				'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
				)
		));
		
		$this->addElement('text', 'price_1h_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Один час:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_2h_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Два часа:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_n_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(
			    array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
			    )))
			),
			'label'    => 'Ночь:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addDisplayGroup(array(
			'price_an',
			'price_1h_ap',
			'price_2h_ap',
			'price_n_ap',
		),
			'price-ap-info',
			array("legend" => "Апартаменты:")
		);
		
		$this->addElement('text', 'price_1h_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Один час:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_2h_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Два часа:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_n_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(				
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Ночь:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addDisplayGroup(array(
				'price_1h_ex',
				'price_2h_ex',
				'price_n_ex',
		),
				'price-ex-info',
				array("legend" => "Выезд:")
		);
		
		$this->addElement('textarea', 'about', array(
			'class' => 'span12',
			'rows' => 9,
			'cols' => 41,
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'label'    => 'О себе',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'form-about')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'name_eng', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
					'Regex', false, array('pattern' => '/^[A-Za-z\s]+$/', 'messages' => array(
						Zend_Validate_Regex::NOT_MATCH => self::NOT_ENG_LETTERS
				)))
			),
			'required' => true,
			'label'    => 'Имя: (на англ.)',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_an', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Доплата за анал:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_1h_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(				
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Один час:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_2h_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(				
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Два часа:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_n_ap', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Ночь:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addDisplayGroup(array(
				'price_i_an',
				'price_i_1h_ap',
				'price_i_2h_ap',
				'price_i_n_ap',
		),
				'price_ap_i_info',
				array("legend" => "Апартаменты:")
		);
		
		$this->addElement('text', 'price_i_1h_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Один час:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_2h_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Два часа:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'price_i_n_ex', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'label'    => 'Ночь:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addDisplayGroup(array(
				'price_i_1h_ex',
				'price_i_2h_ex',
				'price_i_n_ex',
		),
				'price_ex_i_info',
				array("legend" => "Выезд:")
		);
		
		$this->addElement('textarea', 'about_i', array(
			'class' => 'span12',
			'rows' => 9,
			'cols' => 41,
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'label'    => 'О себе для иностранцев (на английском языке)',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'add-ank-form-about')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		foreach ( $this->content->srv->toArray() as $part => $srv ) {
			$legend = array_shift($srv);
			$nn=-1;
			$elements = array();			
			while( $val = array_shift($srv) ) {
				$elements[] = $part . '_' . ++$nn;
				$service = $this->addElement('checkbox', $part. '_' . $nn, array(
					'label'    => $val,
					'decorators' => array(
						'ViewHelper',
						'Errors',
						array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
						array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group', 'style' => 'margin-bottom: 5px;'))
					)
				));
			}

			$service_group = $this->addDisplayGroup(
				$elements,
				'service_group_' . $part,
				array("legend" => $legend)
			);
		}

		$this->addElement('text', 'only', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'label'    => 'Только у меня',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));

		$this->addElement( 'submit', 'submit', array(
			'class' => 'btn btn-large blue',
			'label' => $this->getSubmitLabel(),
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag',array('tag'=>'div', 'class' => 'control-group')),
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
	
		foreach ( $this->content->srv->toArray() as $srv=>$list ) {
			$serv=0;
			foreach ( $list as $key=>$null ) {
				if ( $this->getValue( $srv.'_'.$key ) ) {
					$serv+=1<<$key;
				}
			}
			$data['srv_'.$srv]=$serv;
		}
	
		if ( !$data['srv_main'] && !$data['srv_add'] && !$data['srv_strip'] && !$data['srv_mass'] && !$data['srv_bdsm'] ) {
			$this->getElement('main_0')->addError('Хотя бы одна услуга должна быть указана');				
			$isValid = false;
		}
	
		if ( $this->getElement('place')->getValue() == 1 ) {
			if ( !$this->getElement('price_1h_ap')->getValue() && !$this->getElement('price_2h_ap')->getValue() && !$this->getElement('price_n_ap')->getValue() ) {
				$this->getElement('price_1h_ap')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в рублях.');
				$isValid = false;
			}
		}
		
		if ( $this->getElement('place')->getValue() == 2 || $this->getElement('place')->getValue() == 3) {
			if ( !$this->getElement('price_1h_ex')->getValue() && !$this->getElement('price_2h_ex')->getValue() && !$this->getElement('price_n_ex')->getValue() ) {
				$this->getElement('price_1h_ex')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в рублях.');
				$isValid = false;
			}
		}
	
		/* for validation international prices
		if ( !$this->getElement('price_i_1h_ap')->getValue() && !$this->getElement('price_i_2h_ap')->getValue() && !$this->getElement('price_i_n_ap')->getValue() ) {
			$this->getElement('price_i_1h_ap')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в долларах США.');
			$isValid = false;
		}
	
		if ( !$this->getElement('price_i_1h_ex')->getValue() && !$this->getElement('price_i_2h_ex')->getValue() && !$this->getElement('price_i_n_ex')->getValue() ) {
			$this->getElement('price_i_1h_ex')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в долларах США.');
			$isValid = false;
		}
	    */
		return $isValid;
	}
}