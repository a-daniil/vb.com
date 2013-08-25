<?php

class Ps_Salon_Exception extends Zend_View_Exception {};

include_once 'IndexController.php';
class SalonController extends IndexController {
	public function indexAction(){
		if( !$this->_hasParam('name') ){
			throw new Ps_Salon_Exception();
		}
		list( $name, $salon_id, $uri ) = explode("-", $this->_getParam('name') );
		if( !$salon_id ){
			throw new Ps_Salon_Exception();
		}
		
		include_once 'Salons.php';
		$salons = new Salons();
		$info=$salons->get_salon($salon_id);
		
		if(!$info){
			throw new Ps_Salon_Exception();
		}

		$info['uri'] = $uri;

		if(empty($info['photolist'])){
			$info['photolist']=false;
		} else {
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
		} else {
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
		
		$services=$this->content->srv_salon->toArray();
		$this->view->srv_active=array();
		$this->view->part_active=array();
		foreach($services as $srv=>$list){ // The selected serices view preparing
			foreach($list as $key=>$null){
				if($info['srv_salon'.'_'.$srv] & 1<<$key){
					$this->view->srv_active[$srv.'_'.$key]=true;
					$this->view->part_active[$srv]=true;
				}
				else{$this->view->srv_active[$srv.'_'.$key]=false;
				}
			}
		}
		
		$this->view->options = array();
		
		if( $info['sauna'] ){
			$this->view->options['Сауна'] = true;
		}
		if( $info['bilyrd'] ){
			$this->view->options['Бильярд'] = true;
		}
		if( $info['devushki'] ){
			$this->view->options['Джакузи'] = true;
		}
		if( $info['bar'] ){
			$this->view->options['Бар'] = true;
		}	
		if( $info['karaoke'] ){
			$this->view->options['Караоке'] = true;
		}
		if( $info['kalyan'] ){
			$this->view->options['Кальян'] = true;
		}
		
		list($info['phone_p1'],$info['phone_p2'])=explode('-',$info['phone']);
		list($info['phone_p3'],$info['phone_p4'])=explode('-',$info['pnone_add']);
		
		$info['type']=$this->view->types_of_salon[$info['type']];
		$info['city']=$this->view->cities[$info['city']];
		$this->view->info=$info;
		
		include_once 'CountersAnkets.php';
		$counters=new CountersAnkets();
		$counters->inc_salon($info['id']);
		
		$commpage=$this->_hasParam('cp')?intval($this->_getParam('cp')):1;
		include_once 'Comments.php';
		$comments=new Comments();
		$this->view->comments=$comments->get_list($commpage,$salon_id);

		include_once 'Ankets.php';
		$ankets = new Ankets();
		$this->view->ankets = $ankets->get_ankets_by_salon_id($salon_id);
	}
}