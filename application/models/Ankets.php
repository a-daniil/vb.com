<?php
class Ankets extends Zend_Db_Table_Abstract{
 	const TABLE='ankets';
 	protected $per_page=10;
 	public function set_items_per_page($perpage){
 		$this->per_page=(int)$perpage;
 	}
 
 	public function get_history_ankets( $ids ) {
 		$rows = array('id', 'user_id', 'name_eng', 'age', 'name', 'photolist');

 		$select = $this->getAdapter()
 			->select()
 			->from(self::TABLE, $rows)
 			->where(' id IN ('.join(", ", $ids ). ') ');
 
 		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(1000);
		return $paginator;
 	}

	public function get_ankets_list($page=1,array $params=null, $video=false, $metro=false, $mtr_id=false, $per = false) {
		$rows=array('id','user_id','name','name_eng','age','type','city','metro','phone','height','weight',
			'performer','breast','place','price_1h_ap','price_2h_ap','price_n_ap',
            'price_2h_ex','timestamp','photolist','videolist', 'status');
		
        if($per){
        	/* create two tables for union: 1 - ankets has prioriy, 2 -ankets with no priority */  
			
        	
        	$select=$this->getAdapter()
				->select()
				->from(self::TABLE,$rows)
				->where('active = 1')
				->where('performer = ?', $per)
            	->where('status >= 30')
				->where('priority > 0')
        		->order('priority DESC')
				->order('RAND()');
        	
        	
			
        }else{
            $select=$this->getAdapter()
			->select()
			->from(self::TABLE,$rows)
			->where('active = 1')
            ->where('status >= 30')
			//->order('priority DESC')
            ->order('priority DESC')
			->order('RAND()');
        }
		if(is_array($params)){
			foreach($params as $key => $value){$select->where($value);}
		}
		if($video){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(1);
			$select->where('videolist <> ""');
		}
		
		if( $mtr_id ) {	
			$select->where("metro = ?", $mtr_id);
			
			$statsMetro = new Model_StatsMetro();
			$statsMetro->incMetro( $mtr_id );
		}

		if ($params[2] === 'status>=50') {
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(2);
		}
		if($params[2] === 'breast=0'){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(3);
		}
		//print_r($params);
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}

	public function get_ankets_with_search( $page=1,array $params=null, $video=false, $metro=false, $mtr_id=false, $per = false ){		
		$rows=array('id','user_id','name','name_eng','age','type','city','metro','phone','height','weight',
			'performer','breast','place','price_1h_ap','price_2h_ap','price_n_ap',
            'price_2h_ex','timestamp','photolist','videolist', 'status');

		if ( $per ){
			$select=$this->getAdapter()
			->select()
			->from(self::TABLE,$rows)
			->where('active = 1')
			->where('status >= 30')
			->where('performer = ?', $per)
			->order('RAND()');
		}else{
			$select=$this->getAdapter()
			->select()
			->from(self::TABLE,$rows)
			->where('active = 1')
			->where('status >= 30')
			->order('RAND()');
		}
		
		if( is_array( $params ) ){
			foreach( $params as $param ){
				if ( $param ) {
					$select->where( $param );
				}
			}
		}
		
		if( $metro ){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();	
			$counters->inc_metro($mtr_id);
			$select->where("metro like '%".$metro."'");
		}

		if( $video ){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(1);
			$select->where('videolist <> ""');
		}

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		
		if ( $this->per_page % 3 != 0 ) { 
			$per_page = $this->per_page+1;
		} else {
			$per_page = $this->per_page;
		}
		
		$paginator->setItemCountPerPage($per_page);
		return $paginator;
		
	}

	public function get_coords_per_ankets() {
		$rows = array('coords','id','performer', 'user_id', 'photolist', 'name');
		$select = $this->getAdapter()->select()->from(self::TABLE, $rows);
		$select->where('status >= 30');
		$select->where('active = 1');
		#$select->where('priority > 0');
		$return=$this->getAdapter()->query($select)->fetchAll();
		if ($return) {
			return $return;
		} else {
			return array();
		}
	}

	public function get_ankets_by_salon_id($salon_id){
		$select=$this->getAdapter()->select()->from(self::TABLE);
		$select->where('type = ?', $salon_id);
		$select->where('status > 20');
		$select->where('active = 1');
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}

 	public function get_ankets_list_cab($page=1,$user_id=false,$moder=false,$performer=false,$filter=false){
		$rows=array('id','user_id','name','name_eng', 'type','performer', 'city','timestamp','end_timestamp','active','photolist','phone',
					'priority','status','photo_check', 'photo_finish', 'photo_start');
		$select=$this->getAdapter()->select()->from(self::TABLE,$rows);
		if($user_id){$select->where('user_id = ?',$user_id);}
		if($performer){
			$select->where('performer = ?', $performer);
		}
 		if($moder){$select->order('id')->where('status = 20');}
 		else{$select->order('id DESC');
 		}
 		if ( $filter ) {
 			$select->where($filter);
 		} 		
		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}
	public function count_all_ankets($user_id = false ) {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'COUNT(*) as count');
		if ( $user_id ) {
			$select->where('user_id = ?', $user_id); 
		}
		$return = $this->getAdapter()->query($select)->fetch();
		if ( !$return ) {
			return false;
		}
		return $return['count'];
	}	
	public function count_active_ankets($user_id=false){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'COUNT(*) as count')
			->where('active = 1')
			->where('status >= 30');
		if($user_id){$select->where('user_id = ?',$user_id);}
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['count'];
	}
 	public function count_paid_ankets($user_id=false) {
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'COUNT(*) as count')
			->where('status = 40')
			->where('priority = 1')
			->where('active = 1');
		if($user_id){$select->where('user_id = ?',$user_id);}
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['count'];
	}

 	public function check_phone($phone){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'id')
			->where('phone = ?',$phone)
            ->order('RAND()'); 
		$return=$this->getAdapter()->query($select)->fetch();

		if(!$return){return false;}
		return $return;
	}

	public function add_anket(array $info){
		$this->getAdapter()->insert(self::TABLE,$info);
		return $this->getAdapter()->lastInsertId(self::TABLE,'id');
	}
	public function get_anket($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('id = ?',$id)
			->limit(1);
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	
	public function get_ank_phone($id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'phone')
			->where('id = ?', $id)
			->limit(1);
		$return = $this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}
	
	public function get_ank_performer($id) {
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'performer')
			->where('id = ?', $id)
			->limit(1);
		$return = $this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['performer'];
	}
    
    public function getAllAnket($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('id = ?',$id);
		$return=$this->getAdapter()->query($select)->fetchAll();
		//if(!$return){return false;}
		return $return;
	}
    
	public function get_user_consumption($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'SUM(priority) as sum')
			->where('user_id > ?',$id);
		$return=$this->getAdapter()->query($select)->fetch();
		return $return['sum'];
	}
	public function upd_anket($id,$info){
		$this->getAdapter()->update(self::TABLE,$info,'id = '.$id);
		return true;
	}
	public function del_anket($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
        /**
	 * Increment value in field
	 * @param integer id
	 * @param string $field
	 */
	public function incr($id,$field = 'comments'){
		return $this->getAdapter()->update(self::TABLE,array($field=>new Zend_Db_Expr($field.'+1')),'id = '.$id);
	}
        /**
	 * Decriment value in field
	 * @param integer id
	 * @param string $field
	 */
	public function decr_comment($id,$field = 'comments'){
		return $this->getAdapter()->update(self::TABLE,array($field=>new Zend_Db_Expr($field.'-1')),'id = '.$id);
	}
	public function get_to_moder_num(){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,'COUNT(*) as count')
			->where('status = 20');
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return['count'];		
	}
    
    public function checkNumberPhone($phone) {
        $tags = array("+7", "-", "(", ")", ".", "/", "%", "+", " ", 
                        "d", "a", "b", "c", "d", "e", "f", "g", "q", 
                        "w", "e", "r", "t", "y", "u", "i", "o", "p",
                        "s", "h", "j", "k", "l", "m", "n", "z", "x", 
                        "v", "<", ">", ",", "\\", "|", "!", "[", "]", ";", ":", "'");
        $number = str_replace($tags, "", $phone);
        $number = trim($number);
        
        if(strlen($number) == 10){
            $pre = substr($number, 3, 10); 
            $post = substr($number, 0, 3); 
            $new = $post.'-'.$pre;
        }
        else if(strlen($number) > 10){
            $number = substr($number, 1);
            $pre = substr($number, 3, 10); 
            $post = substr($number, 0, 3); 
            $new = $post.'-'.$pre;
        } 
        
        return $new;
    }
    
    public function get_ankets_list_phone($phone){
		$rows=array('id','user_id','name','age','type','city','metro','phone','height','weight',
			'performer','breast','place','price_1h_ap','price_2h_ap','price_n_ap',
                        'price_2h_ex','timestamp','photolist','videolist', 'status');
	
        $select=$this->getAdapter()
			->select()
			->from(self::TABLE,$rows)
			->where('phone = ?', $phone)
			->order('RAND()'); 
        
        $return=$this->getAdapter()->query($select)->fetchAll();

		//if(!$return){return false;}
		return $return;
	}
 }
