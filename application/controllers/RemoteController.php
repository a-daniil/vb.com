<?php
/* 
 * Cusotme Exceptions
 */
class Postpone_Exception extends Zend_Exception {}
class ShowCab_Exception extends Zend_Exception {}
/**
 * Menu AJAX`s parts 
 *
 * @author master
 */
class RemoteController extends Zend_Controller_Action {
    
    const USR_ADM=0;
    
    public function init() {
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('view', 'html')
                    ->addActionContext('form', 'html')
                    ->addActionContext('process', 'json')
                    ->initContext();
        
        $this->_helper->layout->disableLayout();
        
        $this->config=Zend_Registry::get('config');
        $this->view->url_user_ph=$this->config->path->url_user_ph;
    }
    
    public function indexAction(){
        
    }
    
    public function menuFilterAction(){
        
    }
    
    public function logoAction(){
        
    }

    public function loginAction(){
        $auth=Zend_Auth::getInstance()->getIdentity();
        if($auth){
                $this->view->auth=$auth->user_login;
                $this->view->user_simple = $auth->flags == 0 ? true : false;
                $this->view->com_user = $auth->flags == 2 ? true : false;  
                $this->view->user_id = $auth->id;             
                ($this->admin=$auth->flags & 1<<self::USR_ADM)?true:false;
        }
        else{$this->admin=false;}
        $this->view->admin=$this->admin;
        $this->config=Zend_Registry::get('config');
        $this->content=Zend_Registry::get('content');
    }

    public function phoneAction(){
        if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$ank_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		if(!$ank_id){$this->error('request_error');return;}			
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_ank_phone($ank_id);		
        $this->view->info = $info;
    }
    
    public function phoneSalonAction(){
   		if(!$this->_hasParam('n')){$this->error('request_error');return;}
		$salon_id = $this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
		if(!$salon_id){$this->error('request_error');return;}			
    	include_once 'Salons.php';
    	$salons=new Salons();
    	$info=$salons->get_salon_phone($salon_id);
    	$this->view->info = $info;
    }
    
    public function phoneSalonAddAction(){
    	$salon_id=$this->_hasParam('n')?intval(substr($this->_getParam('n'),0,32)):false;
    	include_once 'Salons.php';
    	$salons=new Salons();
    	$info=$salons->get_salon($salon_id);
    	$this->view->info = $info;
    }
    
    public function addHistoryAction() {
    	$ank_id = $this->_getParam('id');
    	
    	$anks = array();
    	if ( isset( $_COOKIE['ph'] ) ) {
    		$anks = unserialize($_COOKIE['ph']);
    		$anks[] = $ank_id;
    		if ( count( $anks ) > 15 ) {
    			$anks = array_slice($anks, -16);
    		}    		
    	}else{
    		$anks[] = $ank_id;
    	}
    	
    	setcookie("ph", serialize($anks), time()+108000, "/");
    }

    public function historyAction() {
    	include_once 'Ankets.php';
    	$ankets=new Ankets();
    	
		$anks = array();
		if ( isset( $_COOKIE['ph'] ) ) {
			$anks = unserialize($_COOKIE['ph']);
			
			if ( is_array($anks) && count($anks) > 0 ) {
				$this->view->history_ankets = $ankets->get_history_ankets($anks);
			}
		} else {
			die();
		}	
    }
    
    public function postponeAction() {
    	$ank_id = $this->_getParam('ank_id');
    	$user_id = $this->_getParam('user_id');
    	
    	$postpone = new Model_Postpone();
    	$postpone->addPostpone( $user_id, $ank_id );
    	
    	if ( !$postpone ) {
    		throw new Postpone_Exception();
    	}
    } 
    
    public function showUserCabinetAction() {
    	$flag = $this->_getParam('show');
    	$user_id = $this->_getParam('user_id');
    	
    	$usettings = new Model_USettings();
    	$result = $usettings->setConfig( $user_id, 'show_user_cab', $flag);    	
    	//$result = $usettings->update(array("value" => $flag), "name = 'show_user_cab'");
    	
    	if ( !$result ) {
    		throw new ShowCab_Exception();
    	}
    }
    
    public function reviewAction() {
    	/* prepare and insert data */
    	$owner_id              = $this->_getParam('owner_id');
    	$photo_original        = $this->_getParam('photo_original');
    	$photo_original_select = $this->_getParam('photo_original_select');
    	$real_individual       = $this->_getParam('real_individual');
    	$comment               = $this->_getParam('comment');
    	$apartment             = $this->_getParam('apartment');
    	$service               = $this->_getParam('service');
    	$services              = $this->_getParam('services');
    	$score                 = $this->_getParam('score');
    	$user_id               = $this->_getParam('user_id');

    	$services_count = 0;
    	foreach ( $services as $k => $v ) {
    		list($name, $ratio) = explode("_", $v);
    		$serv_ratio += $ratio;
    		$services_count++;
    	}

    	$ratio = ($photo_original + $photo_original_select + $apartment
    		+ $service + $serv_ratio) / (3 + $services_count );
		
     	$review = new Model_Review();
    	$review->insert(array(
    		'owner_id'        => $owner_id,
    		'ratio'           => $ratio,
    		'photo_original'  => $photo_original,
    		'photo_original_select' => $photo_original_select,
    		'real_individual' => $real_individual,
    		'comment'         => $comment,
    		'apartment'       => $apartment,
    		'service'         => $service,
    		'score'           => $score,
    		'services'        => serialize($services),
    		'user_id'         => $user_id
    	));

    	/* get anket info */
    	$ankets = new Model_AnketsTest();
    	$info = $ankets->getById($owner_id);

    	/* check photo original */
    	$ifNotOriginal = $review->getNotOriginalCount($owner_id);
    	if ( ($ifNotOriginal > 5) && ($info['status'] > 20) ) {
    		$status = 10;
    		Ps_SendMessage_NotOriginalPhoto::sendMessage($status, $info, Form_NewMessageForm::FROM_ADMIN);
    		/* update anket status */
    		$ankets->update(array(
    			'status' => $status
    		), 'id = ' . $info['id']);
    	}
    	
    	/* check real individual */
    	$summaByRealIndividual = $review->getSummaByRealIndividual($owner_id);
    	if (  ($summaByRealIndividual >= 5) && ($info['real'] != 1) ) {
    		$status = 1;
    		Ps_SendMessage_RealIndividual::sendMessage($status, $info, Form_NewMessageForm::FROM_ADMIN);
    		$ankets->update(array(
    			'real' => $status
    		), 'id = ' . $info['id']);
    	} elseif ( ($summaByRealIndividual < 5) && ($info['real'] == 1) ) {
    		$status = 0;
    		Ps_SendMessage_RealIndividual::sendMessage($status, $info, Form_NewMessageForm::FROM_ADMIN);
    		$ankets->update(array(
    			'real' => $status
    		), 'id = ' . $info['id']);
    	}
    	
    	/* check score */
    	$score = $review->getSumScore($owner_id);
    	if ( ($score >= 5) && ($info['score'] != 1) ) {
    		$status = 1;
    		Ps_SendMessage_Score::sendMessage($status, $info,  Form_NewMessageForm::FROM_ADMIN);
    		$ankets->update(array(
    			'score' => $status
    		), 'id = ' . $info['id']);
    	} elseif ( ($score < 5) && ($info['score'] == 1)) {
    		$status = 0;
    		Ps_SendMessage_Score::sendMessage($status, $info,  Form_NewMessageForm::FROM_ADMIN);
    		$ankets->update(array(
    			'score' => $status
    		), 'id = ' . $info['id']);
    	}
    }

    protected function error($error){
	}
}
?>
