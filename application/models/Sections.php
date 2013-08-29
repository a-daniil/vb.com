<?php
class Sections extends Zend_Db_Table_Abstract{
	const TABLE = 'sections';
	protected $per_page = 200;
        public $fields = array(
            'id','user_id','title','uri','title_meta','keywords','descriptions',
            'text','text_left','text_right','text_footer',
            'age','type','city','district','hair', 'exotics','metro','phone','height','weight',
            'performer','breast','place',
            'price_1h_ap','price_2h_ap','price_n_ap','price_2h_ex',
            'srv_main',	'srv_add', 'srv_strip',	'srv_extr', 'srv_bdsm', 'srv_mass',
            'timestamp','photolist','videolist', 'status','limit',
            'with_videos', 'verified',	'with_comments'
            );
 	public function set_items_per_page($perpage){
 		$this->per_page = (int)$perpage;
 	}
	public function get_list($page, $uri = false, $city = false){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE,$this->fields)
		    ->order('title');

		if ( is_array($uri) && !empty($uri) ) {
			foreach( $uri as $u ) {
				$select->where('uri NOT LIKE ?', "%{$u}%");
			}
		} elseif ( $uri ) {
			$select->where('uri LIKE ?', "%{$uri}%");
		}
		
		if ( $city ) {
			$select->where('city = ?', $city);
		}

		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}

	public function add($info){
            if (empty($info['timestamp'])){
                $info['timestamp'] = date("m-d-Y H:i:s");
            }
            $this->getAdapter()->insert(self::TABLE,$info);
	}
	public function get($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE, $this->fields)
			->where('id = ?',$id)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	public function upd($id,$info){
            if (empty($info['timestamp'])){
                $info['timestamp'] = date("Y-m-d H:i:s");
            }
            $this->getAdapter()->update(self::TABLE,$info,'id = '.$id);
	}
	public function del($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
        
        public function check_uri($uri){
                $select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('id','title','uri'))
			->where('uri = ?',$uri)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
        }
	
}