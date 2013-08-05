<?php

class ShowCabAuth_Exception extends Zend_Exception {}
class SendConfirmEmail_Exception extends Zend_Exception {}

class AuthController extends Zend_Controller_Action{
	protected $config,$content;
	public function init(){
		$this->config=Zend_Registry::get('config');
		$this->content=Zend_Registry::get('content');
		$this->view->cities=$this->content->cities->toArray();
		$this->view->services=$this->content->srv->toArray();
		$this->view->srv_short=$this->content->srv_short->toArray();
		$this->view->srv_short_list=true;
		$this->view->errors=array();
		include_once 'Meta.php';
		$meta=new Meta;
		$this->view->meta=$meta->get();
	}
	
	public function indexAction() {
		if(!$this->_request->isPost()){return;}
		$username = $this->_request->getPost('username');
		$password = $this->_request->getPost('password');
		if( empty($username) || empty($password) ){
			return;
		} 
		
		$strip_tags          = new Zend_Filter_StripTags();
		$string_trim_filter  = new Zend_Filter_StringTrim();
		
		$filters = array($strip_tags, $string_trim_filter);		
		foreach ( $filters as $filter ) {
			$username = $filter->filter($username);
			$password = $filter->filter($password);
		}	
		
		$aa=new Zend_Auth_Adapter_DbTable(Zend_Registry::get('db'));		
		$aa	->setTableName('users')
			->setIdentityColumn('user_hash')
			->setCredentialColumn('user_pass')
			->setIdentity(md5($username))
			->setCredential(md5($password));
		$auth=Zend_Auth::getInstance($aa);
		$result=$auth->authenticate($aa);
		if($result->isValid() ) {
		    $data=$aa->getResultRowObject(array('id','user_login','flags', 'status'));
		    
		    if ( !$data->status ) {
		    	$this->view->flag = 'banned';
		    	return;
		    } else {		    
		        $auth->getStorage()->write($data);
		    
		   	    if ( $this->_hasParam('url') ) {
		    		$this->_redirect( $this->_getParam('url') );
		    	} else {		    
		   	    	$this->_redirect('/');
		    	}
		    	die;
		    }
		}
		
		$this->view->flag = 'error_credentials';
	}
	
	public function logoutAction() {
		/*
		 * For simple and advertise users
		 */		
		$auth = Zend_Auth::getInstance()->getIdentity();		
		$usettings = new Model_USettings();
		
		if ( $auth->id ) {		
			if ( $usettings->getConfig( $auth->id, 'show_user_cab' ) == 'true' ) {
				$result = $usettings->setConfig( $auth->id, "show_user_cab" , "false");
			
				if ( !$result ) {
					throw new ShowCabAuth_Exception();
				}
			}	 
		}
		
		Zend_Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
        die;
	}
	
	public function registrationAction(){
		$this->captcha_add();
	}
	
	public function regWriteAction() {
		$this->captcha_check();
		if($this->_hasParam('name')){$name=$this->check_text(substr($this->_getParam('name'),0,16));}
		else{$this->error('request_error');return;}
		if($this->_hasParam('pass')){$pass=$this->check_text(substr($this->_getParam('pass'),0,16));}
		else{$this->error('request_error');return;}
		if($this->_hasParam('pass2')){$pass2=$this->check_text(substr($this->_getParam('pass2'),0,16));}
		else{$this->error('request_error');return;}
		if($this->_hasParam('mail')){$mail=$this->check_mail(substr($this->_getParam('mail'),0,64));}
		else{$this->error('request_error');return;}
		if(strlen($name)<4){$this->error('login_short');}
		if(strlen($pass)<6){$this->error('pass_short');}
		if($pass!=$pass2){$this->error('pass_error');}
		if(!$this->check_mail_correct($mail)){$this->error('mail_error');$mail_error=true;}
		else{$mail_error=false;}
		$md=md5($name);
		include_once 'UsersUnconf.php';
		include_once 'Users.php';
		$users_uc=new UsersUnconf();
		$users=new Users();
		if($users->user_check($md) || $users_uc->user_check($md)){$this->error('user_already');}
		if(!$mail_error && ($users->user_check(false,$mail) || $users_uc->user_check(false,$mail))){$this->error('mail_already');}
		if(!empty($this->view->errors)){
			$this->_helper->viewRenderer->setScriptAction('registration');
			$this->captcha_add();
			$this->view->info=array('name'=>$name,'mail'=>$mail);
			return;
		}
		$confirm=$this->generate_confirm();
		$info=array(
			'user_login'	=> $name,
			'user_pass'		=> md5($pass),
			'user_hash'		=> $md,
			'mail'			=> $mail,
			'confirm'		=> $confirm
		);
		$users_uc->user_add($info);
		$link='http://'.$this->config->domen.'/auth/reg-confirm/id/'.$confirm;
		$vars=array('%NAME%','%PASSWORD%','%LINK%');
		$replace=array($name,$pass,'<a href="'.$link.'">'.$link.'</a>');
		$message='<p>'.str_replace($vars,$replace,$this->content->mail->message->reg_conf).'</p>';
		$this->send_mail($mail,$this->content->mail->subj->reg_conf,$message,$this->content->mail->from->reg_conf);
	}
	
	public function regConfirmAction(){
		if($this->_hasParam('id')){$id=$this->check_text(substr($this->_getParam('id'),0,32));}
		else{$this->error('request_error');return;}
		include_once 'UsersUnconf.php';
		include_once 'Users.php';
		$users_uc=new UsersUnconf();
		$users = new Model_UsersTest();
		$info=$users_uc->get_confirm($id);
		if(!$info){$this->error('confirm_error');return;}
		$users_uc->user_del($info['id']);
		unset($info['id'],$info['confirm']);
		$info+=array(
			'balance'		=> 0,
			'consumption'	=> 0,
			'ankets'		=> 0,
			'spent'			=> 0,
			'banners'		=> 0,
			'salons'		=> 0,
			'shops'			=> 0,
			'status'		=> 1,
		);
		$id = $users->insert($info);	

		$user_config = new Model_UsersConfig();
		$user_config->insert(array(
			"balance"    => 1,
			"comments"   => 1,
			"moderation" => 1,
			"messages"   => 1,
			"days_info"  => 3,
			"user_id"    => $id,
			"news"       => 1
		));
	}
	public function recoveryAction(){
		$this->captcha_add();
	}
	public function recoveryReqAction(){
		$this->captcha_check();
		if($this->_hasParam('name')){$name=$this->check_text(substr($this->_getParam('name'),0,16));}
		else{$this->error('request_error');return;}
		if($this->_hasParam('mail')){$mail=$this->check_mail(substr($this->_getParam('mail'),0,64));}
		else{$this->error('request_error');return;}
		if(!empty($mail) && !$this->check_mail_correct($mail)){$this->error('mail_error');}
		include_once 'Users.php';
		$users=new Users();
		$info=$users->user_check(md5($name),$mail);
		if(!$info){$this->error('no_user');return;}
		$confirm=$this->generate_confirm();
		$users->user_recovery_add($info['id'],$confirm);
		$link='http://'.$this->config->domen.'/auth/recovery-complete/id/'.$confirm;
		$message='<p>'.$this->content->mail->message->recovery_req.'</p><br /><a href="'.$link.'">'.$link.'</a>';
		$this->send_mail($info['mail'],$this->content->mail->subj->recovery_req,$message,$this->content->mail->from->recovery_req);
	}
	public function recoveryCompleteAction(){
		if($this->_hasParam('id')){$id=$this->check_text(substr($this->_getParam('id'),0,32));}
		else{$this->error('request_error');return;}
		include_once 'Users.php';
		$users=new Users();
		$info=$users->user_check_confirm($id);
		if(!$info){$this->error('recovery_error');return;}
		$new_pass=$this->generate_password();
		$users->user_change_password($info['id'],md5($new_pass));
		$message='<p>'.$this->content->mail->message->new_pass.'</p><br />';
		$message.='<p>Login: '.$info['user_login'].'</p><p>Password: '.$new_pass.'</p>';
		$this->send_mail($info['mail'],$this->content->mail->subj->new_pass,$message,$this->content->mail->from->new_pass);
	}
	public function captchaRefreshAction(){
		$this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        if($this->_hasParam('id')){
        	$cap_id=$this->check_text(substr($this->_getParam('id'),0,32));
        	$img_path=$this->config->path->captcha.'/'.$cap_id.'.png';
			if(is_file($img_path)){unlink($img_path);}
        }
        echo $this->captcha_add();
	}
	protected function check_text($text){
		return mysql_escape_string(preg_replace('/[^\d\w]/ui','',$text));
	}
	protected function check_mail($text){
		return mysql_escape_string(preg_replace('/[^\d\w\.\@_-]/ui','',$text));
	}
	protected function check_mail_correct($email){
		if(preg_match('/^[a-z0-9&\'\.\-_\+]+@[a-z0-9\-]+\.([a-z0-9\-]+\.)*+[a-z]{2}/is',$email)){return true;}
		return false;
	}
	protected function error($error){
		$this->view->errors[]=$this->content->messages->{$error};
	}
	protected function generate_password(){
		return substr(md5(rand(1,1000).microtime(true)),0,8);
	}
	protected function generate_confirm(){
		return md5(rand(1,1000).microtime(true));
	}
	protected function send_mail($to,$subj,$message,$from=null){
		$message='<html><head></head><body>'.nl2br($message).'</body></html>';
		$header="Content-type: text/html; charset=utf-8 \r\n";
		if($from){$header.="From: ".$from." \r\n";}
		$result = mail($to,$subj,$message,$header);
		if ( !$result ) {
			throw new SendConfirmEmail_Exception();
		}
	}
	protected function captcha_add(){
		$captcha=new Zend_Captcha_Image();
		$captcha
			->setImgDir($this->config->path->captcha)
			->setFont($this->config->font->captcha)
			->setWordlen(4)
			->setFontSize(21)
			->setDotNoiseLevel(10)
			->setLineNoiseLevel(2)
			->generate();
		$this->view->captcha=$captcha->getId();
		return $this->view->captcha;
	}
	protected function captcha_check(){
		if($this->_hasParam('captcha')){$cap=$this->check_text(substr($this->_getParam('captcha'),0,16));}
		else{$this->error('request_error');return false;}
		if($this->_hasParam('id')){$cap_id=$this->check_text(substr($this->_getParam('id'),0,32));}
		else{$this->error('request_error');return false;}
		$img_path=$this->config->path->captcha.'/'.$cap_id.'.png';
		if(is_file($img_path)){unlink($img_path);}
		$cap_sess=new Zend_Session_Namespace('Zend_Form_Captcha_'.$cap_id);
		$cap_int=$cap_sess->getIterator();
		if(!isset($cap_int['word']) || $cap_int['word']!=$cap){$this->error('captcha');return false;}
		return true;
	}
}