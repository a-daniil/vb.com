<?php
class Comments extends Zend_Db_Table_Abstract{
 	const TABLE='comments';
 	protected $per_page=2;
 	public function set_items_per_page($perpage){
 		$this->per_page=(int)$perpage;
 	}

	public function get_list($page=1,$id, $priority){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','text', 'user_id', 'timestamp', 'flags', 'users.user_login', 'owner_id', 'hide', 'ankets.user_id as anket_user_id'))
			->join('users',
				'users.id = comments.user_id',
				'user_login')
			->join('ankets',
				   'ankets.id = comments.owner_id',
				   'ankets.user_id as anket_user_id')
			->where('comments.owner_id = ?',$id)
			->where('comments.confirm = true');

		if ( $priority ) {
			$select->where('comments.hide = false OR comments.hide IS NULL');
		}

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}

	public function get_list_by_user($page=1,$id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','name','text','timestamp','flags'))
			->where('user_id = ?',$id);
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}
	public function add(array $info){
		$this->getAdapter()->insert(self::TABLE,$info);
		return $this->getAdapter()->lastInsertId(self::TABLE,'id');
	}
	public function get($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('id = ?',$id)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	
	public function upd($id,$info) {
		$this->getAdapter()->update(self::TABLE,$info,'id = '.$id);
	}
	
	public function del($id) {
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
 }