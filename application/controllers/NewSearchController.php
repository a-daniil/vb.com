<?php

class NewSearchController extends Zend_Controller_Action {
	
	const USR_ADM = 0;
	
	protected $config, $content, $settings, $admin;
	protected $mBanner;
	
	public function init() {
		$auth = Zend_Auth::getInstance()->getIdentity();
				
		if ($auth ) {
			$this->view->auth = $auth->user_login;
			($this->admin = $auth->flags & 1<<self::USR_ADM) ? true : false;
		} 
		else {
			$this->admin = false;
		}
		
		$this->view->admin = $this->admin;
		$this->config = Zend_Registry::get('config');
		$this->content = Zend_Registry::get('content');
		
		$this->view->controller = $this->_request->getControllerName();
		$this->view->performer = $this->content->performer->toArray();
		$this->view->types = $this->content->types->toArray();
		$this->view->cities = $this->content->cities->toArray();
		$this->view->places = $this->content->places->toArray();
		$this->view->novelty = $this->content->novelty->toArray();
		$this->view->exotics = $this->content->exotics->toArray();
		$this->view->districts = $this->content->district_spb->toArray();		
		$this->view->services = $this->content->srv->toArray();
		$this->view->srv_short = $this->content->srv_short->toArray();
		$this->view->url_user_ph = $this->config->path->url_user_ph;		
		$this->view->banner_path = $this->config->path->url_banners;
		$this->view->srv_short_list = true;
		$this->view->errors = array();
		
		$info['city'] = 2;
		if ( $info['city'] == 1 ) {
			$this->view->metro_list = $this->content->metro_msk->toArray();
		} 
		elseif ( $info['city'] == 2 ) {
			$this->view->metro_list = $this->content->metro_spb->toArray();
		}
		
		include_once 'Settings.php';
		$settings = new Settings;
		$this->settings = $settings->get();
		
		include_once 'Meta.php';
		$meta = new Meta;
		$this->view->meta = array();
		$meta_data = $meta->get();		
		
		foreach ( $meta_data as $key => $value ) {
			$tmp1=explode('{',$value);
			$string=array_shift($tmp1);
			foreach($tmp1 as $tmp2){
				$tmp3=explode('}',$tmp2);
				$tmp4=explode(',',$tmp3[0]);
				$string.=$tmp4[rand(0,count($tmp4)-1)];
				$string.=$tmp3[1];
			}
			$this->view->meta[$key]=$string;			
		}
		unset( $meta, $meta_data, $settings, $auth );
		$page = $this->_hasParam('ap') ? intval( $this->_getParam('ap') ) : 1;		
		
		include_once 'MenuItems.php';
		$mMenuItems = new MenuItems();
		$this->view->menu_items = $mMenuItems->get_all_items();
	}
	
	public function indexAction() {	
		$this->view->price_options = $this->content->price_options->toArray();
		$this->view->breast_options = $this->content->breast_options->toArray();
		$this->view->weight_options = $this->content->weight_options->toArray();
		$this->view->height_options = $this->content->height_options->toArray();
		$this->view->age_options = $this->content->age_options->toArray();		
		$this->view->menu_sub = array( 'active' => 'default' );	
	}
	
	public function resultAction() {	
		if ( $this->_hasParam( 'st' ) ) $this->view->menu_sub['active'] = 'st';
		if ( $this->_hasParam( 'v' ) ) $this->view->menu_sub['active'] = 'v';
		if ( $this->_hasParam( 'comments' ) ) $this->view->menu_sub['active'] = 'rv';

	    if($this->_hasParam('metro')){
        	$metro_arr = $this->_getParam('metro');
            if(is_array($metro_arr) && count($metro_arr) > 0){
            	$i = 0;
                $metro_str = '('; 
                foreach ($metro_arr as $metro){
                	$i++;
                    $metro_str .= (($i>1)?' OR ':'').'metro = "'. $this->view->metro_list[$metro] . '"';
                    $info["Метро"] .= $this->view->metro_list[$metro] . " "; 
                }
                $params[] = $metro_str.')';
                unset($i, $metro_str, $metro);
            }
        	unset($metro_arr);
        } 

		$page = $this->_hasParam('p') ? intval( $this->_getParam('p') ) : 1;

		if(  $this->_getParam('performer') <> 0 ) {
			$per = $this->_getParam('performer');
			$info["Анкеты"] = $this->view->performer[$per];
		} else {
			$per = false;
		}
		if(  $this->_getParam('type') <> 0 ) {
			$params[] = "type = {$this->_getParam('type')}";
			$info["Тип"] = $this->view->types[$this->_getParam('type')];
		}
		if(  $this->_getParam('oho') <> null ) {
			$params[] = "price_1h_ap >=  {$this->_getParam('oho')}";
			$info["Цена за 1 час"] = "от " . $this->_getParam('oho') . " руб.";
		}
		if(  $this->_getParam('ohd') <> null ) {
			$params[] = "price_1h_ap <=  {$this->_getParam('ohd')}";
			$info["Цена за 1 час"].= " до " . $this->_getParam('ohd') . " руб.";
		}
		if( $this->_getParam('tho') <> null ) {
			$params[] = "price_2h_ap >=  {$this->_getParam('tho')}";
			$info["Цена за 2 час"] = "от " . $this->_getParam('tho') . " руб.";
		}
		if( $this->_getParam('thd') <> null ) {
			$params[] = "price_2h_ap <=  {$this->_getParam('thd')}";
			$info["Цена за 2 час"].= " до " . $this->_getParam('thd') . " руб.";
		}
		if( $this->_getParam('aho') <> null ) {
			$params[] = "price_n_ap >= {$this->_getParam('aho')}";
			$info["Цена за ночь"] = "от " . $this->_getParam('aho') . " руб.";
		}
		if( $this->_getParam('ahd') <> null ) {
			$params[] = "price_n_ap <=  {$this->_getParam('ahd')}";
			$info["Цена за ночь"].= " до " . $this->_getParam('ahd') . " руб.";
		}	
		if( $this->_getParam('ao') <> null ) {
			$params[] = "age >= {$this->_getParam('ao')}";
			$info["Возраст"] = "от " . $this->_getParam('ao');
		}
		if( $this->_getParam('ad') <> null ) {
			$params[] = "age >= {$this->_getParam('ad')}";
			$info["Возраст"].= " до " . $this->_getParam('ad');
		}	
		if( $this->_getParam('bo') <> null ) {
			$params[] = "breast >= {$this->_getParam('bo')}";
			$info["Грудь"] = "от " . $this->_getParam('bo');
		}
		if( $this->_getParam('bd') <> null ) {
			$params[] = "breast <= {$this->_getParam('bd')}";
			$info["Грудь"].= " до " . $this->_getParam('bd');
		}
		if( $this->_getParam('ho') <> null ) {
			$params[] = "height >= {$this->_getParam('ho')}";
			$info["Рост"] = "от " . $this->_getParam('ho');
		}
		if( $this->_getParam('hd') <> null ) {
			$params[] = "height <= {$this->_getParam('hd')}";
			$info["Рост"].= " до " . $this->_getParam('hd');
		}	
		if( $this->_getParam('wo') <> null ) {
			$params[] = "weight >= {$this->_getParam('wo')}";
			$info["Вес"] = "от " . $this->_getParam('wo');
		}
		if( $this->_getParam('wd') <> null ) {
			$params[] = "weight <= {$this->_getParam('wd')}";
			$info["Вес"].= " до " . $this->_getParam('wd');
		}
		if( $this->_getParam('place') <> 0 ) {
			$params[] = "place = {$this->_getParam('place')}";
			$info["Место"].= $this->view->places[$this->_getParam('place')];
		}
		if( $this->_getParam('rv') <> null ) {
			$params[] = "comments <> 0";
			$info["rv"] = "+ комментарии";
		}	
		if( $this->_getParam('v') <> null ) {
			$video = true;
			$info["v"] = "+ анкета с видео";
		}
		if( $this->_getParam('st') <> null ) {
			$params[] = "status > 50";
			$info["st"] = "+ фото проверено";
		}	
		if( $this->_getParam('district') <> 0){
			$params[] = "district = \"{$this->view->districts[$this->_getParam('district')]}\"";
			$info["Район"] = $this->view->districts[$this->_getParam('district')];
		}
		if( $this->_getParam('exotics') <> 0 ){
			$params[] = "exotics = {$this->_getParam('exotics')}";
			$info["Экзотика"] = $this->view->exotics[$this->_getParam('exotics')];
		}

		if($this->_hasParam('s') ) {
			foreach ( $this->_getParam('s') as $s ){
				if( strpos( $s,'_' ) ){
					$value=substr($s,0,32);
					$filters['s']=$value;
					list($column,$srv)=explode('_',$value);
					if( isset( $this->content->srv->{$column} ) ){
						$params[] = 'srv_' . $column . '&1<<'.intval($srv);
						$srv = $srv+1;
						$service = $this->content->srv->{$column}->$srv;
						$info["s"].= "+" . $service . " ";
					}
				}
			}
		}
			
		if ( $this->_hasParam('novelty') ) {
			switch ( $this->_getParam('novelty') ) {
				case '1' :
					$params[] = "timestamp >= '" . date( 'Y-m-d', time()) . "'";
					$info["Новизна"] = "За сегодня";
					break;
				case '2' :
					$params[] = "timestamp >= '" . date( 'Y-m-d', time() - 604800) . "'";
					$info["Новизна"] = "За неделю";
					break;
				case '3' :
					$current_month = mktime( 0,0,0,date('n'),date('j'),date('Y') );
					if ( date('n') == 1) {
						$last_month = mktime( 0,0,0,12,date('j'),date('Y') - 1 );
					}
					else
					{
						$last_month = mktime( 0,0,0,date('n') - 1, date('j'), date('Y') );
					}
					$params[] = "timestamp >= '" . date( 'Y-m-d', time() - ( $current_month - $last_month)) . "'";
					$info["Новизна"] = "За месяц";
					break;
			}
		}

		print_r($params);
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$ankets->set_items_per_page( ( $this->_hasParam( 'limit' ) ) ? (int)$this->_getParam( 'limit' ) : $this->settings['girls_per_page']);
		$this->view->ankets = $ankets->get_ankets_with_search( $page, $params, $video, $metro, $mtr_id, $per );
		$info['title'] ="Результат поиска анкет";
		$this->view->info = $info;
	}
}