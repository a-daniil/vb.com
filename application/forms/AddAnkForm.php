<?php 

class Form_AddAnkForm extends Zend_Form
{
	/* Константы сообщений об ошибках */
	const NOT_EMPTY = "Это поле обязательно к заполнению.";
	const NOT_INT = "'%value%' не является целым числом.";
	const NOT_SPECIFIED = "Выберите одно из значений выпадающего списка.";
	const NOT_GREATER = "Значение меньше минмально возможного.";
	const NOT_LESS = "Значение больше максимально возможного.";
	const NOT_RANGE = "Допустимый дипазон значений от %d до %d";
	const NOT_PHONE = "Неверный формат телефона.";
	const NOT_ENG_LETTERS = "Только английские(латинские) символы";

	protected $performer;
	protected $content;
	protected $params;
	
	protected $district_list;
	protected $metro_list;
	protected $city;

	public function __construct( $performer = 1, $content = null, $params = array() ){
		$this->performer = $performer;
		$this->content = $content;
		$this->params = $params;
		$this->city = $params['city'];
		
		switch ( $params['city'] ) {
			case 2 :
					$this->metro_list = $this->content->metro_spb->toArray();
					$this->district_list = $this->content->district_spb->toArray();
				break;
			case 1 :
					$this->metro_list = $this->content->metro_msk->toArray();
					$this->district_list = $this->content->district_msk->toArray();
				break;
		}

		parent::__construct();
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