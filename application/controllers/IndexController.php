<?php

class Delete_User_Messages extends Zend_Exception {}
class Delete_Postpone_NoId_Exception extends Zend_Exception {}
class Delete_Postpone_Exception extends Zend_Exception {}

class IndexController extends Zend_Controller_Action {
	const USR_ADM=0;
	const COM_ADM=1;
	const TECH_ADM=2;
	const MODER = 3;
	const USER = 4;

	protected $config,$content,$settings,$admin;
	protected $mBanners;

	public function init() {
		$auth=Zend_Auth::getInstance()->getIdentity();
		if ( $auth ) {
			$this->user_id = $auth->id;
			$this->view->user_id = $this->user_id;
			$this->view->auth = $auth->user_login;
			$this->view->flags = $auth->flags;
			($this->admin=$auth->flags & 1<<self::USR_ADM) ? true : false;
		} else {
			$this->admin = false;
			$this->view->flags = null;
		}

		$this->view->admin=$this->admin;
		$this->config=Zend_Registry::get('config');
		$this->content=Zend_Registry::get('content');
		$this->view->controller = $this->_request->getControllerName();
		$this->view->performer=$this->content->performer->toArray();
		$this->view->performer_out=$this->content->performer_out->toArray();
		$this->view->types=$this->content->types->toArray();
		$this->view->types_of_salon=$this->content->types_of_salon->toArray();
		$this->view->cities=$this->content->cities->toArray();
		$this->view->places=$this->content->places->toArray();
		$this->view->hair=$this->content->hair->toArray();
		$this->view->exotics=$this->content->exotics->toArray();
		$this->view->services=$this->content->srv->toArray();
		$this->view->services_of_salon=$this->content->srv_salon->toArray();
		$this->view->srv_short=$this->content->srv_short->toArray();
		$this->view->url_user_ph=$this->config->path->url_user_ph;
		$this->view->banners_path=$this->config->path->url_banners;
		$this->view->srv_short_list=true;
		$this->view->errors=array();

		$city = Zend_Registry::get('city');
		if ( $city == 2 ) {
			$this->city = 2;
			$this->view->city = 2;
			$this->view->city_text = 'Санкт-Петербург';
			$this->view->metro_list = $this->content->metro_spb->toArray();
			$this->view->district_list = $this->content->district_spb->toArray();
		} elseif ( $city == 1 ) {
			$this->city = 1;
			$this->view->city = 1;
			$this->view->city_text = 'Москва';
			$this->view->metro_list = $this->content->metro_msk->toArray();
			$this->view->district_list = $this->content->district_msk->toArray();
		}

		// Settings
		include_once 'Settings.php';
		$settings=new Settings;
		$this->settings=$settings->get();

		unset($settings,$auth);

		// Meta data
		$this->prepareMeta();

		// Articles
		$page=$this->_hasParam('ap')?intval($this->_getParam('ap')):1;
		include_once 'Articles.php';
		$articles=new Articles();
		$this->view->articles=$articles->get_list($page);
		include_once 'Banners.php';
		$this->mBanners = new Banners();
		$this->view->bannersLeft = $this->mBanners->get_list_left();
		$this->view->bannersRight= $this->mBanners->get_list_right();

		$this->view->sections = new Model_SectionsTest();

		include_once 'MenuItems.php';
		$mMenuItems = new MenuItems();
		$this->view->menu_items = $mMenuItems->get_all_items();
	}

	public function salonsAction(){
		$this->view->menu_sub = array('active' => 'default');
		if( $this->_hasParam( 'intim' ) ){
			$this->view->menu_sub['active'] = 'intim';
			$type = $this->_getParam('intim') ? 1 : false;
		}
		if( $this->_hasParam( 'mass' ) ){
			$this->view->menu_sub['active'] = 'mass';
			$type = $this->_getParam('mass') ? 2 : false;
		}
		if( $this->_hasParam( 'bdsm' ) ){
			$this->view->menu_sub['active'] = 'bdsm';
			$type = $this->_getParam('bdsm') ? 3 : false;
		}

		$filters = array();
		$params = array();
		$page = $this->_hasParam( 'p' ) ? intval( $this->_getParam( 'p' )) : 1;

		if($this->_getParam('m')){
			$mtr = $this->content->metro_spb->toArray();
			$mtr_id = (int)$this->_getParam('m')-1;
			$metro = $mtr[$mtr_id];
			$filters['m'] = $this->_getParam('m');
		}

		if ( $this->_getParam('tel') ) {
			$tel =  preg_replace('/\D/', '', $this->_getParam('tel'));

			if ( strlen($tel) > 10 ) {
				$tel = substr($tel, -10);
			}

			$pattern = '/(\d{1,3})(\d{0,7})/';
			if ( preg_match($pattern, $tel) ) {
				$replacement = '$1-%$2%';
				$tel1 = preg_replace($pattern, $replacement, $tel);
				$replacement = '%$1$2%';
				$tel2 = preg_replace($pattern, $replacement, $tel);
				$replacement = '$2-%$3%';
				$tel3 = preg_replace('/([7-8]{1})(\d{1,3})(\d{0,7})/', $replacement, $tel);
				$params[] = "phone like '{$tel1}' OR phone like '{$tel2}' OR phone like '{$tel3}'";
			}

			$filters['tel'] = $this->_getParam('tel');
		}

		include_once 'Salons.php';
		$salons = new Salons();
		$salons->set_items_per_page(($this->_hasParam('limit')) ? (int)$this->_getParam('limit') : $this->settings['girls_per_page']);

		// debug filters
		if ($this->admin) print_r($params);

		$salons = new Model_SalonsTest();
		$adapter = $salons->fetchSalonsList($page,$params,$metro, $type);

		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage($this->settings['girls_per_page']);
		$paginator->setCurrentPageNumber($page);
		$this->view->salons = $paginator;

		if (isset($this->view->info['title_meta'])) $this->view->meta['start_title']=$this->view->info['title_meta'];
		if (isset($this->view->info['keywords'])) $this->view->meta['start_keys']=$this->view->info['keywords'];
		if (isset($this->view->info['descriptions'])) $this->view->meta['start_desc']=$this->view->info['descriptions'];
		$this->view->filter=$filters;
		$this->view->services = $this->content->srv_salon->toArray();
	}

	public function indexAction() {
        $this->view->price_options = $this->content->price_options->toArray();
		$this->view->breast_options = $this->content->breast_options->toArray();
		$this->view->weight_options = $this->content->weight_options->toArray();
		$this->view->height_options = $this->content->height_options->toArray();
		$this->view->age_options = $this->content->age_options->toArray();
        $this->view->menu_sub = array('active' => 'default');
        if($this->_hasParam('st')) $this->view->menu_sub['active'] = 'st';
        if($this->_hasParam('v')) $this->view->menu_sub['active'] = 'v';
        if($this->_hasParam('rv')) $this->view->menu_sub['active'] = 'rv';
        $filters = array();	
		if($this->_getParam('st')){$this->_setParam('st','30-40');}
		$video = false; $metro = false;
		$params = array();

		//depends on subdomens
		$params['city'] = "city = " . $this->city;

		if ( $this->_getParam('r_performer') ) {
			$params['performer'] = 'performer = ' . $this->_getParam('r_performer');
		} else {
			$params['performer'] = 'performer ' . ($this->view->info['performer'] ? "=" . $this->view->info['performer'] : "IN (1,2,3,4)");
		}

		if($this->_getParam('m')){
			$mtr = $this->content->metro_spb->toArray();
			$mtr_id = (int)$this->_getParam('m');
       	    $filters['m'] = $this->_getParam('m');
	    }

	    if($this->_hasParam('s_metro')){
	    	$mtr_id = (int)$this->_getParam('s_metro');
	    }

	    if ( $this->_getParam('tel') ) {
	    	if ( $this->view->info['performer'] ) {
	    		$params['performer'] = 'performer = ' . $this->view->info['performer'];
	    	} else {
	    		unset( $params['performer'] );
	    	}

	    	$tel =  preg_replace('/\D/', '', $this->_getParam('tel'));

	    	if ( strlen($tel) > 10 ) {
	    		$tel = substr($tel, -10);
	    	}

	    	$pattern = '/(\d{1,3})(\d{0,7})/';
	    	if ( preg_match($pattern, $tel) ) {
	    		$replacement = '$1-%$2%';
	    		$tel1 = preg_replace($pattern, $replacement, $tel);
	    		$replacement = '%$1$2%';
	    		$tel2 = preg_replace($pattern, $replacement, $tel);
	    		$replacement = '$2-%$3%';
	    		$tel3 = preg_replace('/([7-8]{1})(\d{1,3})(\d{0,7})/', $replacement, $tel);
	    		$params[] = "phone like '{$tel1}' OR phone like '{$tel2}' OR phone like '{$tel3}'";
	    	}

	    	$filters['tel'] = $this->_getParam('tel');

	    	$this->view->search_by_phone = true;
	    }

       if ( $this->_hasParam('metro') ) {
           $metro_arr = $this->_getParam('metro');
                    
           if ( is_array($metro_arr) && count($metro_arr) > 0 ) {
               $i = 0;
               $metro_str = '('; 
               foreach ($metro_arr as $metro){
           	       $i++;
                   $metro_str .= (($i>1)?' OR ':'').'metro = "'.$metro.'"';
               }
               $params[] = $metro_str.')';
               unset($i, $metro_str, $metro);
           }
           unset($metro_arr);
        }

		if($this->_getParam('v')){$video = true;}
		if($this->_getParam('rv')){$params[] = "comments <> 0";}
		$page=$this->_hasParam('p')?intval($this->_getParam('p')):1;

		if ($this->_hasParam('place')) {
        	switch ($this->_getParam('place')) {
            	case 1:
                	$params[] = ("place = 1 OR place = 3");
                    break;
                case 2:
                    $params[] = ("place = 2 OR place = 3");
                    break;
                case 3:
                    $params[] = ("place = 3");
                    break;
                default:
                    break;
            }
            $this->_setParam('place', null);
        }
 
        $in_switch = array(
        	'video' => array('length(videolist)',2),
            //'verified' => array('photo_check',0),
            'comments'=>array('comments',0),
        );

        foreach ($in_switch as $param_key => $ank_tbl_field)
        	switch ($this->_getParam ($param_key)){
            	case '1':
                	$params[] = "$ank_tbl_field[0] > $ank_tbl_field[1]";// 1>
                    break;
                case '0':
                    $params[] = "$ank_tbl_field[0] > $ank_tbl_field[1]";
                    break;
                default:
        }

        $in_params=array(
        	'a' => 'age',
        	'h' => 'height',
        	'w'	=> 'weight',
        	'b'	=> 'breast',
        	'r'	=> 'price_1h_ap',
        	'r2'	=> 'price_2h_ap',
        	'rn'	=> 'price_n_ap',
        	'st' => 'status',
        	'place' => 'place',
        	'district' => 'district',
        	'exotics' => 'exotics'
        );

		foreach($in_params as $param=>$column){ // Add parameters to the base selection
			if($this->_getParam($param)){
				$value=substr($this->_getParam($param),0,32);
				$filters[$param]=$value;
				if(strpos($value,'-')){
					list($min,$max)=explode('-',$value);
					$min=intval($min);
					$max=intval($max);
					if($min){$params[]=$column.'>='.$min;}
					if($max){$params[]=$column.'<='.$max;}
				}
				else{$params[]=$column.'= \''.$value.'\'';}
			}
			else{$filters[$param]=false;}
		}

		// get services from sections
		foreach( $this->content->srv->toArray() as $srv=>$list ) {
        	foreach( $list as $key=>$null ) {
        		if ( $this->_getParam('srv'.'_'.$srv) & 1<<$key )
        			$params[] = 'srv_' . $srv . '&1<<' .$key;
        	}
        }

		if( $this->_hasParam('s') && count($this->_getParam('s')) >= 1 ){
			foreach ( $checkboxes = $this->_getParam('s') as $s ) {
				$value = $s;
				$filters['s'][$value] = true;
				list($column,$srv) = explode('_',$value);
				if( isset( $this->content->srv->{$column} ) ){
					$params[] = 'srv_'.$column.'&1<<'.intval($srv);
				}
			}
		}
		else{$filters['s']=false;}

		$this->view->filter=$filters;
		/* get ankets */
		$ankets = new Model_AnketsTest();

		$adapter = $ankets->fetchAnketsList($params,$video, null, $mtr_id);
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1);
		$paginator->setCurrentPageNumber($page);
		$this->view->ankets = $paginator;

		/* get latest ankets */
		$this->view->ankets_new = $ankets->fetchNewestAnkets($this->city);

		/* get top score */
		if ( !$route = Zend_Registry::get('route')  ) {
			$review = new Model_Review();
			$per = $params['performer'] ? $params['performer'] : false;
			$top_100 = $review->getTop100( $per, $params['city'] );
			$this->view->top_100 = $top_100;
		} else {
			$this->view->top_100 = false;
		}

		// debug filters
		if ($this->admin) print_r($params);
		if (isset($this->view->info['title_meta'])) $this->view->meta['start_title']=$this->view->info['title_meta'];
		if (isset($this->view->info['keywords'])) $this->view->meta['start_keys']=$this->view->info['keywords'];
		if (isset($this->view->info['descriptions'])) $this->view->meta['start_desc']=$this->view->info['descriptions'];
	}

	public function clearAction() {

	}

	public function commAddFormAction(){
		$this->_helper->layout->disableLayout();
		$this->captcha_add();
	}
	
	public function commAddAction() {
		if(!$this->_hasParam('n')) {
			$this->_redirect('/');die;
		}
		$ank_id = $this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($ank_id);
		if(!$info){$this->error('no_ank');return;}		
		if($this->_hasParam('text')){$text=$this->check_text(substr($this->_getParam('text'),0,4096));}
		else{$this->error('request_error');return;}
		if(empty($text)){$this->error('no_comm_text');}
		if(empty($this->view->errors)) {			
			include_once 'Comments.php';
			$comments = new Comments();
			$info = array(
				'owner_id'	=> $ank_id,
				'user_id'	=> $this->user_id,
				'text'		=> $text,
				'confirm'   => false				
			);
			$comments->add($info);
            $ankets->incr($ank_id);
		} else {
			$this->view->comm_info=array('text'=>$text);
		}
	}
	
	public function commDelReqAction(){
		if(!$this->admin){$this->_redirect('/');die;}
		$this->view->ank_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		$this->view->comm_id=$this->_hasParam('cn')?intval(substr($this->_getParam('cn'),0,32)):false;
	}
	
	public function commDelAction(){
		if(!$this->admin){$this->_redirect('/');die;}
		$ank_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		$comm_id=$this->_hasParam('cn')?intval(substr($this->_getParam('cn'),0,32)):false;
		include_once 'Comments.php';
		$comments=new Comments();
		$comments->del($comm_id);
                include_once 'Ankets.php';
                $ankets=new Ankets();
		if ($info=$ankets->get_anket($ank_id)){
                    $ankets->decr($ank_id);
                }
		$this->_redirect('/index/show-ank/n/'.$ank_id);die;
	}
	
	public function articleAction(){
		if(!$this->_hasParam('n')){$this->_redirect('/');die;}
		$id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		if(!$id){$this->_redirect('/');die;}
		include_once 'Articles.php';
		$articles=new Articles();
		$info=$articles->get_art($id);
		if(!$info){$this->view->error=$this->content->messages->no_article;return;}
		$this->view->info=array(
			'title'	=> nl2br(stripslashes($info['title'])),
                        'title_meta'	=> stripslashes($info['title_meta']),
                        'keywords'	=> stripslashes($info['keywords']),
                        'descriptions'	=> stripslashes($info['descriptions']),
			'text'	=> nl2br(stripslashes($info['text']))
		);
                $this->view->meta['start_title']=$this->view->info['title_meta'];
		$this->view->meta['start_keys']=$this->view->info['keywords'];
		$this->view->meta['start_desc']=$this->view->info['descriptions'];
	}
	
    public function sectionAction()
    {   
    	$id = (int)substr($this->_getParam('id'),0,8);
        if (!$id) $id = 1;
        include_once 'Sections.php';
        $mSections=new Sections();
        $this->view->info = $info = $mSections->get($id);

        $in_params=array(
            'c'	=> 'city',
            'a'     => 'age',
            'h'     => 'height',
            'w'	=> 'weight',
            'b'	=> 'breast',
            'r'	=> 'price_1h_ap',
            'r2'	=> 'price_2h_ap',
            'rn'	=> 'price_n_ap',
            'v' => 'with_videos', 
            'comments'=>'with_comments',
            'st' => 'verified',
            'place' => 'place',
            's_metro' => 'metro',
        	'district' => 'district',
        	'exotics' => 'exotics',
        	'srv_main'  => 'srv_main',
        	'srv_add'   => 'srv_add',
        	'srv_strip' => 'srv_strip',
        	'srv_extr'  => 'srv_extr',
        	'srv_bdsm'  => 'srv_bdsm',
        	'srv_mass'  => 'srv_mass',
		);

        foreach ($in_params as $key => $val){
        	if (strpos($val, '[]') != 0){
            	$val = rtrim($val,'[]');
                $info[$val] = unserialize($info[$val]);
            }
            if ($info[$val] != 0 && $info[$val] != -1){
            	$this->_setParam($key, $info[$val]);
            }
        }

        $this->_helper->viewRenderer->setScriptAction('index');
        $this->indexAction();
    }

    public function menuitemsAction() {
    	$uri = substr($this->_getParam('uri'),0,256);

    	include_once 'MenuItem.php';
    	$mMenuItem=new MenuItems();
    	$this->view->info = $info = $mMenuItem->get_by_uri($uri); 

    	//add performer and type ot view
    	$this->view->r_performer = $info['performer'];
    	$this->view->r_type = $info['type'];
    	
    	if( $info['type'] != 2){
    		$this->_helper->viewRenderer->setScriptAction('index');
    		$this->indexAction();
    	} else {
    		$this->_helper->viewRenderer->setScriptAction('salons');
    		$this->salonsAction();
    	}
    }

	protected function check_text($text){
		return preg_replace('/[^ёЁ\d\w\s\.\-а-я+,!?%]/ui','',$text);
	}
	
	protected function error($error){
		$this->view->errors[]=$this->content->messages->{$error};
	}
	
	protected function captcha_add(){
		$captcha=new Zend_Captcha_Image();
		$captcha
			->setImgDir($this->config->path->captcha)
			->setFont($this->config->font->captcha)
			->setWordlen(4)
			->setFontSize(22)
			->setDotNoiseLevel(10)
			->setLineNoiseLevel(2)
			->generate();
		$this->view->captcha=$captcha->getId();
		return $this->view->captcha;
	}
	
	protected function captcha_check(){
		if( $this->_hasParam('captcha') ) {
			$cap=$this->check_text(substr($this->_getParam('captcha'),0,16));
		} else {
			$this->error('request_error');return false;
		}
		if($this->_hasParam('id')){$cap_id=$this->check_text(substr($this->_getParam('id'),0,32));}
		else{$this->error('request_error');return false;}
		$img_path=$this->config->path->captcha.'/'.$cap_id.'.png';
		if(is_file($img_path)){unlink($img_path);}
		$cap_sess = new Zend_Session_Namespace('Zend_Form_Captcha_'.$cap_id);
		$cap_int = $cap_sess->getIterator();
		if ( !isset($cap_int['word']) || $cap_int['word'] != $cap ) {
			$this->error('captcha');
			return false;
		}
		return true;
	}	
	
	public function userCommentsAction() {
		/*
		 *  Need to implement rights check
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$comments = new Model_CommentsTest();

		if ( $this->getRequest()->isPost() ) {
			foreach ( $_POST as $key => $value ) {
				if ( preg_match('/del_/', $key) ) {
					list($prefix, $id) = explode('_', $key);
					$where[] = $id;
				}
			}

			$result = $comments->delete('id IN (' . implode(',', $where) . ')');

			if ( !$result ) {
				throw new Delete_User_Messages();
			}
		}

		$adapter = $comments->getUserComments( $this->user_id );
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$page = $this->_request->getParam('p', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}

	public function userReviewsAction() {
		/*
		 *  Need to implement rights check
		*/
		
		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$reviews = new Model_Review();

		$adapter = $reviews->getUserReviews( $this->user_id );
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$page = $this->_request->getParam('p', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}

	public function postponeAction() {
		/*
		 * Need to implement rights check
		 *
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$postpone = new Model_Postpone();
		$ankets = new Model_AnketsTest();

		$ids = $postpone->getUserAnkPostponeIds( $this->user_id );
		if ( $ids ) {
			$adapter = $ankets->fetchAnketsByIds( $ids );
			$paginator = new Zend_Paginator($adapter);
			$paginator->setItemCountPerPage( $this->settings['girls_per_page'] * 2 );
			$page = $this->_request->getParam('p', 1);
			$paginator->setCurrentPageNumber($page);
			$this->view->paginator = $paginator;
		} else {
			$ankets = null;
		}
	}

	public function delPostponeAction() {
		/*
		 * Need to implement rights check
		 *
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$id = $this->_getParam('id');
			
		if ( !$id ) {
			throw new Delete_Postpone_NoId_Exception();
		}
			
		$postpone = new Model_Postpone();
		$result = $postpone->delete("user_id = " . $this->user_id . " AND ank_id = " . $id);

		if ( !$result ) {
			throw new Delete_Postpone_Exception();
		} else {
			$this->_redirect('/index/postpone');
		}
	}

	public function profileAction () {
		/*
		 *Need to implement rights check
		 *
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$type = $this->_getParam('type');

		switch ($type) {
			case 'pass' : 
				$frm = new Form_EditPassForm();
				break;
			case 'mess' :
				$frm = new Form_EditUserMessForm();
				break;
			default :
				$frm = new Form_EditPassForm();
				break;
		}

		include_once 'Users.php';
		$users = new Users();
		$email = $users->get_email($this->user_id);
		$login = $this->user_login;

		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {
				if ( $frm instanceof Form_EditPassForm ) {
					$oldpass = $frm->getValue('old_pass');
					$pass = $frm->getValue('new_pass');
					if ( $users->user_check_password($this->user_id,md5($oldpass) ) ) {
						$users->user_change_password($this->user_id,md5($pass));
						$this->view->message = Form_EditPassForm::SUCCESS;
						$this->view->success = true;
					} else {
						$this->view->message = Form_EditPassForm::FAILED;
						$this->view->success = false;
					}

				} elseif ( $frm instanceof Form_EditUserMessForm ) {
					$user_config = new Model_UsersConfig();
					$result = $user_config->addUserCabMessagesConfig(
						$frm->getValue('comments'),
						$frm->getValue('messages'),
						$frm->getValue('news'),
						$this->user_id
					);

					if ( $result ) {
						$this->view->message = Form_EditUserMessForm::SUCCESS;
						$this->view->success = true;
					} else {
						$this->view->message = Form_EditUserMessForm::FAILED;
						$this->view->success = false;
					}
				}
			}
		} elseif ( $frm instanceof Form_EditUserMessForm) {
			$user_config = new Model_UsersConfig();
			$data = $user_config->getUserMessagesConfig($this->user_id);
			$frm->populate($data);
		}

		$this->view->login = $login;
		$this->view->email = $email;
		$this->view->form = $frm;
	}

	public function userMessagesAction() {
		/*
		 * Need to implement rights check
		 *
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$frm = new Form_MessagesForm();
		$this->view->form = $frm;

		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {
				$messages = new Model_Messages();
				foreach ( $_POST as $key => $value ) {
					if ( preg_match('/del_/', $key) ) {
						list($prefix, $id) = explode('_', $key);
						$where[] = $id;
					}
				}

				$result = $messages->delete('id IN ('. implode(',',$where) .')');
			}
		}

		$messages = new Model_Messages();

		if ( $this->user_admin) {
			$adapter = $messages->fetchPaginatorAdapter(Form_NewMessageForm::TO_ADMIN, $this->user_id);
		} elseif ($this->user_moder) {
			$adapter = $messages->fetchPaginatorAdapter(Form_NewMessageForm::TO_MODER, $this->user_id);
		} else {
			$adapter = $messages->fetchPaginatorAdapter($this->user_id);
		}

		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(10);
		$page = $this->_request->getParam('p', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}

	function deleteMessageAction()  {
		/*
		 * Need to implement rights check
		*
		*/
		
		if ( !$this->user_id ) {
			$this->_redirect("/");
		}
		
		/*
		 *
		*
		*/
		
		$id = $this->_getParam('id');
		$messages = new Model_Messages();
		$result = $messages->delete('id = ' . $id );
		$this->_redirect('/index/user-messages');
	}
	
	function showMessageAction() {
		/*
		 * Need to implement rights check
		 *
	     */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$id = $this->_getParam('id');
		$frm = new Form_ShowMessagesForm();
		$messages = new Model_Messages();
		$message = $messages->find($id)->current();

		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {
				$values = $frm->getValues();
				$file = urlencode($values['upload']);

				$element = $frm->getElement('upload');
				$element->addFilter('Rename',array('target' => 'download/' . $file ));

				$send_to = $this->sendTo( $this->getAdminType( $this->user_id ), $this->user_id );

				$messages = new Model_Messages();
				$result = $messages->addMessage(
					$send_to,
					"Re: " . $message['subject'],
					trim($frm->getValue('answer')),
					$this->user_id,
					$file
				);

				if ( $element->receive() && $result ) {
					$this->view->message = Form_NewMessageForm::SUCCESS;
					$this->view->color = Form_NewMessageForm::SUCCESS_COLOR;
				} else {
					$this->view->message = Form_NewMessageForm::FAILED;
					$this->view->color = Form_NewMessageForm::FAILED_COLOR;
				}
			}
		}

		if ( $this->user_admin  ) {
			$users = new Model_UsersTest();
			$this->view->from = "От пользователя: " . $users->getLogin( $message['user_id'] );
		}

		$this->view->subject = $message['subject'];
		$this->view->body = $message['body'];
		$this->view->timestamp = $message['timestamp'];

		if ( $message['file'] ) {
			$this->view->file = $message['file'];
		}

		if ( $message['user_id'] == $this->user_id ) {
			$this->view->form = $frm;
		} else {
			$this->view->form = $frm;
			$messages->markViewed($id);
		}
	}

	function getAdminType( $user_id ) {
	
		$users = new Model_UsersTest();
		$flags = $users->getFlags( $user_id );
	
		$user_admin=( $flags & 1<<self::USR_ADM ) ? true : false;
		if ( $user_admin ) {
			return self::USR_ADM;
		}
		$user_com = ( $flags & 1<<self::COM_ADM ) ? true : false;
		if ( $user_com ) {
			return self::COM_ADM;
		}
		$user_tech = ( $flags & 1<<self::TECH_ADM ) ? true : false;
		if ( $user_tech ) {
			return self::TECH_ADM;
		}
		$user_moder = ( $flags & 1<<self::MODER ) ? true : false;
		if ( $user_moder ) {
			return self::MODER;
		}
	
		return self::USER;
	}
	
	function sendTo( $type, $user_id = null ) {
	
		switch ( $type ) {
				
			case self::USR_ADM :
				return Form_NewMessageForm::TO_ADMIN;
				break;
	
			case self::MODER :
				return Form_NewMessageForm::TO_MODER;
				break;
	
			case self::COM_ADM :
				return $user_id;
				break;
	
			case self::TECH_ADM :
				return $user_id;
				break;
	
			case self::USER :
				return $user_id;
				break;
		}
	}

	public function userConnectAction() {
		/*
		 * Need to implement rights check
		 *
		 */

		if ( !$this->user_id ) {
			$this->_redirect("/");
		}

		$frm = new Form_NewMessageForm();
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {

				$values = $frm->getValues();
				$file = urlencode($values['upload']);

				$element = $frm->getElement('upload');
				$element->addFilter('Rename',array('target' => 'user_messages_photos/' . $file ));

				$messages = new Model_Messages();
				$result = $messages->addMessage(
					$frm->getValue('send_to'),
					$frm->getValue('subject'),
					$frm->getValue('body'),
					$this->user_id,
					$file
				);

				if ( $result ) {
					// redirect to my messages 
					$this->_redirect('/index/user-messages');
					//$this->view->message = "Ваше сообщение отправлено " . $this->view->auth;
					//$this->view->color = Form_NewMessageForm::SUCCESS_COLOR;
				} else {
					$this->view->message = "Ваше сообщение не отправлено " . $this->view->auth;
					$this->view->success = false;
				}
			}
		}

		$this->view->form = $frm;
	}

	protected function prepareMeta() {

		include_once 'Meta.php';
		$meta=new Meta;
		$this->view->meta=array();
		$meta_data=$meta->get();
		foreach($meta_data as $key=>$value){ // Parsing
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

		if ( preg_match('/^\/ankety(.*)/', $_SERVER['REQUEST_URI']) ) {
			$this->view->meta['start_title'] = 'Новые анкеты - Vbordele.com';
			$this->view->meta['start_keys'] = "";
			$this->view->meta['start_desc'] = "";
		}

	}
}
