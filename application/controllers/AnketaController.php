<?php

class Ps_Anketa_Exception extends Zend_View_Exception {};

include_once 'IndexController.php';
class AnketaController extends IndexController {
	
	public function indexAction() {
		if(!$this->_hasParam('name')){
			throw new Ps_Anketa_Exception();
		}
		list( $name, $ank_id ) = explode("-", $this->_getParam('name') );
		if(!$ank_id){
			throw new Ps_Anketa_Exception();
		}
		$in_params=array('c','a','h','w','b','r','s');
		$filters=array();
		foreach($in_params as $param){
			if($this->_hasParam($param)){
				$filters[$param]=substr($this->_getParam($param),0,32);
			}
			else{$filters[$param]=false;
			}
		}
		$this->view->filter=$filters;
		if($this->_hasParam('l')) {
			$this->view->srv_short_list=false;
		}
		
		include_once 'Ankets.php';
		$ankets=new Ankets();
		$info=$ankets->get_anket($ank_id);
		
		if ( !$info ) {
			throw new Ps_Anketa_Exception();
		}
			
		$age_conf=$this->content->age_post->toArray();
		switch($info['age'][1]){
			case 0: $age_post=$age_conf[0];break;
			case 1: $age_post=$age_conf[1];break;
			case 2:
			case 3:
			case 4: $age_post=$age_conf[2];break;
			case 5:
			case 6:
			case 7:
			case 8:
			case 9: $age_post=$age_conf[0];break;
		}
		$info['age'].=' '.$age_post;
		if(empty($info['photolist'])){
			$info['photolist']=false;
		} // If the photo's array is empty
		else{
			$info['photolist']=unserialize($info['photolist']);
			$info['photo']=$this->config->path->url_user_ph.'/'.$info['user_id'].'/'.$info['photolist'][0];
			if(isset($info['photolist']['preview'])){
				unset($info['photolist']['preview']);
			}
			foreach($info['photolist'] as $key=>$val){
				if(empty($val)){
					unset($info['photolist'][$key]);continue;
				}
				$info['photolist'][$key]=array(
						'th'=>$this->config->path->url_user_ph.'/'.$info['user_id'].'/th_'.$val,
						'fs'=>$this->config->path->url_user_ph.'/'.$info['user_id'].'/'.$val
				);
			}
		}
		if(empty($info['videolist'])){
			$info['videolist']=false;
		} // If the photo's array is empty
		else{
			$info['videolist']=unserialize($info['videolist']);
			$info['video']=$this->config->path->url_user_vi.'/'.$info['user_id'].'/'.$info['videolist'][0];
			if(isset($info['videolist']['preview'])){
				unset($info['videolist']['preview']);
			}
			foreach($info['videolist'] as $key=>$val){
				if(empty($val)){
					unset($info['videolist'][$key]);continue;
				}
				$info['videolist'][$key]=array(
						'th'=>$this->config->path->url_user_vi.'/'.$info['user_id'].'/th_'.$val,
						'fs'=>$this->config->path->url_user_vi.'/'.$info['user_id'].'/'.$val
				);
			}
		}
		$services=$this->content->srv->toArray();
		$this->view->srv_active=array();
		$this->view->part_active=array();
		foreach($services as $srv=>$list){ // The selected serices view preparing
			foreach($list as $key=>$null){
				if($info['srv'.'_'.$srv] & 1<<$key){
					$this->view->srv_active[$srv.'_'.$key]=true;
					$this->view->part_active[$srv]=true;
				}
				else{$this->view->srv_active[$srv.'_'.$key]=false;
				}
			}
		}
		list($info['phone_p1'],$info['phone_p2'])=explode('-',$info['phone']);
		
		if( $info['type'] != 1){
			include_once 'Salons.php';
			$salons=new Salons();
			$this->view->salon=$salons->get_salon($info['type'], true, 40, true);
			$this->view->uri = 'salony';
			$this->view->services_of_salon = $this->content->srv_salon->toArray();
		} else {
			$info['type']=$this->view->types[$info['type']];
		}
		
		$info['performer']=$this->view->performer_out[$info['performer']];
		$info['city']=$this->view->cities[$info['city']];
		$this->view->info=$info;
		
		$meta_process=array(
				'start_logo_alt',
				'ank_title',
				'ank_keys',
				'ank_desc',
				'ank_text',
				'city_piter',
				'city_moscow',
				'menu_girls',
				'menu_salons',
				'menu_shops'
		);
		$meta_tags=array(
				'name',
				'type',
				'performer',
				'city',
				'metro',
				'age',
				'breast',
				'height',
				'weight',
				'phone'
		);
		$meta_replace=array();
		$meta_data=array();
		foreach($meta_tags as $key){
			switch ($key) {
				case 'phone':
					$meta_replace[$key]=$this->view->PhoneFormat(array('phone' => $info[$key]));
					break;
				case 'metro' :
					$meta_replace[$key] = $this->view->metro_list[$info[$key]];
					break;
				default:
					$meta_replace[$key]=$info[$key];
					break;
			}		
		}
		foreach($meta_tags as $key=>$value){
			$meta_tags[$key]='%'.strtoupper($value).'%';
		}
		foreach($meta_process as $item){ // The meta data parsing with an item info
			$meta_data[$item]=str_replace($meta_tags,$meta_replace,$this->view->meta[$item]);
		}
		$meta_data['start_title']=$meta_data['ank_title'];
		$meta_data['start_desc']=$meta_data['ank_desc'];
		$meta_data['start_keys']=$meta_data['ank_keys'];
		unset($meta_data['ank_title'],$meta_data['ank_desc'],$meta_data['ank_keys']);
		$this->view->meta=$meta_data;
		include_once 'CountersAnkets.php';
		$counters=new CountersAnkets();
		$counters->inc_ank($info['id']);
		// Comments:
		$commpage = $this->_hasParam('cp') ? intval($this->_getParam('cp')) : 1;
		include_once 'Comments.php';
		$comments = new Comments();
		$this->view->comments = $comments->get_list($commpage,$ank_id, $info['priority']);	
		$this->view->services = $services;	
		
		// Reviews for latest two section in sidebar
		$reviews = new Model_Review();
		$latest2reviews = $reviews->getLatest2Reviews($ank_id);
		$this->view->latest2reviews = $latest2reviews;
		
		// Reviews
		$cp = $this->_getParam('cp');
		$adapter = $reviews->fetchPaginatorAdapter($info['id']);
		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage(2);
		$paginator->setCurrentPageNumber($cp);
		$this->view->reviews = $paginator;
	}
}