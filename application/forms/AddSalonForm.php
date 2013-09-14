<?php 

class Form_AddSalonForm extends Zend_Form
{
	/* Константы сообщений об ошибках */
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const NOT_INT = "'%value%' не является целым числом.";
	const NOT_SPECIFIED = "Выберите одно из значений выпадающего списка.";
	const NOT_GREATER = "Значение меньше минмально возможного.";
	const NOT_LESS = "Значение больше максимально возможного.";
	const NOT_RANGE = "Допустимый дипазон значений от %d до %d.";
	const NOT_PHONE = "Неверный формат телефона.";
	const NOT_ENG_LETTERS = "Только английские(латинские) символы";

	/* Типы салонов */
	const INTIM = 1;
	const MASS = 2;
	const BDSM = 3;

	protected $content;
	protected $params;
	
	protected $district_list;
	protected $metro_list;
	protected $cityParam;

	public function __construct( $content = null, $params = array() ){
		$this->content = $content;
		$this->params = $params;
		
		if ( $params['city'] ) {		
			$this->cityParam = $params['city'];
		} else {
			$this->cityParam = 2;
		}
		
		switch ( $params['city'] ) {
			case 2 :
				$this->metro_list = $this->content->metro_spb->toArray();
				$this->district_list = $this->content->district_spb->toArray();
			break;
			case 1 :
				$this->metro_list = $this->content->metro_msk->toArray();
				$this->district_list = $this->content->district_msk->toArray();
			break;
			default :
				$this->metro_list = $this->content->metro_spb->toArray();
				$this->district_list = $this->content->district_spb->toArray();
		}			

		parent::__construct();
	}

	public function init() {
		$this->addElement('select', 'type', array(
			'required' => true,
			'multiOptions' => $this->content->types_of_salon->toArray(),
			'label'    => 'Тип салона:',
			'required' => true,
			'validators' => array(
				array(
					'GreaterThan', false, array(0, 'messages' => array(
						Zend_Validate_GreaterThan::NOT_GREATER => self::NOT_SPECIFIED
				)))
			),
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'name', array(
			'filter'     => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'required' => true,
			'label' => 'Название:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'), array('tag'=>'div', 'class' => 'control-group'))
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
		$city->setValue($this->cityParam);		
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
		
		$this->addElement('text', 'address', array(
			'filter'     => array('StringTrim'),
			'label' => 'Адрес:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'), array('tag'=>'div', 'class' => 'control-group'))
			)
		));		
		
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
		
		$this->addElement('text', 'phone_add', array(
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
 			'label'    => 'Телефон:',
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
			'price_1h_ap',
			'price_2h_ap',
			'price_n_ap',
		),
			'price-ap-info',
			array("legend" => "Апартаменты (в руб.):")
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
			array("legend" => "Выезд (в руб.):")
		);
		
		$this->addElement('text', 'girl_number', array(
			'filter'     => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'required' => true,
			'label' => 'Количество девушек:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'), array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('text', 'room_number', array(
			'filter'     => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', array('breakChainOnFailure' => true), array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				))),
				array(
					'Int', false, array('messages' => array(
						Zend_Validate_Int::NOT_INT => self::NOT_INT
				)))
			),
			'required' => true,
			'label' => 'Количество комнат:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'), array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$options = array( '00 : 00', '01 : 00', '02 : 00', '03 : 00', '04 : 00', '05 : 00', '06 : 00', '07 : 00',
			'08 : 00', '09 : 00', '10 : 00', '11 : 00', '12 : 00', '13 : 00', '14 : 00', '15 : 00', '16 : 00', '17 : 00',
			'18 : 00', '19 : 00', '20 : 00', '21 : 00', '22 : 00', '23 : 00'); 
		
		$this->addElement('select', 'time_from', array(
			'multiOptions' => $options,
			'label'    => 'С:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$this->addElement('select', 'time_to', array(
			'multiOptions' => $options,
			'label'    => 'До:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));		
		
		
		$this->addDisplayGroup(array(
			'time_from',
			'time_to',
			),
			'work-time',
			array("legend" => "Вермя работы:")
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
			'label'    => 'О салоне:',
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
			'label'    => 'Название на английском:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
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
			'label'    => 'О салоне на английском:',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'form-about')),
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
			'price_i_1h_ap',
			'price_i_2h_ap',
			'price_i_n_ap',
		),
			'price_ap_i_info',
			array("legend" => "Апартаменты (для иностранцев в $):")
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
			array("legend" => "Выезд (для иностранцев в $):")
		);
		
		
		$elements[] = 'sauna';
		$service = $this->addElement('checkbox', 'sauna', array(
			'label'    => 'Сауна',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$elements[] = 'bilyrd';
		$service = $this->addElement('checkbox', 'bilyrd', array(
			'label'    => 'Бильярд',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$elements[] = 'devushki';
		$service = $this->addElement('checkbox', 'devushki', array(
			'label'    => 'Джакузи',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$elements[] = 'bar';
		$service = $this->addElement('checkbox', 'bar', array(
			'label'    => 'Бар',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$elements[] = 'karaoke';
		$service = $this->addElement('checkbox', 'karaoke', array(
			'label'    => 'Караоке',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$elements[] = 'kalyan';
		$service = $this->addElement('checkbox', 'kalyan', array(
			'label'    => 'Кальян',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));
		
		$service_group = $this->addDisplayGroup(
				$elements,
				'service_group',
				array("legend" => 'Дополнительные удобства')
		);
		
		$this->addElement('text', 'only', array(
			'filter' => array('StringTrim'),
			'validators' => array(
				array(
					'NotEmpty', false, array('messages' => array(
						Zend_Validate_NotEmpty::IS_EMPTY => self::NOT_EMPTY
				)))
			),
			'label'    => 'Только у нас',
			'decorators' => array(
				'ViewHelper',
				'Errors',
				array('Label', array('class' => 'control-label', 'style' => 'width: 270px')),
				array(array('row'=>'HtmlTag'),array('tag'=>'div', 'class' => 'control-group'))
			)
		));	
		
		foreach ( $this->content->srv_salon->toArray() as $part => $srv ) {
			if( in_array( $part, array('intim', 'mass', 'strip', 'bdsm') ) ) {
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
		}
		
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
	
		foreach ( $this->content->srv_salon->toArray() as $srv=>$list ) {
			$serv=0;
			foreach ( $list as $key=>$null ) {
				if ( $this->getValue( $srv.'_'.$key ) ) {
					$serv+=1<<$key;
				}
			}
			$data['srv_'.$srv]=$serv;
		}
		
		if ( !$data['srv_intim'] && !$data['srv_mass'] && !$data['srv_strip'] && !$data['srv_bdsm']  ) {
			$this->getElement('intim_0')->addError('Хотя бы одна услуга должна быть указана');
			$this->getElement('mass_0')->addError('Хотя бы одна услуга должна быть указана');
			$this->getElement('bdsm_0')->addError('Хотя бы одна услуга должна быть указана');
			$isValid = false;				
		}		
	
		if ( !$this->getElement('price_1h_ap')->getValue() && !$this->getElement('price_2h_ap')->getValue() && !$this->getElement('price_n_ap')->getValue() ) {
			$this->getElement('price_1h_ap')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в рублях.');
			$isValid = false;
		}
		
		if ( !$this->getElement('price_i_1h_ap')->getValue() && !$this->getElement('price_i_2h_ap')->getValue() && !$this->getElement('price_i_n_ap')->getValue() ) {
			$this->getElement('price_i_1h_ap')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в долларах США.');
			$isValid = false;
		}	
		
		if ( $this->isIntim() ) {		
			if ( !$this->getElement('price_1h_ex')->getValue() && !$this->getElement('price_2h_ex')->getValue() && !$this->getElement('price_n_ex')->getValue() ) {
				$this->getElement('price_1h_ex')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в рублях.');
				$isValid = false;
			}	
	
			if ( !$this->getElement('price_i_1h_ex')->getValue() && !$this->getElement('price_i_2h_ex')->getValue() && !$this->getElement('price_i_n_ex')->getValue() ) {
				$this->getElement('price_i_1h_ex')->addError('Должно быть заполнено хотя бы одно поле по указаному месту встречи. Цены должны быть указанны в долларах США.');
				$isValid = false;
			}	
		}	
			
		return $isValid;
	}
	
	private function isIntim() {
		
		$type = $this->getElement('type')->getValue();
		
		if ( $type == 1 ) {
			return true;
		} else {
			return false;
		}		
	}
	
	/*
	 * Get right label for submit button dependent on new param
	*/
	protected function getSubmitLabel() {
		if ( $this->params['new'] ) {
			return "Далее";
		} else {
			return "Сохранить";
		}
	}
}