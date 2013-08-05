<?php 

class MenuItems extends Zend_Db_Table_Abstract{
	const TABLE = 'menu_items';
	protected $per_page;
	
	public $fields = array(
		'id', 'user_id', 'uri', 'title', 'menu_title', 'title_meta', 'keywords', 'descriptions',
		'text', 'text_left', 'text_right', 'text_footer', 'performer', 'type', 'priority'
	);
	
	public function set_items_per_page($perpage) {
		$this->per_page = (int) $perpage;
	}
	
	public function get_list($page){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, $this->fields);
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}
	
	public function get_all_items() {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, $this->fields)
			->order('priority ASC');
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		return $paginator;
	}
	
	public function upd($id,$info){
		$this->getAdapter()->update(self::TABLE, $info, 'id = ' . $id );
	}
	
	public function add($info){
		$this->getAdapter()->insert(self::TABLE,$info);
	}
	
	public function get($id) {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, $this->fields)
			->where('id = ?', $id)
			->limit(1);
		$return = $this->getAdapter()->query($select)->fetch();
		if ( !$return ) { return false; }
		return $return;
	}
	
	public function get_by_uri($uri) {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, $this->fields)
			->where('uri = ?', $uri);
		$return = $this->getAdapter()->query($select)->fetch();
		if ( !$return ) { return false; }
		return $return;
	}
	
	public function check_uri($uri){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, array('id','title','uri'))
			->where('uri = ?', $uri)
			->limit(1);
		$return = $this->getAdapter()->query($select)->fetch();
		if ( !$return ) {
			return false;
		}
		return $return;			
	}
	
	public function del($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
}