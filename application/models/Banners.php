<?php
class Banners extends Zend_Db_Table_Abstract{
 	const TABLE='banners';
 	protected $per_page=10;
 	public function set_items_per_page($perpage){
 		$this->per_page=(int)$perpage;
 	}
	public function get_list($page=1,array $params=null){
		$rows=array('id','user_id','type','timestamp','status','active','title','description','fname','url');
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,$rows);
		if(is_array($params)){
			foreach($params as $key=>$param){$select->where($key.' = ?',$param);}
		}
		//print $select->__toString();exit;
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}
	/**
	 * get left banners
	 * @param integer $page
	 * @return paginator object
	 */
	public function get_list_left($page=1){
	    return $this->get_list($page, array('type'=>1));
	}
	public function get_list_right($page=1){
	    return $this->get_list($page, array('type'=>2));
	}
	public function count_active($user_id=false){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'COUNT(*) as count')
			->where('active = 1');
		if($user_id){$select->where('user_id = ?',$user_id);}
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['count'];
	}
	public function add(array $row){
		$this->getAdapter()->insert(self::TABLE,$row);
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
	/*
	 * update row
	 */
	public function upd($id,$row){
		return $this->getAdapter()->update(self::TABLE,$row,'id = '.$id);
	}
	public function del($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
 }