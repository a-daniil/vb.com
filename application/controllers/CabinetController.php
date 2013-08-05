<?php

class CabinetController extends Zend_Controller_Action {
	
	protected $content,$config,$user_id,$user_admin,$admin_flags;
	
	/* constants for user types */
	const USR_ADM=0;
	const COM_ADM=1;
	const TECH_ADM=2;
	const MODER = 3;
	const USER = 4;
	
	/* constants for ankets's type */
	const GIRL = 1;
	const LESB = 2;
	const MASS = 3;
	const BDSM = 4;
	const MAN = 6;
	const TRANS = 7;
	const PAIR = 5;	

	public function init() { 		
    	$this->_helper->layout->setLayout('cabinet');
        $auth=Zend_Auth::getInstance()->getIdentity();
        if(!$auth){$this->_redirect('/');die;}
        $this->user_id=$auth->id;
        $this->user_login=$auth->user_login;
        $this->user_admin=( $auth->flags & 1<<self::USR_ADM ) ? true : false;
        $this->user_tech = ( $auth->flags & 1<<self::TECH_ADM ) ? true : false;
        $this->user_moder = ( $auth->flags & 1<<self::MODER ) ? true : false;
       	
        if ( $auth->flags & 1<<self::COM_ADM || $auth->flags == 0 ) {
        	$this->user_com = true;
        }
        
        $this->view->auth=$auth->user_login;
        $this->view->admin=$this->user_admin;
        $this->view->com_admin = $this->user_com;
        $this->view->tech_admin = $this->user_tech;
        $this->view->user_moder = $this->user_moder;
        $this->config = Zend_Registry::get('config');
        $this->content = Zend_Registry::get('content');
        $this->view->user_id = $this->user_id;
        $this->view->controller = $this->_request->getControllerName();
        $this->view->cities=$this->content->cities->toArray();
        $this->view->photos_path=$this->config->path->url_user_ph;
        $this->view->videos_path=$this->config->path->url_user_vi;
        $this->view->banners_path=$this->config->path->url_banners;
        $this->view->errors=array();      
        $this->admin_flags=new Zend_Session_Namespace('admin');
        if(!isset($this->admin_flags->all_anks)){$this->admin_flags->all_anks=false;}
        $this->view->all_anks=$this->admin_flags->all_anks;
        $this->view->yesNoNullOptions = array(
        	'-1' => 'UnSelect',
            // '0'  => 'No',
            '1'  => 'Yes'
        ); 
        
        include_once 'Settings.php';
        $settings=new Settings;
        $this->settings=$settings->get();
	}	
	
	public function indexAction() {
		$page      = $this->getParam('p') ? intval($this->getParam('p')) : 1;
        $performer = $this->getParam('performer') ? intval($this->getParam('performer')) : false;
        $filter    = $this->getParam('filter') ? $this->getParam('filter') : false;
        
        switch ($filter) {
        	case 'active' :
        		$filter = "active = 1 AND status >= 30";
        		break;
        	case 'paid' :
        		$filter = "status = 40 AND active = 1";
        		break;
        	case 'free' :
        		$filter = "status = 30 AND active = 1";
        		break;
        	case 'not_active' :
        		$filter = "active = 0 OR status < 30";
        		break;
        	case 'all' : 
        		$filter = false;
        		break;        	
        }
        
		include_once 'Ankets.php';
		$ankets=new Ankets();		
		
		if($this->user_admin && $this->admin_flags->all_anks){$user_id=false;}
		else{
			$user_id = $this->user_id;			
			$this->view->balance = $balance = $this->calculateBalance();
			$this->view->spend = $spend = $this->calculateSpending();
			$this->view->forecast = $this->calculateForecast($balance, $spend);
		}
		$this->view->user_id = $user_id;		
		
		if ( $this->user_moder ) {
			$this->view->ankets = $ankets->get_ankets_list_cab($page, false, true, $performer, $filter);
		} else {
			$this->view->ankets = $ankets->get_ankets_list_cab($page, $user_id, false, $performer, $filter);				
		}
		
		$this->view->all    = $ankets->count_all_ankets($user_id);
		$this->view->active = $ankets->count_active_ankets($user_id);
		$this->view->paid   = $ankets->count_paid_ankets($user_id);
		include_once 'CountersAnkets.php';	
		$this->view->per_page = $this->settings['girls_per_page'];
	}

	function profileAction () {			
		$type = $this->getParam('type');
		
		switch ($type) {
			case 'pass' : 
				$frm = new Form_EditPassForm();
				break;
			case 'mess' :
				$frm = new Form_EditMessForm();				
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
						$this->view->color = Form_EditPassForm::SUCCESS_COLOR;				
					} else {
						$this->view->message = Form_EditPassForm::FAILED;
						$this->view->color = Form_EditPassForm::FAILED_COLOR;
					}
					
				} elseif ( $frm instanceof Form_EditMessForm ) {
					$user_config = new Model_UsersConfig();					
					$result = $user_config->addUserMessagesConfig(
						$frm->getValue('balance'),
						$frm->getValue('days_info'),
						$frm->getValue('comments'),
						$frm->getValue('moderation'),
						$frm->getValue('messages'),
						$frm->getValue('news'),
						$this->user_id
					);
					
					if ( $result ) {
						$this->view->message = Form_EditMessForm::SUCCESS;
						$this->view->color = Form_EditMessForm::SUCCESS_COLOR;
					} else {
						$this->view->message = Form_EditMessForm::FAILED;
						$this->view->color = Form_EditMessForm::FAILED_COLOR;
					}
				}
			}
		} elseif ( $frm instanceof Form_EditMessForm) {
			$user_config = new Model_UsersConfig();
			$data = $user_config->getUserMessagesConfig($this->user_id);
			$frm->populate($data);	
		}
		
		$this->view->login = $login;
		$this->view->email = $email;
		$this->view->form = $frm;		
	}

	function changeUserPassAction () {
		$id = $this->getParam('id');
		$frm = new Form_ChangeUserPassForm();
		
		include_once 'Users.php';
		$users = new Users();
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {
				$admin_password = $frm->getValue('admin_password');
				$pass = $frm->getValue('new_pass');
				if ( $users->user_check_password($this->user_id,md5($admin_password) ) ) {
					$users->user_change_password($id, md5($pass));
					$this->view->message = Form_ChangeUserPassForm::SUCCESS;
					$this->view->color = Form_ChangeUserPassForm::SUCCESS_COLOR;
				} else {
					$this->view->message = Form_ChangeUserPassForm::FAILED;
					$this->view->color = Form_ChangeUserPassForm::FAILED_COLOR;
				}
			}
		}

		$this->view->login = $users->getLogin($id);
		$this->view->form = $frm;
	}

	function blockUserAction () {
		$id = $this->getParam('id');
		$filter = $this->getParam('filter');

		$users = new Model_UsersTest();	
		$where = "id = " . $id;		

		$users->update(array('status' => 0), $where);
		$this->_redirect('/cabinet/users?filter=' . $filter );
	}

	function activateUserAction () {
		$id = $this->getParam('id');
		$filter = $this->getParam('filter');

		$users = new Model_UsersTest();
		$where = "id = " . $id;

		$users->update(array('status' => 1), $where);
		$this->_redirect('/cabinet/users?filter=');
	}

	function changeUserTypeAction () {
		$id = $this->getParam('id');
		$frm = new Form_ChangeUserTypeForm();

		include_once 'Users.php';
		$users = new Users();
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {				
				$admin_password = $frm->getValue('admin_password');
				$flag = $frm->getValue('flag');
				if ( $users->user_check_password($this->user_id,md5($admin_password) ) && $users->user_check_for_retype($id) ) {
					//$users->user_change_password($id, md5($pass));
					$users->upd_user_info($id, array('flags' => $flag));
					$this->view->message = Form_ChangeUserTypeForm::SUCCESS;
					$this->view->color = Form_ChangeUserTypeForm::SUCCESS_COLOR;
				} else {
					$this->view->message = Form_ChangeUserTypeForm::FAILED;
					$this->view->color = Form_ChangeUserTypeForm::FAILED_COLOR;
				}
			}
		}
		
		$this->view->login = $users->getLogin($id);
		$this->view->form = $frm;
	}
	
	function connectAction () {			
		$frm = new Form_NewMessageForm( $this->getParam( 'uid' ), $this->getParam('login') );
		
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
					$this->view->message = Form_NewMessageForm::SUCCESS;
					$this->view->color = Form_NewMessageForm::SUCCESS_COLOR;
				} else {
					$this->view->message = Form_NewMessageForm::FAILED;
					$this->view->color = Form_NewMessageForm::FAILED_COLOR;
				}
			}
		}
		
		$this->view->form = $frm;		
	}
	
	function messagesAction () {
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
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;	
		$this->view->my_id = $this->user_id;	
	}
	
	function deleteMessageAction()  {
		$id = $this->getParam('id');
		$messages = new Model_Messages();		
		$result = $messages->delete('id = ' . $id );
		$this->_redirect('/cabinet/messages');		
	}
	
	function showMessageAction() {
		$id = $this->getParam('id');		
		$frm = new Form_ShowMessagesForm();
		$messages = new Model_Messages();		
		$message = $messages->find($id)->current();		
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frm->isValid( $_POST ) ) {
				$values = $frm->getValues();
				$file = urlencode($values['upload']);
				
				$element = $frm->getElement('upload'); 				        		
        		$element->addFilter('Rename',array('target' => 'user_messages_photos/' . $file ));       		
        		
        		$send_to = $this->sendTo( $this->getAdminType( $message['user_id'] ), $message['user_id'] );
        		
        		$messages = new Model_Messages();
        		$result = $messages->addMessage(
        			$send_to,
        			"Re: " . $message['subject'],
        			$frm->getValue('answer'),
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
		
		if ( $message['file'] ) {
			$this->view->file = $message['file'];
		}		
		
		$this->view->timestamp = $message['timestamp'];
		
		if ( $message['user_id'] == $this->user_id ) {
			$this->view->form = null;
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
	
	function statisticAction() {
		include_once 'CountersAnkets.php';		
		$ankets=new Model_AnketsTest();		
		$adapter = $ankets->fetchPaginatorAdapter($this->user_id);
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}
	
	function statisticMetroAction() {
		$stats_metro = new Model_StatsMetro();
		$adapter = $stats_metro->fetchPaginatorAdapter();
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;		
	}
	
	function statisticFilterAction() {
		$stats_common = new Model_StatsCommon();
		$this->view->video = $stats_common->getCommon('video');
		$this->view->verified = $stats_common->getCommon('verified');
		$this->view->man = $stats_common->getCommon('man');
	}
	
	function statisticCommerceAction() {
		$ankets = new Model_AnketsTest();
		$this->view->ankets_count = $ankets->getAnketsCount();	
		
		$salons = new Model_SalonsTest();
		$this->view->salons = $salons->getSalonsCount();
		
		$this->view->girl_priority = $ankets->getPriority( 1 );
		$this->view->lesb_priority = $ankets->getPriority( 2 );
		$this->view->mass_priority = $ankets->getPriority( 3 );
		$this->view->bdsm_priority = $ankets->getPriority( 4 );
		$this->view->para_priority = $ankets->getPriority( 5 );
		$this->view->man_priority = $ankets->getPriority( 6 );
		$this->view->trans_priority = $ankets->getPriority( 7 );
		
		$this->view->salons_priority = $salons->getPriority();
	}
	
	public function salonsAction() {
		$page   = $this->_hasParam('p') ? intval($this->_getParam('p')):1;		
		$filter = $this->getParam('filter') ? $this->getParam('filter') : false;
		
		switch ($filter) {
			case 'active' :
				$filter = "active = 1 AND status >= 30";
				break;
			case 'paid' :
				$filter = "status = 40 AND active = 1";
				break;
			case 'free' :
				$filter = "status = 30 AND active = 1";
				break;
			case 'not_active' :
				$filter = "active = 0 OR status < 30";
				break;
			case 'all' :
				$filter = false;
				break;
		}
		
		include_once 'Salons.php';
		$salons = new Salons();
		if( $this->user_admin && $this->admin_flags->all_anks){
			$user_id = false;
		}
		else {
			$user_id=$this->user_id;
			$this->view->balance = $balance = $this->calculateBalance();
			$this->view->spend = $spend = $this->calculateSpending();
			$this->view->forecast = $this->calculateForecast($balance, $spend);
		}		
		
		$this->view->user_id=$user_id;
		$this->view->salons=$salons->get_salons_list_cab($page,$user_id, false, $filter);
		$this->view->all    = $salons->count_all_ankets($user_id);
		$this->view->active = $salons->count_active_salons($user_id);
		$this->view->paid   = $salons->count_paid_salons($user_id);		
		include_once 'CountersAnkets.php';
		$this->view->per_page = $this->settings['girls_per_page'];
	}
	
	public function addAnkToSalonAction(){
		if( $this->_hasParam('n')){
			$this->view->salon = intval( $this->_getParam('n') );
		}		
		/* redirect to salons ingo pages */
		if ( $this->_hasParam('to_photo') ) { $redirect = '/cabinet/edit-photo-salon/n/' . $this->view->salon; }
		if ( $this->_hasParam('to_video') ) { $redirect = '/cabinet/edit-photo-video/n/' . $this->view->salon; }
		if ( $this->_hasParam('to_salon_edit') ) { $redirect = '/cabinet/edit-salon-form/id/' . $this->view->salon; }
		if ( $redirect ) {
			$this->_redirect($redirect);
		}
		
		
		$page = $this->_hasParam('p') ? intval($this->_getParam('p')):1;		
		include_once 'Salons.php';
		$salons=new Salons();
		$this->view->info=$salons->get_salon( $this->_getParam( 'n' ) );
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		if($this->user_admin && $this->admin_flags->all_anks){
			$user_id=false;
		} else {
			$user_id=$this->user_id;
		}
		
		$this->view->user_id=$user_id;
		$ankets->set_items_per_page(12);
		$this->view->ankets=$ankets->get_ankets_list_cab($page,$user_id);
		if( $this->view->ankets->getTotalItemCount() == 0 ){
			$this->_redirect('/cabinet/salons');
		}
		include_once 'CountersAnkets.php';
		$this->view->counters=new CountersAnkets();
	}
	
	public function addAnkSalonAction(){
		$n = intval( $this->_getParam('n') );
		$id = intval( $this->_getParam('a') );
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$ankets->upd_anket($id,array('type'=>$n));
		$this->_redirect('/cabinet/add-ank-to-salon/n/'.$n);
		die;
	}
	
	public function delAnkSalonAction(){
		$n = intval( $this->_getParam('n') );
		$id = intval( $this->_getParam('a') );
	
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$ankets->upd_anket($id,array('type'=>0));
		$this->_redirect('/cabinet/add-ank-to-salon/n/'.$n);
		die;
	}
	
	public function ankModerationAction(){
		$this->hasRights( array( 'user_admin', 'user_moder') );
		$user_id = $this->_hasParam('uid') ? intval($this->_getParam('uid')) : false;
		$page = $this->_hasParam('p')?intval($this->_getParam('p')):1;
		$this->_helper->viewRenderer->setScriptAction('index');
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$this->view->ankets=$ankets->get_ankets_list_cab($page,$user_id,true);
		include_once 'CountersAnkets.php';
		$this->view->counters=new CountersAnkets();		
		$this->view->user_id=false;
		$this->view->ank_moderation=true;
	}
	
	public function salonsModerationAction(){		
		$this->hasRights( array( 'user_admin', 'user_moder') );
		$user_id = $this->_hasParam('uid') ? intval($this->_getParam('uid')) : false;
		$page = $this->_hasParam('p')?intval($this->_getParam('p')):1;
		$this->_helper->viewRenderer->setScriptAction('salons');
		include_once 'Salons.php';
		$salons = new Salons();
		$this->view->salons = $salons->get_salons_list_cab($page,$user_id,true);
		include_once 'CountersAnkets.php';
		$this->view->counters=new CountersAnkets();		
		$this->view->user_id=false;
		$this->view->ank_moderation=true;
	}
	
	public function ankStatusSetAction() {
		$this->hasRights( array( 'user_admin', 'user_moder') );
		if(!$this->_hasParam('n') || !$this->_hasParam('s')){$this->error('request_error');return;}
		$status = $this->_hasParam('s') ? intval($this->_getParam('s')) : 10;
		$comm = $this->_hasParam('comm') ? $this->_getParam('comm') : false;
		
		$ankets = new Model_AnketsTest();
		$info = $ankets->getById( $this->_getParam('n') );	
		if(!$info){$this->error('no_ank');return;}

		$data = array(
			'photo_start' => date('Y-m-d H:i:s', time()),
			'photo_finish' =>  date('Y-m-d H:i:s', time() + 7776000),
			'status' => Ps_Statuses_ControlStatuses::getStatus($status, 'ankStatusSet', array('priority' => $info['priority']))
		);	
		$res = $ankets->update($data, "id = " . $info['id']);		
	
		if ( $res ) {
			/* send message into inner mail */
			Ps_SendMessage_SM::sendMessage($status, $comm, $info, $this->user_id);
			
			if ( $comm && $status == 11 ) {
				$comm_by_ankets_moderation = new Model_AnketsModerationComm();
				$comm_by_ankets_moderation->addAnketaModerationCommm( $comm, $info['id'] );
			}
		}
		$this->_redirect('/cabinet/ank-moderation' . $this->addGetParamsToUri());
		die;
	}	
	
	public function salonStatusSetAction() {
		$this->hasRights( array( 'user_admin', 'user_moder') );	
		
		$n       = $this->getParam('n');
		$status  = $this->getParam('s') ? $this->getParam('s') : 10;
		$comm    = $this->getParam('comm') ? $this->getParam('comm') : false;
		
		$salons = new Model_SalonsTest();
		$info = $salons->getById( $this->_getParam('n') );
		if(!$info){
			$this->error('no_ank');return;
		}
		
		$data = array(			
			'status' => Ps_Statuses_ControlStatuses::getStatus($status, 'salonStatusSet', array('priority' => $info['priority']))
		);
		$res = $salons->update($data, "id = " . $info['id']);
		
		if ( $res ) {
			/* send message into inner mail */
			Ps_SendMessage_SMA::sendMessage($status, $comm, $info, $this->user_id);
		}
		$this->_redirect('/cabinet/salons-moderation' . $this->addGetParamsToUri());
		die;
	}
	
	public function commentModerationAction() {
		if( !$this->user_moder && !$this->user_admin ) {
			$this->_redirect('/cabinet');
			die;
		}	
		
		$uid = $this->getParam('uid');
		$users = new Model_UsersTest();
		$this->view->user_login = $users->getLogin($uid);		
		
		$comments = new Model_CommentsTest();
		$adapter = $comments->fetchPaginatorAdapter( $uid );
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;		
	}
	
	function confirmCommentAction () {
		$id = $this->getParam('id');
		$uid = $this->getParam('uid');
	
		$users = new Model_CommentsTest();
		$where = "id = " . $id;
	
		$res = $users->update(array('confirm' => true), $where);		
		/* send email */
		if ( $res && Ps_Notifier_Email::isSend( $uid, Ps_Notifier_Email::COMMENT ) ) {
			$notifier = new Ps_Notifier_Email( $uid, Ps_Notifier_Email::COMMENT );
			$notifier->send($id);
		}
		
		$this->_redirect('/cabinet/comment-moderation?uid=' . $uid);
	}
	
	function addAnkFormAction () {		
		$performer = $this->getParam('performer');	
		$salons = $this->getUsersSalons();
		
		switch ( $performer ) {
			case self::GIRL :
				$frmAddAnket = new Form_AddGirlAnkForm(self::GIRL, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Девушки';
				break;
			case self::LESB :
				$frmAddAnket = new Form_AddLesbAnkForm(self::LESB, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Подружки';
				break;
			case self::MASS :
				$frmAddAnket = new Form_AddMassAnkForm(self::MASS, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Массажистки';
				break;
			case self::BDSM :
				$frmAddAnket = new Form_AddBdsmAnkForm(self::BDSM, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'БДСМ';
				break;
			case self::MAN :
				$frmAddAnket = new Form_AddManAnkForm(self::MAN, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Мужчины';
				break;
			case self::TRANS :
				$frmAddAnket = new Form_AddTransAnkForm(self::TRANS, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Транса';
				break;
			case self::PAIR :
				$frmAddAnket = new Form_AddPairAnkForm(self::PAIR, $this->content, array('types' => $salons, 'new' => true));
				$type_label = 'Пары';								
				break;
		}
		
		$frmAddAnket->setMethod("post");
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		if ( $this->getRequest()->isPost() ) {
			if ( $frmAddAnket->isValid( $_POST ) ) {
				$data['user_id']       = $this->user_id;
				$data['active']        = 1;
				$data['priority']      = 0;
				$data['status']        = Ps_Statuses_ControlStatuses::getStatus(0, 'addAnket');
				$data['end_timestamp'] = date('Y-m-d H:i:s',time()+$this->config->ank_autodisable);
				
				/* required params*/
				$data['name']      = $frmAddAnket->getValue('name');
				$data['name_eng']  = $frmAddAnket->getValue('name_eng');
				$data['type']      = $frmAddAnket->getValue('type');
				$data['performer'] = $performer;//$frmAddAnket->getValue('performer');
				$data['city']      = '2';//$frmAddAnket->getValue('city');
				$data['district']  = $frmAddAnket->getValue('district');
				$data['metro']     = $frmAddAnket->getValue('metro');
				$data['place']     = $frmAddAnket->getValue('place');
				$data['age']       = $frmAddAnket->getValue('age');
				$data['breast']    = $frmAddAnket->getValue('breast');
				$data['height']    = $frmAddAnket->getValue('height');
				$data['weight']    = $frmAddAnket->getValue('weight');
				$data['phone']     = $this->preparePhone( $frmAddAnket->getValue('phone') );
				
				if ( $performer == 2 || $performer == 5 ) {
					$data['name_2']     = $frmAddAnket->getValue('name_2');
					$data['name_eng_2'] = $frmAddAnket->getValue('name_eng_2');
					$data['age_2']      = $frmAddAnket->getValue('age_2');
					$data['breast_2']   = $frmAddAnket->getValue('breast_2');
					$data['height_2']   = $frmAddAnket->getValue('height_2');
					$data['weight_2']   = $frmAddAnket->getValue('weight_2');
					$data['clothing_2'] = $frmAddAnket->getValue('clothing_2');
					$data['exotics_2']  = $frmAddAnket->getValue('exotics_2');
				}
				
				// grab size of fallos for trans
				if ( $performer == 7 ) {
					$data['breast_2']   = $frmAddAnket->getValue('breast_2'); 
				}
				
				/* other params */
				$other_params = array('exotics', 'clothing', 'price_1h_ap', 'price_2h_ap', 'price_n_ap',
					'price_1h_ex', 'price_2h_ex', 'price_n_ex', 'price_an', 'price_i_1h_ap', 'price_i_2h_ap',
					'price_i_n_ap', 'price_i_1h_ex', 'price_i_2h_ex', 'price_i_n_ex', 'price_i_an', 'about', 'about_i', 'only');
				
				foreach ( $other_params as $op ) {
					if ( $frmAddAnket->getValue( $op ) != null) {
						$data[ $op ] = $frmAddAnket->getValue( $op );
					}					
				}

				foreach ( $this->content->srv->toArray() as $srv=>$list ) {
					$serv=0;
					foreach ( $list as $key=>$null ) {
						if ( $frmAddAnket->getValue( $srv.'_'.$key ) ) {
							$serv+=1<<$key;
						}
					}
					$data['srv_'.$srv]=$serv;
				}
				
				$result = $ankets->add_anket($data);
				
				if ( $result ) {
					/* some strange requirement from manager */	
					$users = new Model_UsersTest();
					
					if ( $users->getFlags( $this->user_id ) == 0 ) {
						$res = $users->update(array("flags" => 2), "id = " . $this->user_id);
					}
					/* end of some strange requirement from manager */
					
					/* increment ankets per user */
					$users->update(array('ankets' => new Zend_Db_Expr('ankets + 1')), 'id = ' . $this->user_id);
					/* end of increment of ankets per user */
					
					$this->_redirect('/cabinet/edit-photo/n/'.$result.'?new=1');						
				}
			}
		} 
		
		$this->view->form = $frmAddAnket;		
		$this->view->type_label = $type_label;	
		$this->view->performer = $performer;
	}
	
	function editAnkFormAction () {	
		$id = $this->getParam('id');
		
		include_once 'Ankets.php';
		$ankets=new Ankets();		
		
		$performer = $ankets->get_ank_performer($id);
	    $salons = $this->getUsersSalons();
		switch ( $performer ) {
			case self::GIRL :
				$frmAddAnket = new Form_AddGirlAnkForm(self::GIRL, $this->content, array('types' => $salons));
				$type_label = 'девушки';
				break;
			case self::LESB :
				$frmAddAnket = new Form_AddLesbAnkForm(self::LESB, $this->content, array('types' => $salons));
				$type_label = 'подружки';
				break;
			case self::MASS :
				$frmAddAnket = new Form_AddMassAnkForm(self::MASS, $this->content, array('types' => $salons));
				$type_label = 'массажистки';
				break;
			case self::BDSM :
				$frmAddAnket = new Form_AddBdsmAnkForm(self::BDSM, $this->content, array('types' => $salons));
				$type_label = 'БДСМ';
				break;
			case self::MAN :
				$frmAddAnket = new Form_AddManAnkForm(self::MAN, $this->content, array('types' => $salons));
				$type_label = 'пары';
				break;
			case self::TRANS :
				$frmAddAnket = new Form_AddTransAnkForm(self::TRANS, $this->content, array('types' => $salons));
				$type_label = 'мужчины';
				break;
			case self::PAIR :
				$frmAddAnket = new Form_AddPairAnkForm(self::PAIR, $this->content, array('types' => $salons));
				$type_label = 'транса';
				break;
		}
	
		$frmAddAnket->setMethod("post");	

		if ( $this->getRequest()->isPost() ) {
			if ( $frmAddAnket->isValid( $_POST ) ) {	
				/* required params*/
				$data['name']      = $frmAddAnket->getValue('name');
				$data['name_eng']  = $frmAddAnket->getValue('name_eng');
				$data['type']      = $frmAddAnket->getValue('type');
				$data['performer'] = $performer;//$frmAddAnket->getValue('performer');
				$data['city']      = '2';//$frmAddAnket->getValue('city');
				$data['district']  = $frmAddAnket->getValue('district');
				$data['metro']     = $frmAddAnket->getValue('metro');
				$data['place']     = $frmAddAnket->getValue('place');
				$data['age']       = $frmAddAnket->getValue('age');
				$data['breast']    = $frmAddAnket->getValue('breast');
				$data['height']    = $frmAddAnket->getValue('height');
				$data['weight']    = $frmAddAnket->getValue('weight');
				$data['phone']     = $this->preparePhone( $frmAddAnket->getValue('phone') );
	
				if ( $performer == 2 || $performer == 5 ) {
					$data['name_2']     = $frmAddAnket->getValue('name_2');
					$data['name_eng_2'] = $frmAddAnket->getValue('name_eng_2');
					$data['age_2']      = $frmAddAnket->getValue('age_2');
					$data['breast_2']   = $frmAddAnket->getValue('breast_2');
					$data['height_2']   = $frmAddAnket->getValue('height_2');
					$data['weight_2']   = $frmAddAnket->getValue('weight_2');
					$data['clothing_2'] = $frmAddAnket->getValue('clothing_2');
					$data['exotics_2']  = $frmAddAnket->getValue('exotics_2');
				}
				
				// grab size of fallos for trans				
				if ( $performer == 7 ) {
					$data['breast_2']   = $frmAddAnket->getValue('breast_2');
				}
	
				/* other params */
				$other_params = array('exotics', 'clothing', 'price_1h_ap', 'price_2h_ap', 'price_n_ap',
						'price_1h_ex', 'price_2h_ex', 'price_n_ex', 'price_an', 'price_i_1h_ap', 'price_i_2h_ap',
						'price_i_n_ap', 'price_i_1h_ex', 'price_i_2h_ex', 'price_i_n_ex', 'price_i_an', 'about', 'about_i', 'only');
	
				foreach ( $other_params as $op ) {
					$data[ $op ] = $frmAddAnket->getValue( $op );
				}
	
				foreach ( $this->content->srv->toArray() as $srv=>$list ) {
					$serv=0;
					foreach ( $list as $key=>$null ) {
						if ( $frmAddAnket->getValue( $srv.'_'.$key ) ) {
							$serv+=1<<$key;
						}
					}
					$data['srv_'.$srv]=$serv;
				}
	
				$result = $ankets->upd_anket($id, $data);
	
				if ( $result ) {
					$this->_redirect('/cabinet?performer=' . $performer);
				}
			}
		} else {
			$anketa = $ankets->get_anket($id);			
			$anketa['phone']     = str_replace("-", '', $anketa['phone']);
			$type_label .= ' ' . $anketa['name'];
			
			foreach( $this->content->srv->toArray() as $srv=>$list ) {
				foreach( $list as $key=>$null ){
					$anketa[$srv.'_'.$key] = $anketa['srv'.'_'.$srv] & 1<<$key ? 1 : 0;
				}
			}
			
			foreach( $anketa as $key => $value ) {
				if ( $value === '0' ) {
					unset( $anketa[$key] );
				}
			}
			
			unset( $anketa['srv_main'] );
			unset( $anketa['srv_strip'] );
			unset( $anketa['srv_add'] );
			unset( $anketa['srv_bdsm'] );
			unset( $anketa['srv_mass'] );
			
			$frmAddAnket->populate($anketa);
		}
	
		$this->view->id   = $id;
		$this->view->form = $frmAddAnket;		
		$this->view->type_label = $type_label;		
	}
	
	function addSalonFormAction() {
		$frmAddSalon = new Form_AddSalonForm( $this->content, array('new' => true) );
		$frmAddSalon->setMethod("post");
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frmAddSalon->isValid( $_POST ) ) {				
				$data['user_id']       = $this->user_id;
				$data['active']        = 1;
				$data['priority']      = 0;
				$data['status']        = Ps_Statuses_ControlStatuses::getStatus(0, 'addSalon');
				$data['end_timestamp'] = date('Y-m-d H:i:s',time()+$this->config->ank_autodisable);
		
				/* required params*/
				$data['type']        = $frmAddSalon->getValue('type');
				$data['name']        = $frmAddSalon->getValue('name');
				$data['city']        = '2';//$frmAddSalon->getValue('city');
				$data['district']    = $frmAddSalon->getValue('district');
				$data['metro']       = $frmAddSalon->getValue('metro');				
				$data['phone']       = $this->preparePhone( $frmAddSalon->getValue('phone') );
				$data['girl_number'] = $frmAddSalon->getValue('girl_number');
				$data['room_number'] = $frmAddSalon->getValue('room_number');
				$data['time_from']   = $frmAddSalon->getValue('time_from') ? $frmAddSalon->getValue('time_from') : 0;
				$data['time_to']     = $frmAddSalon->getValue('time_to') ? $frmAddSalon->getValue('time_to') : 0;
				$data['name_eng']    = $frmAddSalon->getValue('name_eng');		
				
				if ( $frmAddSalon->getValue('phone_add') ) {
					$data['phone_add'] = $this->preparePhone( $frmAddSalon->getValue('phone_add') );
				}
		
				/* other params */
				$other_params = array('address', 'price_1h_ap', 'price_2h_ap', 'price_n_ap',
					'price_1h_ex', 'price_2h_ex', 'price_n_ex', 'price_i_1h_ap', 'price_i_2h_ap',
					'price_i_n_ap', 'price_i_1h_ex', 'price_i_2h_ex', 'price_i_n_ex', 'about', 'about_i', 'only',
					'sauna', 'bilyrd', 'devushki', 'bar', 'karaoke', 'kalyan');
		
				foreach ( $other_params as $op ) {
					if ( $frmAddSalon->getValue( $op ) != null) {
						$data[ $op ] = $frmAddSalon->getValue( $op );
					}
				}
		
				foreach ( $this->content->srv_salon->toArray() as $srv=>$list ) {
					$serv=0;
					foreach ( $list as $key=>$null ) {
						if ( $frmAddSalon->getValue( $srv.'_'.$key ) ) {
							$serv+=1<<$key;
						}
					}
					$data['srv_salon_'.$srv]=$serv;
				}
		
				include_once 'Salons.php';
				$salon = new Salons();				
				$result = $salon->add_salon($data);
		
				if ( $result ) {
					/* some strange requirement from manager */	
					$users = new Model_UsersTest();
					
					if ( $users->getFlags( $this->user_id ) == 0 ) {
						$res = $users->update(array("flags" => 2), "id = " . $this->user_id);
					}
					/* end of some strange requirement from manager */
					
					$this->_redirect('/cabinet/edit-photo-salon/n/'.$result.'?new=1');					
				}
			}
		} 
		
		$this->view->form = $frmAddSalon;		
	}
	
	function editSalonFormAction () {
		$id = $this->getParam('id');
		
		include_once 'Salons.php';
		$salon = new Salons();
		
		$frmAddSalon = new Form_AddSalonForm( $this->content );
		$frmAddSalon->setMethod("post");
		
		if ( $this->getRequest()->isPost() ) {
			if ( $frmAddSalon->isValid( $_POST ) ) {		
				/* required params*/
				$data['type']        = $frmAddSalon->getValue('type');
				$data['name']        = $frmAddSalon->getValue('name');
				$data['city']        = '2';//$frmAddSalon->getValue('city');
				$data['district']    = $frmAddSalon->getValue('district');
				$data['metro']       = $frmAddSalon->getValue('metro');
				$data['phone']       = $this->preparePhone( $frmAddSalon->getValue('phone') );
				$data['girl_number'] = $frmAddSalon->getValue('girl_number');
				$data['room_number'] = $frmAddSalon->getValue('room_number');
				$data['time_from']   = $frmAddSalon->getValue('time_from') ? $frmAddSalon->getValue('time_from') : 0;
				$data['time_to']     = $frmAddSalon->getValue('time_to') ? $frmAddSalon->getValue('time_to') : 0;
				$data['name_eng']    = $frmAddSalon->getValue('name_eng');
		
				if ( $frmAddSalon->getValue('phone_add') ) {
					$data['phone_add'] = $this->preparePhone( $frmAddSalon->getValue('phone') );
				}
		
				/* other params */
				$other_params = array('address', 'price_1h_ap', 'price_2h_ap', 'price_n_ap',
					'price_1h_ex', 'price_2h_ex', 'price_n_ex', 'price_i_1h_ap', 'price_i_2h_ap',
					'price_i_n_ap', 'price_i_1h_ex', 'price_i_2h_ex', 'price_i_n_ex', 'about', 'about_i', 'only',
					'sauna', 'bilyrd', 'devushki', 'bar', 'karaoke', 'kalyan');
		
				foreach ( $other_params as $op ) {
					$data[ $op ] = $frmAddSalon->getValue( $op );
				}
		
				foreach ( $this->content->srv_salon->toArray() as $srv=>$list ) {
					$serv=0;
					foreach ( $list as $key=>$null ) {
						if ( $frmAddSalon->getValue( $srv.'_'.$key ) ) {
							$serv+=1<<$key;
						}
					}
					$data['srv_salon_'.$srv] = $serv;
				}		
				
				$result = $salon->upd_salon($id, $data);
		
				if ( $result ) {
					$this->_redirect('/cabinet/salons');
				}
			}
		} else {
			$salon = $salon->get_salon($id);
			
			$salon['phone']     = str_replace("-", '', $salon['phone']);
			$salon['phone_add'] = str_replace("-", '', $salon['phone_add']);
			
			foreach( $this->content->srv_salon->toArray() as $srv=>$list ) {
				foreach( $list as $key=>$null ){
					$salon[$srv.'_'.$key] = $salon['srv_salon_'.$srv] & 1<<$key ? 1 : 0;
				}
			}
			
			foreach( $salon as $key => $value ) {
				if (  $value === '0' ) {
					unset( $salon[$key] );
				}
			}
			
			unset( $salon['srv_salon_intim'] );
			unset( $salon['srv_salon_strip'] );
			unset( $salon['srv_salon_bdsm'] );
			unset( $salon['srv_salon_mass'] );
			
			$frmAddSalon->populate($salon);
		}
	
		$this->view->id   = $id;		
		$this->view->form = $frmAddSalon;
	}
	
	public function financeStatisticAction () {
		$filter  = $this->getParam('filter') ? $this->getParam('filter') : false;
		
		switch ($filter) {
			case 0 :
				$filter = "date >= NOW() - INTERVAL 1 DAY";
				break;
			case 1 :
				$filter = "date >= NOW() - INTERVAL 1 WEEK";
				break;
			case 2 :
				$filter = "date >= NOW() - INTERVAL 1 MONTH";
				break;
			case 3 :
				$filter = "date >= NOW() - INTERVAL 3 MONTH";
			
		}

		$payments = new Model_Payment();

		$adapter = $payments->fetchPaginatorAdapter( true, $filter );
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
		
		$this->view->summ = $payments->getSumAmount( true, $filter );
	}

	public function payAction() {
		$balance = $this->calculateBalance();
		$spend = $this->calculateSpending();
		$forecast = $this->calculateForecast($balance, $spend);
		
		$this->view->balance = $balance . " (р.)";
		$this->view->spend = $spend . " (р.)";
		$this->view->forecast = $forecast;		
	}	
	
	public function paymentAction() {
		/* set vallets from finance web money config */
		include_once 'WebMoneySettings.php';
		$webmoney_settings = new WebMoneySettings();
		$data = $webmoney_settings->get();
		
		$this->view->vallets = array(
			"WMR" => $data["WMR"],
			"WMU" => $data["WMU"],
			"WME" => $data["WME"],
			"WMZ" => $data["WMZ"]
		);
		
		/* set user email */
		$users = new Model_UsersTest();
		$this->view->user_email = $users->getEmail($this->user_id);
				
		/* generate Payment No. */
		$payment_no = Ps_Payment_Order::getRandomPaymentNo();
		$this->view->payment_no = $payment_no;
		
		/*  set user id */
		$this->view->user_id = $this->user_id;
		
		/* get type */
		$this->view->type = $this->_getParam('type');
	}
	
	public function paymentInfoAction() {
		include_once 'FinanceSettings.php';	
		$finance_settings = new FinanceSettings();		
		$prices = $finance_settings->get();		
		$this->view->girl_price  = $prices['girl_hour_price'];
		$this->view->lesb_price  = $prices['lesb_hour_price'];
		$this->view->mass_price  = $prices['mass_hour_price'];
		$this->view->bdsm_price  = $prices['bdsm_hour_price'];
		$this->view->pair_price  = $prices['pair_hour_price'];
		$this->view->mam_price   = $prices['man_hour_price'];
		$this->view->trans_price = $prices['trans_hour_price'];
	}
	
	public function paymentSalonInfoAction() {
		include_once 'FinanceSettings.php';
		$finance_settings = new FinanceSettings();
		$prices = $finance_settings->get();
		$this->view->salon_price = $prices['salon_hour_price'];
	}
	
	public function paymentHistoryAction() {	
		/* set payments */
		$payment = new  Model_Payment();
		$adapter = $payment->getPaymentsByUserId( $this->user_id );
		
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->payments = $paginator;
	}
	
	public function ankExtendAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$new_info=array('end_timestamp'=>date('Y-m-d H:i:s',time()+$this->config->ank_autodisable));
		$ankets->upd_anket($id,$new_info);
		$this->_redirect('/cabinet');
		die;
	}
	
	public function salonExtendAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$new_info=array('end_timestamp'=>date('Y-m-d H:i:s',time()+$this->config->ank_autodisable));
		$salons->upd_salon($id,$new_info);
		$this->_redirect('/cabinet/salons');
		die;
	}
	
	public function editPhotoAction(){
		$this->get_config_info();
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin && !$this->user_moder){
			$this->error('no_rights_ank');return;
		}
		$photos_num=$this->config->limit->max_photos;
		if(empty($info['photolist'])){$info['photolist']=array_fill(0,$photos_num,false);}
		else{
			$info['photolist']=unserialize($info['photolist']);
			if(isset($info['photolist']['preview'])){
				$this->view->preview=$info['photolist']['preview'];
				unset($info['photolist']['preview']);
			}
			$count=count($info['photolist']);		
			if($count<$photos_num){
				for($ii=0;$ii<$photos_num;$ii++){
					if(!isset($info['photolist'][$ii])){$info['photolist'][$ii]=false;}
				}
			}
		}
		$this->view->photos_path.='/'.$info['user_id'];
		$this->view->info=$info;
		$this->view->status = $info['status'];
	}
	
	public function editPhotoSalonAction(){
		$this->get_salon_info();
		if( !$this->_hasParam('n') ){
			$this->error('request_error');
			return;
		}
		$id = intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';	
		$salons = new Salons();
		$info = $salons->get_salon($id);
		if($info['user_id'] != $this->user_id && !$this->user_admin && !$this->user_moder){
			$this->error('no_rights_ank'); return;
		}
		$photos_num=$this->config->limit->max_photos;
		if(empty($info['photolist'])){
			$info['photolist']=array_fill(0,$photos_num,false);
		}
		else
		{
			$info['photolist']=unserialize($info['photolist']);
			if(isset($info['photolist']['preview'])){
				$this->view->preview=$info['photolist']['preview'];
				unset($info['photolist']['preview']);
			}
			$count=count($info['photolist']);
			if($count<$photos_num){
				for($ii=0;$ii<$photos_num;$ii++){
					if(!isset($info['photolist'][$ii])){
						$info['photolist'][$ii]=false;
					}
				}
			}
		}
		$this->view->photos_path.='/'.$info['user_id'];
		$this->view->info=$info;
	}
	
	public function addPhotoAction() 
	{
		$id = $this->getParam('n');

		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);

		$err_nm=0;
		foreach($_FILES as $file){if($file['error']){$err_nm++;}}
		if( $err_nm<count($_FILES) ){
			$this->hasRights(array('user_admin', 'user_moder'), array($info['user_id'], $this->user_id));
			$dir=$this->config->path->user_photos.'/'.$info['user_id'];
			if(!is_dir($dir)){mkdir($dir);}
			if(empty($info['photolist'])){$photos=array_fill(0,$this->config->limit->max_photos,false);}
			else{$photos=unserialize($info['photolist']);}
			include_once 'SimpleImage.php';
			$cnt=-1;
			foreach($_FILES as $file){
				$cnt++;
				if($file['error'] || $file['size']>$this->config->limit->max_photo_size){
					continue;
				}	
				$path_tmp=$dir.'/'.$file['name'];
				move_uploaded_file($file['tmp_name'],$path_tmp);
				$hash=md5_file($path_tmp);
				$ext=array_pop(explode('.',$file['name']));
				$path=$dir.'/'.$hash.'.'.$ext;
				$simage=new SimpleImage();
				if ( ! $simage->checkFile( $path_tmp, $this->config->limit->min_photo_width, $this->config->limit->min_photo_height ) ){
					$cnt--;
					continue;
				}
				$simage->load($path_tmp);
				if($simage->getWidth()>$this->config->limit->max_photo_width){
					$simage->resizeToWidth($this->config->limit->max_photo_width);
				}
				$simage->save($path);
				$conf_aspect=$this->config->thumbnale_width/$this->config->thumbnale_height;
				$aspect=$simage->getWidth()/$simage->getHeight();
				if($aspect>$conf_aspect){$simage->resizeToHeight($this->config->thumbnale_height);}
				else{$simage->resizeToWidth($this->config->thumbnale_width);}
				$simage->save($dir.'/th_'.$hash.'.'.$ext);
				unlink($path_tmp);
				if(isset($photos[$cnt]) && $photos[$cnt] && $photos[$cnt]!=$hash.'.'.$ext){
					unlink($dir.'/'.$photos[$cnt]);
					unlink($dir.'/th_'.$photos[$cnt]);
				}
				$photos[$cnt]=$hash.'.'.$ext;
			}

			$count = 0;
			foreach($photos as $key => $value) {
				if( $value && is_int($key) ) {
					$count++;
				}
			}

			$new_info = array(
				'photolist' => serialize($photos),
				'status'	=> Ps_Statuses_ControlStatuses::getStatus(
					$info['status'], 'addPhoto', array(
						'count' => $count,
						'photo_check' => $info['photo_check']
						)
					)
			);	
			
			$ankets->upd_anket($id,$new_info);
		}

		if ( $this->_hasParam('preview') ) {
			$this->hasRights(array('user_admin', 'user_moder'), array($info['user_id'], $this->user_id));

			$photos=unserialize($info['photolist']);

			$preview=intval(substr($this->_getParam('preview'),0,4));
			if($preview>=0 && $preview<=$this->config->limit->max_photos){
				$photos['preview']=$preview;
			}

			$new_info = array('photolist' => serialize($photos));
			$ankets->upd_anket($id,$new_info);
		}

		$redirect='/cabinet?performer=' . $info['performer'];
		if($this->_hasParam('next')){$redirect='/cabinet/check-photo/n/'.$id.'?new=1';}
		elseif($this->_hasParam('to_ank_edit')){$redirect='/cabinet/edit-ank-form/id/'.$id;}
		elseif($this->_hasParam('to_check_photo')){$redirect='/cabinet/check-photo/n/'.$id;}
		elseif($this->_hasParam('to_video')){$redirect='/cabinet/edit-video/n/'.$id;}
		$this->_redirect($redirect);
		die;
	}

	public function addPhotoSalonAction()
	{
		$id = $this->getParam('n');

		include_once 'Salons.php';
		$salons = new Salons();
		$info = $salons->get_salon($id);

		$err_nm = 0;
		foreach ( $_FILES as $file ) {
			if ( $file['error'] ) {
				$err_nm++;
			}
		}

	    if ( $err_nm < count($_FILES) ) {
	    	 $this->hasRights(array('user_admin', 'user_moder'), array($info['user_id'], $this->user_id));
	    	 $dir = $this->config->path->user_photos . '/' . $info['user_id'];
	    	 if ( !is_dir($dir)) {
	    	 	mkdir($dir);
	    	 }
	    	 
	    	 if (empty($info['photolist'])) {
	    	 	$photos = array_fill(0, $this->config->limit->max_photos, false);
	    	 } else {
	    	 	$photos = unserialize($info['photolist']);
	    	 }
	    	 
	    	 include_once 'SimpleImage.php';
	    	 $cnt=-1;	    	
	    	 foreach($_FILES as $file){
	    	 	$cnt++;
	    	 	if($file['error'] || $file['size']>$this->config->limit->max_photo_size){
	    	 		continue;
	    	 	}	    	 	
	    	 	$path_tmp=$dir.'/'.$file['name'];
	    	 	move_uploaded_file($file['tmp_name'],$path_tmp);
	    	 	$hash=md5_file($path_tmp);
	    	 	$ext=array_pop(explode('.',$file['name']));
	    	 	$path=$dir.'/'.$hash.'.'.$ext;
	    	 	$simage=new SimpleImage();
	    	 	if ( ! $simage->checkFile( $path_tmp, $this->config->limit->min_photo_width, $this->config->limit->min_photo_height ) ){
	    	 		$cnt--;continue;
	    	 	}
	    	 	$simage->load($path_tmp);
	    	 	if($simage->getWidth()>$this->config->limit->max_photo_width){
	    	 		$simage->resizeToWidth($this->config->limit->max_photo_width);
	    	 	}
	    	 	$simage->save($path);
	    	 	$conf_aspect=$this->config->thumbnale_salon_width/$this->config->thumbnale_salon_height;
	    	 	$aspect=$simage->getWidth()/$simage->getHeight();
	    	 	if($aspect>$conf_aspect){
	    	 		$simage->resizeToHeight($this->config->thumbnale_salon_height);
	    	 	}
	    	 	else{$simage->resizeToWidth($this->config->thumbnale_salon_width);
	    	 	}
	    	 	$simage->save($dir.'/th_'.$hash.'.'.$ext);
	    	 	unlink($path_tmp);
	    	 	if(isset($photos[$cnt]) && $photos[$cnt] && $photos[$cnt]!=$hash.'.'.$ext){
	    	 		unlink($dir.'/'.$photos[$cnt]);
	    	 		unlink($dir.'/th_'.$photos[$cnt]);
	    	 	}
	    	 	$photos[$cnt]=$hash.'.'.$ext;
	    	 }

	    	 $count = 0;
	    	 foreach($photos as $key => $value) {
	    	 	if( $value && is_int($key) ) {
	    	 		$count++;
	    	 	}
	    	 } 
	    	 
	    	 $new_info = array(
	    	 	'photolist' => serialize($photos),
	    	 	'status'	=> Ps_Statuses_ControlStatuses::getStatus(
	    	 		$info['status'], 'addPhotoSalon', array(
	    	 			'count' => $count
	    	 		)
	    	 	)
	    	 );	
	    	 $salons->upd_salon($id,$new_info);

	    	 if ( $new_info['status'] == 20 && $info['status'] != 20) {
	    	 	$notifier = new Ps_Notifier_EmailNewSalon();
	    	 	$notifier->send($info);
	    	 }
	    }

	    if ( $this->_hasParam('preview') ) {
	    	$this->hasRights(array('user_admin', 'user_moder'), array($info['user_id'], $this->user_id));

	    	$photos=unserialize($info['photolist']);

	    	$preview = $this->getParam('preview');
	    	if ( $preview>=0 && $preview<=$this->config->limit->max_photos ) {
	    		$photos['preview']=$preview;
	    	}

	    	$new_info = array('photolist' => serialize($photos));
	    	$salons->upd_salon($id,$new_info);
	    }

		$redirect='/cabinet';
		if($this->_hasParam('next')){
			$redirect .= '/edit-video-salon/n/'.$id.'?new=1';
		}
		if($this->_hasParam('to_salon_edit')){
			$redirect.='/edit-salon-form/id/'.$id;
		} elseif ($this->_hasParam('to_video')) {
			$redirect.='/edit-video-salon/n/'.$id;
		} else {
			$redirect.='/salons';
		}		
		$this->_redirect($redirect);
		die;
	}
	
	public function delPhotoAction() {
		if(!$this->_hasParam('n') || !$this->_hasParam('f')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		$ph=intval(substr($this->_getParam('f'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$dir=$this->config->path->user_photos.'/'.$info['user_id'];
		if(empty($info['photolist'])){$this->_redirect('/cabinet/edit-photo/n/'.$id);die;}
		else{$photos=unserialize($info['photolist']);}
		if(!isset($photos[$ph]) || !$photos[$ph]){$this->error('request_error');return;}
		unlink($dir.'/'.$photos[$ph]);
		unlink($dir.'/th_'.$photos[$ph]);
		$photos[$ph]=false;
		
		$real_number = 0;
		foreach ( $photos as $key => $value ) {
			if ( $value ) {
				$new_photos[$real_number++] = $value;				
			}
		}
		
		$count = 0;
		foreach($photos as $key => $value) {
			if( $value && is_int($key) ) {
				$count++;
			}
		}
		
		$ankets->upd_anket($id,array(
			'photolist'=>serialize($new_photos),
			'status' => Ps_Statuses_ControlStatuses::getStatus($info['status'], 'delPhoto', array('count' => $count, 'photo_check' => $info['photo_check']))
		));
		
		$this->_redirect('/cabinet/edit-photo/n/'.$id);
		die;
	}
	
	public function delPhotoSalonAction(){
		if(!$this->_hasParam('n') || !$this->_hasParam('f')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		$ph=intval(substr($this->_getParam('f'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$dir=$this->config->path->user_photos.'/'.$info['user_id'];
		if(empty($info['photolist'])){$this->_redirect('/cabinet/edit-photo/n/'.$id);die;}
		else{$photos=unserialize($info['photolist']);}
		if(!isset($photos[$ph]) || !$photos[$ph]){$this->error('request_error');return;}
		unlink($dir.'/'.$photos[$ph]);
		unlink($dir.'/th_'.$photos[$ph]);
		$photos[$ph]=false;
		
		$real_number = 0;
		foreach ( $photos as $key => $value ) {
			if ( $value ) {
				$new_photos[$real_number++] = $value;
			}
		}
		
		$count = 0;
		foreach($photos as $key => $value) {
			if( $value && is_int($key) ) {
				$count++;
			}
		}		
		
		$salons->upd_salon($id,array(
			'photolist'=>serialize($new_photos),
			'status' => Ps_Statuses_ControlStatuses::getStatus($info['status'], 'delPhotoSalon', array('count' => $count ))
		));
		$this->_redirect('/cabinet/edit-photo-salon/n/'.$id);
		die;
	}
	
	public function checkPhotoAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin && !$this->user_moder){$this->error('no_rights_ank');return;}
		$this->view->photos_path.='/'.$info['user_id'];
		$this->view->status = $info['status'];
		$this->view->photo_check = unserialize($info['photo_check']);
		$this->view->info=$info;
		
		$comm_by_ankets_moderation = new Model_AnketsModerationComm();
		$this->view->comm =	$comm_by_ankets_moderation->getByOwnerId( $info['id'] );
	}	
	
	public function checkPhotoAddAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		
		$err_nm=0;
		foreach($_FILES as $file){if($file['error']){$err_nm++;}}
		if( $err_nm<count($_FILES) ){			
			$this->hasRights('user_admin', array($info['user_id'], $this->user_id));
									
			$dir=$this->config->path->user_photos.'/'.$info['user_id'];
			if(!is_dir($dir)){
				mkdir($dir);
			}
			$photos_check = unserialize($info['photo_check']);
			
			include_once 'SimpleImage.php';
			if ( !$photos_check ) {
				$cnt = -1;
			} else {
				$cnt = count($photos_check) - 1;
			}
			foreach($_FILES as $file){
				$cnt++;
				if($file['error'] || $file['size']>$this->config->limit->max_photo_size){continue;}
				$path_tmp=$dir.'/'.$file['name'];
				move_uploaded_file($file['tmp_name'],$path_tmp);
				$hash=md5_file($path_tmp);
				$ext=array_pop(explode('.',$file['name']));
				$path=$dir.'/'.$hash.'.'.$ext;
				$simage=new SimpleImage();
				$simage->load($path_tmp);
				if($simage->getWidth()>$this->config->limit->max_photo_width){
					$simage->resizeToWidth($this->config->limit->max_photo_width);
				}
				$simage->save($path);
				$conf_aspect=$this->config->thumbnale_width/$this->config->thumbnale_height;
				$aspect=$simage->getWidth()/$simage->getHeight();
				if($aspect>$conf_aspect){
					$simage->resizeToHeight($this->config->thumbnale_height);
				}else{
					$simage->resizeToWidth($this->config->thumbnale_width);
				}
				$simage->save($dir.'/th_'.$hash.'.'.$ext);
				unlink($path_tmp); 
				if(isset($photos_check[$cnt]) && $photos_check[$cnt] && $photos_check[$cnt]!=$hash.'.'.$ext){
					unlink($dir.'/'.$photos_check[$cnt]);
					unlink($dir.'/th_'.$photos_check[$cnt]);
				}
				$photos_check[$cnt]=$hash.'.'.$ext;
			}			
			
			/* get photos count for right status */
			$photos = unserialize($info['photolist']);
			$count  = 0;
			foreach($photos as $key => $value) {
				if( $value && is_int($key) ) {
					$count++;
				}
			}
			
			$new_info = array(
				'photo_check' => serialize($photos_check),
				'status'      => Ps_Statuses_ControlStatuses::getStatus($info['status'], 'addCheckPhoto', array('count' => $count))
			);
			
			$ankets->upd_anket($id,$new_info);
		}		
		
		$redirect='/cabinet?performer=' . $info['performer'];
		if($this->_hasParam('next')){$redirect = '/cabinet/edit-video/n/'.$id.'?new=1';}
		elseif($this->_hasParam('to_photo')){$redirect='/cabinet/edit-photo/n/'.$id;}
		elseif($this->_hasParam('to_ank_edit')){$redirect ='/cabinet/edit-ank-form/id/'.$id;}
		elseIf($this->_hasParam('to_video')){$redirect = '/cabinet/edit-video/n/'.$id;}	
		$this->_redirect($redirect);
		die;
	}
	
	public function checkPhotoSalonAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		$file=$_FILES['photo'];
		if(!$file['error'] && $file['size']<$this->config->limit->max_photo_size){
			include_once 'Salons.php';
			$salon=new Salons();
			$info=$salon->get_salon($id);
			if($info['user_id']!=$this->user_id && !$this->user_admin){
				$this->error('no_rights_ank');return;
			}
			$dir=$this->config->path->user_photos.'/'.$info['user_id'];
			if(!is_dir($dir)){
				mkdir($dir);
			}
			include_once 'SimpleImage.php';
			$path_tmp=$dir.'/'.$file['name'];
			move_uploaded_file($file['tmp_name'],$path_tmp);
			$hash=md5_file($path_tmp);
			$ext=array_pop(explode('.',$file['name']));
			$path=$dir.'/'.$hash.'.'.$ext;
			$simage=new SimpleImage();
			$simage->load($path_tmp);
			if($simage->getWidth()>$this->config->limit->max_photo_width){
				$simage->resizeToWidth($this->config->limit->max_photo_width);
			}
			$simage->save($path);
			$conf_aspect=$this->config->thumbnale_width/$this->config->thumbnale_height;
			$aspect=$simage->getWidth()/$simage->getHeight();
			if($aspect>$conf_aspect){
				$simage->resizeToHeight($this->config->thumbnale_height);
			}
			else{$simage->resizeToWidth($this->config->thumbnale_width);
			}
			$simage->save($dir.'/th_'.$hash.'.'.$ext);
			unlink($path_tmp);
			$filename=$hash.'.'.$ext;
			if(!empty($info['photo_check']) && $info['photo_check']!=$filename){
				unlink($dir.'/'.$info['photo_check']);
				unlink($dir.'/th_'.$info['photo_check']);
			}
			$new_info=array('photo_check'=>$filename);
			if($info['status']==40){
				$new_info['status']=50;
			}
			$salon->upd_salon($id,$new_info);
		}
		$redirect='/cabinet';
		if($this->_hasParam('to_photo')){
			$redirect.='/edit-photo-salon/n/'.$id;
		}
		elseif($this->_hasParam('to_ank_edit')){
			$redirect.='/edit-salon-form/n/'.$id;
		}
		elseif($this->_hasParam('to_check')){
			$redirect.='/to-moderation/n/'.$id;
		}
		$this->_redirect($redirect);
		die;
	}
	
	public function editVideoAction(){
		$this->get_config_info();
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin && !$this->user_moder){$this->error('no_rights_ank');return;}
		$items_num=$this->config->limit->max_videos;
		if(empty($info['videolist'])){$info['videolist']=array_fill(0,$items_num,false);}
		else{
			$info['videolist']=unserialize($info['videolist']);
			if(isset($info['videolist']['preview'])){
				$this->view->preview=$info['videolist']['preview'];
				unset($info['videolist']['preview']);
			}			
		}
		$this->view->videos_path.='/'.$info['user_id'];
		$this->view->info=$info;
	}
	
	public function editVideoSalonAction(){
		$this->get_salon_info();
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$items_num=$this->config->limit->max_videos;
		if(empty($info['videolist'])){
			$info['videolist']=array_fill(0,$items_num,false);
		}
		else{
			$info['videolist']=unserialize($info['videolist']);
			if(isset($info['videolist']['preview'])){
				$this->view->preview=$info['videolist']['preview'];
				unset($info['videolist']['preview']);
			}
			$count=count($info['videolist']);			
		}
		$this->view->videos_path.='/'.$info['user_id'];
		$this->view->info=$info;
	}

	public function addVideoAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		
		$err_nm=0;
		foreach($_FILES as $file){if($file['error']){$err_nm++;}}
		if($err_nm<count($_FILES) || $this->_hasParam('preview')){			
			if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
			$dir=$this->config->path->user_videos.'/'.$info['user_id'];
			if(!is_dir($dir)){mkdir($dir);}
			if(empty($info['videolist'])){$items=array_fill(0,(int)$this->config->limit->max_videos,false);}
			else{$items=unserialize($info['videolist']);}
			include_once 'SimpleImage.php';
			$cnt=-1;
			foreach($_FILES as $file){
				$cnt++;
				if($file['error'] || $file['size']>$this->config->limit->max_video_size){continue;}
				$path_tmp=$dir.'/'.$file['name'];
				move_uploaded_file($file['tmp_name'],$path_tmp);
				$hash=md5_file($path_tmp);
				$ext=substr(strrchr($file['name'], '.'), 1);
				$path=$dir.'/'.$hash.'.'.$ext;				
				copy($path_tmp, $path);
				unlink($path_tmp);
				if(isset($items[$cnt]) && $items[$cnt] && $items[$cnt]!=$hash.'.'.$ext){
					unlink($dir.'/'.$items[$cnt]);
					unlink($dir.'/th_'.$items[$cnt]);
				}
				$items[$cnt]=$hash.'.'.$ext;
			}
			/*if($this->_hasParam('preview')){
				$preview=intval(substr($this->_getParam('preview'),0,4));
				if($preview>=0 && $preview<=$this->config->limit->max_videos){$items['preview']=$preview;}
			}*/
			$items['preview'] = 0;
			$status=false;
			foreach($items as $photo){if($photo){$status=true;break;}}
			$new_info=array('videolist'=>serialize($items));
			if($info['status']==60 || $info['status']==40){$new_info['status']=50;}
			elseif($info['status']==0 && $status){$new_info['status']=20;}
			$ankets->upd_anket($id,$new_info);
		}
		$redirect='/cabinet?performer=' . $info['performer'];		
		if($this->_hasParam('to_ank_edit')){$redirect='/cabinet/edit-ank-form/id/'.$id;}
		elseif($this->_hasParam('to_check_photo')){$redirect='/cabinet/check-photo/n/'.$id;}
		elseif($this->_hasParam('to_photo')){$redirect='/cabinet/edit-photo/n/'.$id;}		
		$this->_redirect($redirect);
		die;
	}
	
	public function addVideoSalonAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		$err_nm=0;
		foreach($_FILES as $file){
			if($file['error']){
				$err_nm++;
			}
		}
		if($err_nm<count($_FILES) || $this->_hasParam('preview')){
			include_once 'Salons.php';
			$salons=new Salons();
			$info=$salons->get_salon($id);
			if($info['user_id']!=$this->user_id && !$this->user_admin){
				$this->error('no_rights_ank');return;
			}
			$dir=$this->config->path->user_videos.'/'.$info['user_id'];
			if(!is_dir($dir)){
				mkdir($dir);
			}
			if(empty($info['videolist'])){
				$items=array_fill(0,(int)$this->config->limit->max_videos,false);
			}
			else{$items=unserialize($info['videolist']);
			}
			include_once 'SimpleImage.php';
			$cnt=-1;
			foreach($_FILES as $file){
				$cnt++;
				if($file['error'] || $file['size']>$this->config->limit->max_video_size){
					continue;
				}
				$path_tmp=$dir.'/'.$file['name'];
				move_uploaded_file($file['tmp_name'],$path_tmp);
				$hash=md5_file($path_tmp);
				$ext=substr(strrchr($file['name'], '.'), 1);
				$path=$dir.'/'.$hash.'.'.$ext;

				copy($path_tmp, $path);
				unlink($path_tmp);
				if(isset($items[$cnt]) && $items[$cnt] && $items[$cnt]!=$hash.'.'.$ext){
					unlink($dir.'/'.$items[$cnt]);
					unlink($dir.'/th_'.$items[$cnt]);
				}
				$items[$cnt]=$hash.'.'.$ext;
			}

			$items['preview'] = 0;

			$new_info=array('videolist'=>serialize($items));
			$salons->upd_salon($id,$new_info);
		}
		$redirect='/cabinet';
		if($this->_hasParam('to_salon_edit')){
			$redirect.='/edit-salon-form/id/'.$id;
		} elseif ( $this->_hasParam('to_photo') ) {	
			$redirect.='/edit-photo-salon/n/'.$id;	
		}
		$this->_redirect($redirect);
		die;
	}

	public function delVideoAction(){
		if(!$this->_hasParam('n') || !$this->_hasParam('f')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		$ph=intval(substr($this->_getParam('f'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$dir=$this->config->path->user_videos.'/'.$info['user_id'];
		if(empty($info['videolist'])){$this->_redirect('/cabinet/edit-video/n/'.$id);die;}
		else{$items=unserialize($info['videolist']);}
		if(!isset($items[$ph]) || !$items[$ph]){$this->error('request_error');return;}
		unlink($dir.'/'.$items[$ph]);
		unlink($dir.'/th_'.$items[$ph]);
		$items[$ph]=false;
		$ankets->upd_anket($id,array('videolist'=>serialize($items)));
		$this->_redirect('/cabinet/edit-video/n/'.$id);
		die;
	}
	
	public function delVideoSalonAction(){
		if(!$this->_hasParam('n') || !$this->_hasParam('f')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		$ph=intval(substr($this->_getParam('f'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$dir=$this->config->path->user_videos.'/'.$info['user_id'];
		if(empty($info['videolist'])){
			$this->_redirect('/cabinet/edit-video-salon/n/'.$id);die;
		}
		else{$items=unserialize($info['videolist']);
		}
		if(!isset($items[$ph]) || !$items[$ph]){
			$this->error('request_error');return;
		}
		unlink($dir.'/'.$items[$ph]);
		unlink($dir.'/th_'.$items[$ph]);
		$items[$ph]=false;
		$salons->upd_salon($id,array('videolist'=>serialize($items)));
		$this->_redirect('/cabinet/edit-video-salon/n/'.$id);
		die;
	}
	
	public function delAnkAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$this->view->info=$info;
	}
	
	public function delSalonAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$this->view->info=$info;
	}
	
	public function delAnkConfAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$ankets->del_anket($id);
		include_once 'CountersAnkets.php';
		$counters=new CountersAnkets();
		$counters->del_ank($id);
		include_once 'Users.php';
		$users=new Users;
		$users->ank_dec($this->user_id);
		$dir=$this->config->path->user_photos.'/'.$info['user_id'];
		foreach(unserialize($info['photolist']) as $photo){
			unlink($dir.'/'.$photo);
			unlink($dir.'/th_'.$photo);
		}
		$this->_redirect('/cabinet');
		die;
	}
	
	public function delSalonConfAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$salons->del_salon($id);
		include_once 'CountersAnkets.php';
		$counters=new CountersAnkets();
		$counters->del_salon($id);
		include_once 'Users.php';
		$users=new Users;
		$users->salon_dec($this->user_id);
		$dir=$this->config->path->user_photos.'/'.$info['user_id'];
		foreach(unserialize($info['photolist']) as $photo){
			unlink($dir.'/'.$photo);
			unlink($dir.'/th_'.$photo);
		}
		$this->_redirect('/cabinet/salons');
		die;
	}
	
	public function disableAnkAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$ankets->upd_anket($id,array('active'=>0));
		$this->_redirect('/cabinet' . $this->addGetParamsToUri()); 
		die;
	}
	
	public function disableSalonAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');
			return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){
			$this->error('no_rights_ank');return;
		}
		$salons->upd_salon($id,array('active'=>0));
		$this->_redirect('/cabinet/salons');
		die;
		
	}
	
	public function enableAnkAction(){
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$ankets->upd_anket($id,array('active'=>1));
		$this->_redirect('/cabinet' . $this->addGetParamsToUri());
		die;
	}
	
	public function enableSalonAction(){
		if(!$this->_hasParam('n')){
			$this->error('request_error');return;
		}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		if($info['user_id']!=$this->user_id && $this->hasRights( 'user_admin' )){
			$this->error('no_rights_ank');return;
		}
		$salons->upd_salon($id,array('active'=>1));
		$this->_redirect('/cabinet/salons');
		die;
	}
	
	public function adminAction(){
		$this->hasRights( array('user_tech', 'user_admin') );		
	}
	
	public function billingAction() {
		$this->hasRights( 'user_admin' );
	}
	
	public function moderAction(){
		$this->hasRights( 'user_admin' );
	}
	
	public function allAnksAction(){
		$this->hasRights( array('user_tech', 'user_admin') );
	}
	
	public function myAnksAction(){
		$this->hasRights( array('user_tech', 'user_admin') );	
	}
	
	public function metaAction(){
		$this->hasRights( array('user_tech', 'user_admin') );
		include_once 'Meta.php';
		$meta=new Meta;
		$this->view->data=$meta->get();
	}
	
	public function settingsAction(){
		$this->hasRights( array('user_tech', 'user_admin') );
		include_once 'Settings.php';
		$settings=new Settings;
		$this->view->data=$settings->get();
	}
	
	public function financeSettingsAction() {
		$this->hasRights('user_admin');
		include_once 'FinanceSettings.php';
		$settings=new FinanceSettings;
		$this->view->data=$settings->get();
	}
	
	public function webmoneySettingsAction() {
		$this->hasRights('user_admin');
		include_once 'WebMoneySettings.php';
		$webmoney_settings = new WebMoneySettings();
		$this->view->data = $webmoney_settings->get();
	}
	
	function usersAction() {		
		$this->hasRights( 'user_admin' );		
		$filter = $this->getParam('filter');
		
		$users = new Model_UsersTest();
		$adapter = $users->fetchPaginatorAdapter( $filter );
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}	
	
	function anketsAction() {
		$this->hasRights( array('user_moder', 'user_admin') );
		
		$ankets_per_users = new Model_AnketsTest();
		$adapter = $ankets_per_users->fetchAnketsPerUsersPaginationAdapter();
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}
	
	function salonsPerUsersAction() {
		$this->hasRights( array('user_moder', 'user_admin') );
		
		$salons_per_users = new Model_SalonsTest();
		$adapter = $salons_per_users->fetchSalonsPerUsersPaginationAdapter();
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;		
	}
	
	function commentsAction() {
		$this->hasRights( array('user_moder', 'user_admin') );
		
		$commetns_per_users = new Model_CommentsTest();
		$adapter = $commetns_per_users->fetchCommentsPerUsersPaginationAdapter();
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(1000);
		$page = $this->_request->getParam('page', 1);
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
	}
	
	public function metaWriteAction(){
		$this->hasRights( array('user_tech', 'user_admin') );
		
		$input=array(
			'start_logo_alt',
			'start_title',
			'start_keys',
			'start_desc',
			'city_piter',
			'city_moscow',
			'menu_girls',
			'menu_salons',
			'menu_shops',
			'ank_title',
			'ank_keys',
			'ank_desc',
			'ank_text',
			'ank_img_alt',
			'ank_detail',
			'inst_title',
			'inst_keys',
			'inst_desc',
			'inst_text',
			'title',
		    'text',
			'column_left',
			'column_right',
			'footer'
		);
		$data=array();
		foreach($input as $value){
			if($this->_hasParam($value)){$data[$value]=$this->_getParam($value);}
			else{$data[$value]='';}
		}
		include_once 'Meta.php';
		$meta=new Meta;
		$meta->set($data);
		$this->_redirect('/cabinet/admin');
		die;
	}
	
	public function settingsWriteAction(){
		if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$input=array(
			'girls_per_page'
		);
		$data=array();
		foreach($input as $value){
			if($this->_hasParam($value)){$data[$value]=intval($this->_getParam($value));}
			else{$data[$value]=false;}
		}
		include_once 'Settings.php';
		$settings=new Settings;		
		$settings->set($data);
		$this->_redirect('/cabinet/admin');
		die;
	}

	public function financeSettingsWriteAction() {
		if( !$this->user_admin ){
			$this->_redirect('/cabinet');die;
		}
		$input=array(
				'girl_hour_price', 'girl_hour_discount',
				'lesb_hour_price', 'lesb_hour_discount',
				'mass_hour_price', 'mass_hour_discount',
				'bdsm_hour_price', 'bdsm_hour_discount',
				'man_hour_price', 'man_hour_discount',
				'trans_hour_price', 'trans_hour_discount',
				'pair_hour_price', 'pair_hour_discount',
				'salon_hour_price', 'salon_hour_discount'
		);
		$data=array();
		foreach($input as $value){
			if($this->_hasParam($value)){
				$data[$value]=intval($this->_getParam($value));
			}
			else{$data[$value]=false;
			}
		}
		include_once 'FinanceSettings.php';
		$settings=new FinanceSettings;
		$settings->set($data);
		$this->_redirect('/cabinet/admin');
		die;
	}

	public function webmoneySettingsWriteAction() {
		if ( !$this->user_admin ) {
			$this->_redirect('/cabinet'); die;
		}
		
		$input = array(
			'WMR',
			'WMZ',
			'WMU',
			'WME',
			'DESC',
			'SECRET_KEY'	
		);
		
		$data = array();
		foreach( $input as $value ){
			if ( $this->_hasParam($value) ) {
				$data[$value] = $this->_getParam($value);
			} else {
				$data[$value] = false;
			}
		}
		
		include_once 'WebMoneySettings.php';
		$webmoney_settings = new WebMoneySettings();
		$webmoney_settings->set($data);
		$this->_redirect('/cabinet/');
		die;
	}
	
	public function priorityWriteAction(){
		$this->_helper->viewRenderer->setScriptAction('error');		
		$id=intval(substr($this->_getParam('n'),0,32));
		$pr=$this->getParam('pr');
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		
		/* get cost for anket's type */		
		include_once 'FinanceSettings.php';
		$settings = new FinanceSettings;
		$settings = $settings->get();
		
		switch ( $info['performer'] ) {
			case 1 :
				$cost = $settings['girl_hour_price'] / 24;
				break;
				
			case 2 :
				$cost = $settings['lesb_hour_price'] / 24;
				break;
				
			case 3 :
				$cost = $settings['mass_hour_price'] / 24;
				break;
					
			case 4 :
				$cost = $settings['bdsm_hour_price'] / 24;
				break;
				
			case 5 :
				$cost = $settings['pair_hour_price'] / 24;
				break;
				
			case 6 :
				$cost = $settings['man_hour_price'] / 24;
				break;
					
			case 7 :
				$cost = $settings['trans_hour_price'] / 24;
				break;
				
			default :
				$cost = 100;
				break;
		}		
	   /* end of get cost for ankets's type */
		
		include_once 'Users.php';
		$users=new Users;
		$user_info=$users->get_user_info($info['user_id']);
		if($user_info['balance']<$cost){$this->error('bal_too_lower');return;}

		if ( $pr == 1 ) {
			$new_info = array(
				'balance'	=> $user_info['balance']-$cost,
				'spent'		=> $user_info['spent']+$cost
			);		
		} else {
			$new_info = array(
					'balance'	=> $user_info['balance'],
					'spent'		=> $user_info['spent']
			);
		}
		
		$update = array(
			'priority' => $pr,
			'status' => Ps_Statuses_ControlStatuses::getStatus($info['status'], 'priorityWrite', $pr)	
		);
				
		$users->upd_user_info($info['user_id'],$new_info);
		$ankets->upd_anket($id,$update);
		$this->_redirect('/cabinet' . $this->addGetParamsToUri() );
		die;
	}
	
	public function priorityWriteSalonAction(){
		$this->_helper->viewRenderer->setScriptAction('error');		
		$id=intval(substr($this->_getParam('n'),0,32));
		$pr=intval(substr($this->_getParam('pr'),0,32));
		include_once 'Salons.php';
		$salons=new Salons();
		$info=$salons->get_salon($id);
		
		
		/* get cost for anket's type */		
		include_once 'FinanceSettings.php';
		$settings = new FinanceSettings;
		$settings = $settings->get();
		
		$cost = $settings['salon_hour_price'] / 24;
	   /* end of get cost for ankets's type */
		
		include_once 'Users.php';
		$users=new Users;
		$user_info=$users->get_user_info($info['user_id']);
		if($user_info['balance']<$cost){$this->error('bal_too_lower');return;}

		if ( $pr == 1 ) {
			$new_info = array(
				'balance'	=> $user_info['balance']-$cost,
				'spent'		=> $user_info['spent']+$cost
			);		
		} else {
			$new_info = array(
					'balance'	=> $user_info['balance'],
					'spent'		=> $user_info['spent']
			);
		}
		$users->upd_user_info($info['user_id'],$new_info);
		
		$update = array(
			'priority' => $pr,
			'status' => Ps_Statuses_ControlStatuses::getStatus($info['status'], 'priorityWriteSalon', $pr)	
		);
		
		$salons->upd_salon($id,$update);
		$this->_redirect('/cabinet/salons' .  $this->addGetParamsToUri());
		die;
	}
	
	public function toModerationAction(){
		$this->_helper->viewRenderer->setScriptAction('error');
		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		if($info['status']<30){$status=30;}
		elseif($info['status']==40){$status=50;}
		else{$status=30;}
		$ankets->upd_anket($id,array('status'=>$status));
		$this->_redirect('/cabinet');
		die;
	}
	
	public function articlesAction(){
		$page=$this->_hasParam('p')?intval($this->_getParam('p')):1;
		include_once 'Articles.php';
		$articles=new Articles();
		$this->view->articles=$articles->get_list($page);
	}
	
	public function artAddFormAction(){
		if(!$this->user_admin){$this->_redirect('/cabinet');die;}
	}
	
	public function artAddAction(){
		if(!$this->user_admin){$this->_redirect('/cabinet');die;}
		$info=array(
                    'title'	=> $this->check_text_adm($this->_getParam('title')),
                    'title_meta'=> $this->check_text_adm($this->_getParam('title_meta')),
                    'keywords'	=> $this->check_text_adm($this->_getParam('keywords')),
                    'descriptions'=> $this->check_text_adm($this->_getParam('descriptions')),
                    'text'	=> $this->check_text_adm($this->_getParam('text'))
		);
		include_once 'Articles.php';
		$articles=new Articles();
		$articles->add_art($info);
		$this->_redirect('/cabinet/articles');die;
	}
	
	public function artDelReqAction(){
		if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Articles.php';
		$articles=new Articles();
		$info=$articles->get_art($id);
		if(!$info){
			$this->_helper->viewRenderer->setScriptAction('error');
			$this->error('no_article');
			return;			
		}
		$info['title']=nl2br(stripslashes($info['title']));
		$this->view->info=$info;
	}
	
	public function artDelAction(){
		if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Articles.php';
		$articles=new Articles();
		$articles->del_art($id);
		$this->_redirect('/cabinet/articles');die;
	}
	
	public function artEditFormAction(){
		if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Articles.php';
		$articles=new Articles();
		$info=$articles->get_art($id);
		if(!$info){
			$this->_helper->viewRenderer->setScriptAction('error');
			$this->error('no_article');
			return;			
		}
		$this->view->info=array(
                    'title'	=> stripslashes($info['title']),
                    'title_meta'=> stripslashes($info['title_meta']),
                    'keywords'	=> stripslashes($info['keywords']),
                    'descriptions'=> stripslashes($info['descriptions']),
                    'text'	=> stripslashes($info['text'])
		);
		$this->_helper->viewRenderer->setScriptAction('art-add-form');
	}
	
	public function artEditAction(){
		if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$id=intval(substr($this->_getParam('n'),0,32));
		$info=array(
			'title'	=> $this->check_text_adm($this->_getParam('title')),
                        'title_meta'	=> $this->check_text_adm($this->_getParam('title_meta')),
                        'keywords'	=> $this->check_text_adm($this->_getParam('keywords')),
                        'descriptions'	=> $this->check_text_adm($this->_getParam('descriptions')),
			'text'	=> $this->check_text_adm($this->_getParam('text'))
		);
		include_once 'Articles.php';
		$articles=new Articles();
		$articles->upd_art($id,$info);
		$this->_redirect('/cabinet/articles');die;
	}
	
	public function commsListAction(){
		$commpage=$this->_hasParam('cp')?intval($this->_getParam('cp')):1;
		$id=$this->_hasParam('n')?intval($this->_getParam('n')):false;
		if(!$id){$this->error('request_error');return;}		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($id);
		if(!$info){$this->error('no_ank');return;}
		if($info['user_id']!=$this->user_id && !$this->user_admin && !$this->user_moder){$this->error('no_rights_ank');return;}
		$this->view->info=$info;		
		include_once 'Comments.php';
		$comments=new Comments();
		$this->view->comments=$comments->get_list($commpage,$id);
		$this->view->adm_flag=self::COM_ADM;
	}
	
	public function commAddAction(){
		if(!$this->_hasParam('n')){$this->_redirect('/');die;}
		$ank_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		if(!$ank_id){$this->_redirect('/');die;}
		$this->_helper->viewRenderer->setScriptAction('error');
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($ank_id);
		if(!$info){$this->error('no_ank');return;}
		if($this->_hasParam('name')){$name=$this->check_text(substr($this->_getParam('name'),0,255));}
		else{$this->error('request_error');return;}
		if($this->_hasParam('text')){$text=$this->check_text(substr($this->_getParam('text'),0,4096));}
		else{$this->error('request_error');return;}
		if(empty($name)){$this->error('no_comm_name');}
		if(empty($text)){$this->error('no_comm_text');}
		$this->_helper->viewRenderer->setScriptAction('comms-list');
		if(empty($this->view->errors)){
			$flags=0;
			if($this->_hasParam('admin_only')){$flags+=1<<self::COM_ADM;}
			include_once 'Comments.php';
			$comments=new Comments();
			$info=array(
				'owner_id'	=> $ank_id,
				'user_id'	=> $info['user_id'],
				'name'		=> se,
				'text'		=> $text,
				'flags'		=> $flags
			);
			$comments->add($info);
                        $ankets->incr($ank_id);
		}
		else{$this->view->comm_info=array('name'=>$name,'text'=>$text);}
		$this->commsListAction();
	}
	
	public function commDelReqAction(){
		$this->view->comm_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
	}
	
	public function commDelAction(){
		$this->_helper->viewRenderer->setScriptAction('error');
		$commpage=$this->_hasParam('cp')?intval(substr($this->_getParam('cp'),0,32)):1;
		$comm_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		include_once 'Comments.php';
		$comments=new Comments();
		$info=$comments->get($comm_id);
		if($info['user_id']!=$this->user_id && !$this->user_admin){$this->error('no_rights_ank');return;}
		$comments->del($comm_id);
                include_once 'Ankets.php';
                $ankets=new Ankets();
		if ($info=$ankets->get_anket($ank_id)){
                    $ankets->decr($ank_id);
                }
		$this->_redirect('/cabinet/comms-list/n/'.$info['owner_id'].'/cp/'.$commpage);die;
	}		
	
	public function addShopFormAction(){
		$this->get_config_info();
		$this->view->action='add';
	}
	
	protected function get_salon_info(){
		$this->view->types = $this->content->types_of_salon->toArray();
		$this->view->district_list = $this->content->district_spb->toArray();
		$this->view->metro_list = $this->content->metro_spb->toArray();	
		$this->view->services=$this->content->srv_salon->toArray();
	}
	
	protected function get_config_info(){
		include_once "Salons.php";
		$salons = new Salons();
		$salons = $salons->get_user_salons($this->user_id);	
		$types = array();	
		foreach( $salons as $s)
		{
			$types[$s['id']] = $s['name']; 
		}	
		$this->view->types_menu = $this->content->types_menu->toArray();
		$this->view->types = $types;		
		$this->view->places=$this->content->places->toArray();
		$this->view->services=$this->content->srv->toArray();
		$this->view->performer=$this->content->performer->toArray();
		$this->view->exotics = $this->content->exotics->toArray();
		$this->view->breast=array();
		$br_sizes=explode('-',$this->content->breast);
		for($i=$br_sizes[0];$i<=$br_sizes[1];$i++){$this->view->breast[]=$i;}
		$this->view->metro_list=$this->content->metro_spb->toArray();
		$this->view->district_list=$this->content->district_spb->toArray();
		$this->view->metro=json_encode(array('m1'=>$this->content->metro_msk->toArray(),'m2'=>$this->content->metro_spb->toArray()));
		$this->view->district=json_encode(array('d2'=>$this->content->district_spb->toArray()));		
	}
	
		protected function get_menu_items_info_from_form(array $current = array()){
			$params = $this->_getAllParams();
			$info = array();
			$fields = array(
				'menu_title', 'title','uri','title_meta','keywords','descriptions',
				'text','text_left','text_right','text_footer',
				'type','performer', 'priority');
			
			foreach ($fields as $field){
				if (strpos($field, '[]') != 0){
					$field = rtrim($field,'[]');
					$info[$field] = serialize($params[$field]);
				}else{
					$info[$field] = $this->check_text_adm($params[$field]);
				}
			}
			
			if (empty($current['objMenuItems']) || !$current['objMenuItems'] instanceof MenuItems) throw new Zend_Exception(__METHOD__.'Exception: $current[\'objMenuItems\'] must be a member of Sections model!');
			$objMenuItems = $current['objMenuItems'];
			
			if ( empty($params['uri']) || $data = $objMenuItems->check_uri($params['uri'])){
				$info['failed']['uri'] = true;
				$id=intval(substr($this->_getParam('n'),0,32));
				if ($id > 0 && $data['id'] == $id && $data['uri'] == $params['uri']){
				
					unset($info['failed']['uri']);
				}
			}
			
			if ( empty($params['title']) ) {
				$info['failed']['title'] = true;
			}
			
			if( isset($info['failed']) ){
				$this->error('requireds');return $info;
			}
			return $info;						
		}
        
        protected function get_section_info_from_form(array $current=array()){
            
            $params=$this->_getAllParams();
            $info = array();
            $fields = array(
                'title','uri','title_meta','keywords','descriptions',
                'text','text_left','text_right','text_footer',
                'age','type','city','district','exotics','metro[]','phone','height','weight',
                'performer','breast','place','price_1h_ap','price_2h_ap',
                'price_n_ap','price_2h_ex','timestamp','photolist','videolist', 
                'status','with_videos', 'verified',	'with_comments');
            foreach ($fields as $field){
                if (strpos($field, '[]') != 0){
                    $field = rtrim($field,'[]');
                    $info[$field] = serialize($params[$field]);
                }else{
                    $info[$field] = $this->check_text_adm($params[$field]);
                    //$info[$field] = htmlentities($params[$field]);
                }
            }
                          
            if (empty($current['objSections']) || !$current['objSections'] instanceof Sections) throw new Zend_Exception(__METHOD__.'Exception: $current[\'objSections\'] must be a member of Sections model!');
            $objSections = $current['objSections'];
            if (empty($params['uri']) || $data = $objSections->check_uri($params['uri'])){
                $info['failed']['uri'] = true;
                // check for unset failed uri if section edit
                $id=intval(substr($this->_getParam('n'),0,32));
                if ($id > 0 && $data['id'] == $id && $data['uri'] == $params['uri']){
            
                    unset($info['failed']['uri']);
                }
                    
                #$this->_helper->viewRenderer->setScriptAction('section-add-form');
                #$this->error('section_bad_or_non_unique_uri');
                #if(isset($info['failed'])){$this->error('requireds');return $info;}
                #$this->error('requireds');
   
                }
            if (empty($params['title'])) {
                $info['failed']['title'] = true;
            }  
            
            $services = $this->content->srv->toArray(); // services math
            foreach($services as $srv => $list){
                    $serv = 0;
                    foreach($list as $key => $null){
                            if(isset($params[$srv.'_'.$key])){$serv += 1 << $key;}
                    }
                    $info['srv_'.$srv] = $serv;
            }
            //if( !$info['srv_main'] && !$info['srv_add'] && !$info['srv_strip'] && !$info['srv_extr'] && !$info['srv_bdsm'] && !$info['srv_mass'])
            //{
            // 	$info['failed']['services'] = true;
            //}                
            
            if(isset($info['failed'])){
            	$this->error('requireds');
            	return $info;
            }
            return $info;
        }
        
        protected function menu_items_return_to_edit($info){
        	
        }
        
        protected function section_return_to_edit($info){
			$this->get_config_info();
			$services=$this->content->srv->toArray();
			$this->view->srv_active=array();
			foreach($services as $srv=>$list){
				foreach($list as $key=>$null){
					$this->view->srv_active[$srv.'_'.$key]=$info['srv'.'_'.$srv] & 1 << $key ? true : false;
				}
			}
			if( $info['city']==1 ){
				$this->view->metro_list=$this->content->metro_msk->toArray();			
			}
			elseif( $info['city']==2 ){
				$this->view->metro_list=$this->content->metro_spb->toArray();
				$this->view->district_list=$this->content->district_spb->toArray();
			}
			$this->view->metro_list_checkbox = $this->metro_for_checkbox($this->view->metro_list,  unserialize($info['metro']));
            $this->_helper->viewRenderer->setScriptAction('add-section-form');
			$this->view->info = $info;
       		$this->view->info = $this->arr_htmlentities($this->view->info);
       	   //$this->view->info = $this->arr_stripslashes($this->view->info);
	  }
	
	public function bannersAction(){
	    $this->get_config_info();
		//if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$id=intval(substr($this->_getParam('n'),0,32));
		$page=intval(substr($this->_getParam('p'),0,3));
		if (!$id) $id = 1;
		if (!$page) $page = 1;
		if( !$this->user_admin && !$this->user_tech ){$this->error('no_rights_ank');return;}
		include_once 'Banners.php';
		$mBanners=new Banners();
		$this->view->paginator=$mBanners->get_list($page);
		
		
		$photos_num=$this->config->limit->max_photos;
		
		
		$this->view->banners_path;
	}

	public function addBannerAction(){
		//if(!$this->_hasParam('n')){$this->error('request_error');return;}
//		$id=intval(substr($this->_getParam('n'),0,32));
//		if (!$id) $id = 1;
//print '<pre>'; print_r($_FILES);print_r($_POST);exit;
		$err_nm=0;
		$files=0;
		include_once 'Banners.php';
		$mBanners=new Banners();
		//$info=$mBanners->get($id);

		foreach($_FILES as $file){if($file['error']){$err_nm++;}$files++;}
		if($err_nm<count($_FILES) || $this->_hasParam('preview')){
			if( !$this->user_admin && !$this->user_tech ){$this->error('no_rights_ank');return;}
			$dir=$this->config->path->banners;//.'/'.$info['user_id'];
			if(!is_dir($dir)){mkdir($dir);}//$this->config->limit->max_photos
			//if(empty($info['items_list'])){$photos=array_fill(0,count($_FILES),false);}
			//else{$photos=unserialize($info['items_list']);}
			include_once 'SimpleImage.php';
			$cnt=-1;
			$fvarnamelen = strlen('file_');//file var name in bannes.phtml
			foreach($_FILES as $fkey=>$file){
				//$cnt++
				$cnt=substr($fkey,$fvarnamelen);
				if($file['error'] || $file['size']>$this->config->limit->max_photo_size){continue;}
				$path_tmp=$dir.'/'.$file['name'];
				move_uploaded_file($file['tmp_name'],$path_tmp);
				$hash=md5_file($path_tmp);
				$ext=substr(strrchr($file['name'], '.'), 1);
				$path=$dir.'/'.$hash.'.'.$ext;
				$simage=new SimpleImage();
				$simage->load($path_tmp);
				if($simage->getWidth()>$this->config->limit->max_photo_width){
					$simage->resizeToWidth($this->config->limit->max_photo_width);
				}
				$simage->save($path);
				$conf_aspect=$this->config->thumbnale_width/$this->config->thumbnale_height;
				$aspect=$simage->getWidth()/$simage->getHeight();
				if($aspect>$conf_aspect){$simage->resizeToHeight($this->config->thumbnale_height);}
				else{$simage->resizeToWidth($this->config->thumbnale_width);}
				$simage->save($dir.'/th_'.$hash.'.'.$ext);
				unlink($path_tmp);
				if(isset($photos[$cnt]) && $photos[$cnt] && $photos[$cnt]!=$hash.'.'.$ext){
					unlink($dir.'/'.$photos[$cnt]);
					unlink($dir.'/th_'.$photos[$cnt]);
				}
				$photos[$cnt]=$hash.'.'.$ext;
			}
			if($this->_hasParam('preview')){
				$preview=intval(substr($this->_getParam('preview'),0,4));
				if($preview>=0 && $preview<=$this->config->limit->max_photos){$photos['preview']=$preview;}
			}
			//$status=false;
			
			//$new_info=array('items_list'=>serialize($photos));
			//if($info['status']==60 || $info['status']==40){$new_info['status']=50;}
			//elseif($info['status']==0 && $status){$new_info['status']=20;}
		}
		//print'<pre>';print_r($_POST);
		if(isset($_POST['title']))$cnt = count($_POST['title']);
		    else $cnt=1;
			foreach($_POST['title'] as $key=>$post){
			    $cnt--;
			    $mData['user_id'] = $this->user_id;
			    if(isset ($photos[$key])&&$photos[$key])$mData['fname']=$photos[$key];
			    if(isset($_POST['url'][$key])&&$_POST['url'][$key])$mData['url']=substr($_POST['url'][$key],0,(int)$this->config->limit->banners->url);
				else $mData['url'] = '';
			    if(isset($_POST['type'][$key])&&$_POST['type'][$key])$mData['type']=substr($_POST['type'][$key],0,2);
				else $mData['type']=0;
			    $mData['priority']=0;
			    if(isset($_POST['title'][$key])&&$_POST['title'][$key])$mData['title']=substr($_POST['title'][$key],0,(int)$this->config->limit->banners->title);
				else $mData['title']='';
			    $mData['description']='';
			    //upd() глючит и постоянно возвращает 0, поэтому простой способ контроля
			    //вставки-обновления не прокатывает ((
			    //if(!$mBanners->upd($key, $mData))$mBanners->add($mData);
//print_r($mData);
			    if($cnt){$mBanners->upd($key, $mData);
				}elseif(isset($mData['fname'])){
				    $mBanners->add($mData);
				}
			}//exit;
		$redirect='/cabinet/banners';
		if($this->_hasParam('to_ank_edit')){$redirect.='/edit-ank-form/n/'.$id;}
		elseif($this->_hasParam('to_check')){$redirect.='/to-moderation/n/'.$id;}
		elseif($this->_hasParam('to_check_photo')){$redirect.='/check-photo/n/'.$id;}
		$this->_redirect($redirect);
		die;
	}

	public function delBannerAction(){
		if(!$this->_hasParam('f')){$this->error('request_error');return;}
		$fid=intval(substr($this->_getParam('f'),0,32));
		include_once 'Banners.php';
		$mBanners=new Banners();
		$info=$mBanners->get($fid);
		if( !$this->user_admin && !$this->user_tech ){$this->error('no_rights_ank');return;}
		$dir=$this->config->path->banners;//.'/'.$info['user_id'];
		if(empty($info['fname'])){
		    unlink($dir.'/'.$info['fname']);
		    unlink($dir.'/th_'.$info['fname']);
		}
		$mBanners->del($fid);
		$this->_redirect('/cabinet/banners');
		die;
	} 
        
	public function menuItemsAction(){
    	$this->get_config_info();      
    	$id = intval(substr($this->_getParam('n'),0,32));
    	$page = intval(substr($this->_getParam('p'),0,3));
    	if ( !$id ) $id = 1;
    	if ( !$page ) $page = 1;
    	if( !$this->user_admin && !$this->user_tech ) { $this->error('no_rights_ank'); return; } 
    	include_once 'MenuItems.php';
    	$mMenuItems = new MenuItems();
    	$this->view->items = $mMenuItems->get_list($page); 	
    }
    
    public function menuItemsAddAction(){
    	if( !$this->user_admin && !$this->user_tech ){
    		$this->_redirect('/cabinet');die;
    	}
    	include_once 'MenuItems.php';
    	$mMenuItems = new MenuItems();
    	if(!$info = $this->get_menu_items_info_from_form(array('objMenuItems'=>$mMenuItems))){
    		return;
    	}
    	if($info['failed']){
    		$this->_helper->viewRenderer->setScriptAction('menu-items-add-form');
    		$this->view->action='add';
    		return;
    	}
    	unset($info['failed']);
    	if (empty($info['title_meta'])){
    		$info['title_meta'] = $info['title'];
    	}
    
    	$mMenuItems->add($info);
    	$this->_redirect('/cabinet/menu-items');die;
    } 

    public function menuItemsDelAction(){
    	if( !$this->user_admin && !$this->user_tech ){
    		$this->_redirect('/cabinet');die;
    	}
    	$id=intval(substr($this->_getParam('n'),0,32));
    	include_once 'MenuItems.php';
    	$mMenuItems = new MenuItems();
    	$mMenuItems->del($id);
    	$this->_redirect('/cabinet/menu-items');die;
    }
    
    public function menuItemsEditAction(){
    	if( !$this->user_admin && !$this->user_tech ){
    		$this->_redirect('/cabinet');die;
    	}
    	$id = intval(substr($this->_getParam('n'),0,32));
    	
    	include_once 'MenuItems.php';
    	$mMenuItems = new MenuItems();
    	$current['objMenuItems'] = $mMenuItems;
    	$info = $this->get_menu_items_info_from_form($current);
    	if($info['failed']){
    		//$this->section_return_to_edit($info);
    		$this->_helper->viewRenderer->setScriptAction('menu-items-add-form');
    		$this->view->action='edit';
    		return;
    	}
    	unset($info['failed']);
    	$mMenuItems->upd($id,$info);
    	if(!$info){
    		$this->_helper->viewRenderer->setScriptAction('error');
    		$this->error('no_menu_item');
    		return;
    	}
    	$this->_redirect('/cabinet/menu-items');die;
    }
    
    public function menuItemsAddFormAction(){
    	$this->get_config_info();
    	
    	$this->_helper->viewRenderer->setScriptAction('menu-items-add-form');
    	$this->view->action = 'add';
    }
    
    public function menuItemsEditFormAction(){
    	if( !$this->user_admin && !$this->user_tech ){
    		$this->_redirect('/cabinet');die;
    	}
    	if(!$this->_hasParam('n')){
    		$this->error('request_error');return;
    	}
    	$this->get_config_info();
    	include_once 'MenuItems.php';
    	$mMenuItems = new MenuItems();
    	$this->view->info = $info = $mMenuItems->get(intval(substr($this->_getParam('n'),0,32)));
    	if(!$info){
    		$this->error('no_menu_item');return;
    	}
    
    	$this->_helper->viewRenderer->setScriptAction('menu-items-add-form');
    	$this->view->action = 'edit';
    }
    
    public function sectionsAction(){
    	$this->get_config_info();
    	//if(!$this->_hasParam('n')){$this->error('request_error');return;}
    	$id=intval(substr($this->_getParam('n'),0,32));
    	$page=intval(substr($this->_getParam('p'),0,3));
    	if (!$id) $id = 1;
    	if (!$page) $page = 1;
    	if( !$this->user_admin && !$this->user_tech ){
    		$this->error('no_rights_ank');return;
    	}
    	include_once 'Sections.php';
    	$mSections=new Sections();
    	#$this->view->paginator=$mSections->get_list($page);
    	$this->view->items=$mSections->get_list($page);
    }
        
    public function sectionAddFormAction(){
    	$this->get_config_info();
        $info = array();
        $info['city']=2;
   		if( $info['city']==1 ){
			$this->view->metro_list=$this->content->metro_msk->toArray();			
		}
		elseif( $info['city']==2 ){
			$this->view->metro_list=$this->content->metro_spb->toArray();
			$this->view->district_list=$this->content->district_spb->toArray();
		}
            
        $this->view->metro_list_checkbox = $this->metro_for_checkbox($this->view->metro_list,  unserialize($info['metro']));
        $this->_helper->viewRenderer->setScriptAction('section-add-form');
        $this->view->action='add';
        $this->view->info = $info;
        $this->view->info = $this->arr_stripslashes($this->view->info);
        $this->view->types = $this->content->types->toArray();        
    }
        
        public function sectionAddAction(){
            if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
            include_once 'Sections.php';
            $sections=new Sections();
            if(!$info=$this->get_section_info_from_form(array('objSections'=>$sections))){return;}
            if($info['failed']){
                    $this->section_return_to_edit($info);
                    $this->_helper->viewRenderer->setScriptAction('section-add-form');
                    $this->view->action='add';
                    $this->view->types = $this->content->types->toArray();
                    return;
            }
            unset($info['failed']);
            if (empty($info['title_meta'])){
                $info['title_meta'] = $info['title'];
            }

            $sections->add($info);
            $this->_redirect('/cabinet/sections');die;
        }
        
        public function sectionEditFormAction(){
            if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
            if(!$this->_hasParam('n')){$this->error('request_error');return;}
            $this->get_config_info();
            include_once 'Sections.php';
            $sections=new Sections();
            $this->view->info = $info = $sections->get(intval(substr($this->_getParam('n'),0,32)));
            if(!$info){$this->error('no_section');return;}
            
            $this->section_return_to_edit($info);
            $this->_helper->viewRenderer->setScriptAction('section-add-form');
            $this->view->action='edit';
            $this->view->types = $this->content->types->toArray(); 
        }
        
        public function sectionEditAction(){
            if( !$this->user_admin && !$this->user_tech ){
            	$this->_redirect('/cabinet');
            	die;
            }
			$id=intval(substr($this->_getParam('n'),0,32));
                
            include_once 'Sections.php';
			$sections=new Sections();
            $current['objSections'] = $sections;
			$info = $this->get_section_info_from_form($current);
			if($info['failed']){
            	$this->section_return_to_edit($info);
                $this->_helper->viewRenderer->setScriptAction('section-add-form');
                $this->view->action='edit';
                $this->view->types = $this->content->types->toArray();
                return;
            }
            unset($info['failed']);
			$sections->upd($id,$info);
            if(!$info){
				$this->_helper->viewRenderer->setScriptAction('error');
				$this->error('no_section');
				return;			
			}
			$this->_redirect('/cabinet/sections');die;
        }
        
        public function sectionDelReqAction(){
            if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
            if(!$this->_hasParam('n')){$this->error('request_error');return;}
            $this->get_config_info();
            include_once 'Sections.php';
            $sections=new Sections();
            $this->view->info = $info = $sections->get(intval(substr($this->_getParam('n'),0,32)));
            if(!$info){$this->error('no_section');return;}
        }

        public function sectionDelAction(){
            if( !$this->user_admin && !$this->user_tech ){$this->_redirect('/cabinet');die;}
		$id=intval(substr($this->_getParam('n'),0,32));
		include_once 'Sections.php';
		$sections=new Sections();
		$sections->del($id);
		$this->_redirect('/cabinet/sections');die;
        }
        
                
        protected function metro_for_checkbox($metro_list, $sel_metro_arr){

            if (is_array($sel_metro_arr) && count($sel_metro_arr)) {
                $sel_metro_arr = array_values($sel_metro_arr);
            }else{
                $sel_metro_arr = false;
            }
            $result = array();
            foreach ($metro_list as $metro) {

                if ($sel_metro_arr && in_array($metro, $sel_metro_arr)) {
                    $result[] = array('title' => $metro, 'checked' => 'checked');
                }else{
                    $result[] = array('title' => $metro, 'checked' => '');
                }
            }
            return $result;
        }
	
	protected function check_text($text){
		return preg_replace('/[^ёЁ\d\w\s\.\-а-я+,!?%]/ui','',$text);
	}
	
	protected function check_text_adm($text){
		return addslashes($text);
	}
        
        protected function arr_stripslashes($info){
            foreach ($info as $key => $value) {
                $info[$key] = stripslashes($info[$key]);
            }
            return $info;
        }
        
        protected function arr_htmlentities($info){
            foreach ($info as $key => $value) {
                $info[$key] = $this->view->TextToFormEdit($info[$key]);
            }
            return $info;
        }

        protected function error($error){
		$this->view->errors[]=$this->content->messages->{$error};
	}

	public function successAction()
	{
		$LMI_PAYMENT_NO = $this->_getParam('LMI_PAYMENT_NO');
		$payment = new Model_Payment();
	
		/* set amount */
		$this->view->amount = $payment->getAmountByPaymentNo($LMI_PAYMENT_NO);
	
		/* set balance */
		$user_id = $payment->getUserIdByNo($LMI_PAYMENT_NO);
		$user = new Model_UsersTest();
		$this->view->balance = $user->getBalance($user_id);
	}

	public function failAction() {
		$LMI_PAYMENT_NO = $this->_getParam('LMI_PAYMENT_NO');

		$payment = new Model_Payment();
		$payment->update(array('state' => false), "number = " . $LMI_PAYMENT_NO );
	}

	/* return get params in uri */
	protected function addGetParamsToUri () {
		$uri = "?";	
		
		foreach ( $_GET as $k => $v) {			
			$uri .=  $k . "=" . $v . "&";
		}
		
		return $uri;
	}

	/* prepare phone */
	protected function preparePhone( $phone ) {
		$phone =  preg_replace('/\D/', '', $phone);
		
		if ( strlen($phone) > 10 ) {
			$phone = substr($phone, -10);
		}
		
		return  preg_replace( '/(\d{3})(\d{7})/', '$1-$2', $phone );
	}

	protected function calculateBalance () {
		$users = new Model_UsersTest();
		return $users->getBalance($this->user_id);
	}

	protected function calculateSpending () {
		include_once 'FinanceSettings.php';
		$finance_settings = new FinanceSettings();
		$ankets = new Model_AnketsTest();
		$salons = new Model_SalonsTest();

		$priority_ankets = $ankets->getPriorityAnketsByUserId($this->user_id);
		$prices = $finance_settings->get();
		$spend = 0;
		foreach ( $priority_ankets as $pa ) {
			if ( $pa['count'] ) {
				$spend += $this->calculateDiscountPrice($pa['performer'], $pa['count'], $prices);
			}
		}

		$priority_salons = $salons->getPrioritySalonsByUserId($this->user_id);
		if ( $priority_salons['count'] ) {
			$spend += $this->calculateDiscountPrice(8, $priority_salons['count'], $prices);
		}

		return $spend;
	}

	protected function calculateDiscountPrice ( $performer, $count, $prices) {
		if ( $count <= 0 ) {
			return 0;
		}

		$range = array(
			"0" => array(1,2),
			"1" => array(3,4),
			"2" => array(5,6,7,8,9),
			"3" => array(10,11,12,13,14)
		);

		foreach ($range as $k => $v) {
			if ( in_array($count, $v) ) {
				$range = (int)$k; break;
			}
		}

		if ( is_array($range)) {
			$range = 4;
		}

		$spend = 0;
		if ( $performer == 1 ){
			$spend += ($prices['girl_hour_price'] - $range * $prices['girl_hour_discount']) * $count;
		} elseif ( $performer == 2 ) {
			$spend += ($prices['lesb_hour_price'] - $range * $prices['lesb_hour_discount']) * $count;
		} elseif ( $performer == 3 ) {
			$spend += ($prices['mass_hour_price'] - $range * $prices['mass_hour_discount']) * $count;
		} elseif ( $performer == 4 ) {
			$spend += ($prices['bdsm_hour_price'] - $range * $prices['bdsm_hour_discount']) * $count;
		} elseif ( $performer == 5 ) {
			$spend += ($prices['pair_hour_price'] - $range * $prices['pair_hour_discount']) * $count;
		} elseif ( $performer == 6 ) {
			$spend += ($prices['man_hour_price'] - $range * $prices['man_hour_discount']) * $count;
		} elseif ( $performer == 7 ) {
			$spend += ($prices['trans_hour_price'] - $range * $prices['trans_hour_discount']) * $count;
		} elseif ( $performer == 8 ) {
			$spend += ($prices['salon_hour_price'] - $range * $prices['salon_hour_discount']) * $count;
		}

		return $spend;
	}

	protected function calculateForecast ( $balance, $spend ) {
		if ( $spend <= 0 ) {
			return false;
		}

		return date('d.m.Y в H:i', ( $balance / $spend ) * 86400 + time());
	}

	protected function getUsersSalons() {
		require_once 'Salons.php';
		$salons = new Salons();
		$salons = $salons->get_user_salons($this->user_id);
		$result = array('Нет/индивидуалка');
		foreach( $salons as $s)
		{
			$result[$s['id']] = $s['name'];
		}
		return $result;
	}

	protected function getParam( $name ) {
		if ( !$this->_hasParam($name) ) {
			return;
		}

		return $this->_getParam($name);
	}

	/*
	 * Check user permissions
	 */
	protected function hasRights( $type, $compare = null ) {
		if ( is_array($compare) && count($compare) == 2 ) {
			if ( $compare[0] != $compare[1] && !$this->hasType($type)) {
				$this->_redirect('/cabinet');die;
			} 

			return;
		} 

		$this->hasType($type);
	}

	protected function hasType($type){
		if ( is_array( $type ) ) {
			$has_right = false;
		
			foreach ( $type as $t ) {
				if ( $this->$t ) {
					$has_right = true; break;
				}
			}
		
			if ( !$has_right ) {
				$this->_redirect('/cabinet');die;
			}
		
			return true;
		}
		
		if( !$this->$type ) {
			$this->_redirect('/cabinet');die;
		} else  {
			return true;
		}
	}
}
