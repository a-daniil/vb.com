<?php
class Articles extends Zend_Db_Table_Abstract{
	const TABLE='articles';
	protected $per_page=20;
 	public function set_items_per_page($perpage){
 		$this->per_page=(int)$perpage;
 	}
	public function get_list($page){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','title','title_meta','keywords','descriptions'));
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}
	public function add_art($info){
		$this->getAdapter()->insert(self::TABLE,$info);
	}
	public function get_art($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('id = ?',$id)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	public function upd_art($id,$info){
		$this->getAdapter()->update(self::TABLE,$info,'id = '.$id);
	}
	public function del_art($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
	
}