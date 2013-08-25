<?php
class Users extends Zend_Db_Table_Abstract{
 	const TABLE='users';
 	protected $per_page=50;
	public function user_check_by_email( $mail ){
		$select=$this->getAdapter()
		->select()
		->from(self::TABLE,array('id','user_login','mail'))
		->limit(1);
		$select->where('mail = ?',$mail);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	public function user_check_by_name( $login ){
		$select=$this->getAdapter()
		->select()
		->from(self::TABLE,array('id','user_login','mail'))
		->limit(1);	
		$select->where('user_hash = ?',$login);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){
			return false;
		}
		return $return;
	}
	public function user_check_confirm($confirm){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','user_login','mail'))
			->where('confirm = ?',$confirm)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}	
	public function user_check_password($id,$pass){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'id')
			->where('id = ?',$id)
			->where('user_pass = ?',$pass)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return true;
	}
	
	public function user_check_for_retype ($id) {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'id')
			->where('id = ?', $id)
			->where('balance <= 0 OR balance IS NULL')
			->where('ankets <= 0 OR ankets IS NULL');
		$return = $this->getAdapter()->query($select)->fetch();
		if ( !$return ) { 
			return false;
		}
		return true;
	}
	
	public function user_add(array $info){
		$this->getAdapter()->insert(self::TABLE,$info);
	}
	public function user_change_password($id,$pass){
		$this->getAdapter()->update(self::TABLE,array('user_pass'=>$pass,'confirm'=>''),'id = '.$id);
	}
	public function user_recovery_add($id,$conf){
		$this->getAdapter()->update(self::TABLE,array('confirm'=>$conf),'id = '.$id);
	}
	public function user_del($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
	public function upd_user_info($id,array $info){
		$this->getAdapter()->update(self::TABLE,$info,'id = '.$id);
	}
	public function get_users_list($page=1,array $params=null){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','user_login','mail','timestamp','balance','consumption',
				'spent','ankets','banners','salons','shops','status'))
			->order('id DESC');
		if(is_array($params)){
			foreach($params as $param){$select->where($param);}
		}
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;	
	}
	public function get_user_info($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('balance','spent'))
			->where('id = ?',$id)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	public function get_email($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE, array('mail'))
			->where('id = ?', $id);
		$return = $this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['mail'];
	}
	
	public function getLogin($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE, array('user_login'))
			->where('id= ?', $id);
		$return = $this->getAdapter()->query($select)->fetch();
		if(!$return){
			return false;
		}
		return $return['user_login'];
	}
	
	public function ank_inc($id){
		$this->getAdapter()->update(self::TABLE,array('ankets'=>new Zend_Db_Expr('ankets+1')),'id = '.$id);
	}
	public function ank_dec($id){
		$this->getAdapter()->update(self::TABLE,array('ankets'=>new Zend_Db_Expr('ankets-1')),'id = '.$id);
	}
	public function salon_inc($id){
		$this->getAdapter()->update(self::TABLE,array('salons'=>new Zend_Db_Expr('salons+1')),'id = '.$id);
	}
	public function salon_dec($id){
		$this->getAdapter()->update(self::TABLE,array('salons'=>new Zend_Db_Expr('salons-1')),'id = '.$id);
	}
	
}