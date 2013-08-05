<?php
class UsersUnconf extends Zend_Db_Table_Abstract{
 	const TABLE='users_unconf';
 	public function user_check($login){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'id')
			->where('user_hash = ?',$login)
			->limit(1);
		$return=$this->getAdapter()
			->query($select)
			->fetch();
		if(!$return){return false;}
		return true;
	}
	public function get_confirm($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('confirm = ?',$id)
			->limit(1);
		$return=$this->getAdapter()
			->query($select)
			->fetch();
		if(!$return){return false;}
		return $return;
	}
	public function user_add(array $info){
		$this->getAdapter()->insert(self::TABLE,$info);
	}
	public function user_del($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
}