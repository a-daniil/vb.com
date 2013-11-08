<?php

class Salons extends Zend_Db_Table_Abstract{
	const TABLE = 'salons';
	protected $per_page = 10;
	
	public function set_items_per_page($perpage){
		$this->per_page = (int)$perpage;
	}
	
	public function add_salon(array $info){
		$this->getAdapter()->insert(self::TABLE,$info);
		return $this->getAdapter()->lastInsertId(self::TABLE,'id');
	}

	public function getSalonsCount()
	{
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'COUNT(*) as count');
		$return = $this->getAdapter()->query($select)->fetch();
		if( !$return ){ return array();}
		return $return->toArray();
	}

	public function get_salon_list($page=1,array $params=null, $metro=false, $per = false, $type = false){
		$rows=array(
			'id',
			'user_id',
			'name',
			'name_eng',
			'type',
			'city',
			'metro',
			'phone',
			'address',
			'district',	
			'price_1h_ap','price_2h_ap','price_n_ap',
			'price_1h_ex','price_2h_ex','price_n_ex',
			'photolist',
			'videolist', 
			'status',
			'girl_number');

		$select=$this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('active = 1')
			->where('status = 40')
			->order('RAND()');
		
		if(is_array($params)){
			foreach($params as $param){
				$select->where($param);
			}
		}
		
		if($type){
			$select->where('type = ?', $type);
		}
		
		if($metro){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$mtr_id = $mtr_id + 1;
			$counters->inc_metro($mtr_id);
			$select->where("metro like '".$metro."%'");
		}		

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
		$paginator->setPageRange(15);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($this->per_page);
		return $paginator;
	}

	public function get_coords_per_salons($city) {
		$rows = array('coords','id','type', 'user_id', 'photolist', 'name');
		$select = $this->getAdapter()->select()->from(self::TABLE, $rows);
		$select->where('status >= 30');
		$select->where('active = 1');
		$select->where('priority > 0');
		$select->where('city = ?',$city);
		$return=$this->getAdapter()->query($select)->fetchAll();
		if ($return) {
			return $return;
		} else {
			return array();
		}
	}

	public function get_salon($id, $active = false, $status = false, $priority = false){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('id = ?', $id)
			->limit(1);

		if ( $active ) {
			$select->where('active = 1');
		}

		if ( $priority ) {
			$select->where('priority = 1');
		}

		if ( $status ) {
			$select->where('status = ?', $status);
		}

		$return = $this->getAdapter()->query($select)->fetch();
		if( !$return ){ return false;}
		return $return;
	}

	public function get_salon_phone($id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE, 'phone')
			->where('id = ?', $id)
			->limit(1);
		$return = $this->getAdapter()->query($select)->fetch();
		if(!$return){return false;}
		return $return;
	}

	public function upd_salon($id, $info){
		$this->getAdapter()->update(self::TABLE, $info, 'id = '.$id );
		return true;
	}

	public function get_user_consumption($id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE,'SUM(priority) as sum')
			->where('user_id > ?', $id);			
		$return = $this->getAdapter()->query($select)->fetch();	
		return $return['sum'];
	}

	public function get_salons_list_cab($page=1,$user_id=false,$moder=false, $filter = false){
		$rows=array('id','user_id','name','city','timestamp','end_timestamp','active','photolist','phone',
				'priority','status');
		$select=$this->getAdapter()->select()->from(self::TABLE,$rows);
		if($user_id){
			$select->where('user_id = ?',$user_id);
		}
		if($moder){
			$select->order('id')->where('status = 20');
		}
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

	public function count_active_salons($user_id=false){
		$select=$this->getAdapter()
		->select()
		->from(self::TABLE,'COUNT(*) as count')
		->where('active = 1 AND status >= 30');
		if($user_id){
			$select->where('user_id = ?',$user_id);
		}
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){
			return false;
		}
		return $return['count'];
	}
	
	public function count_paid_salons($user_id=false){
		$select=$this->getAdapter()
		->select()
		->from(self::TABLE,'COUNT(*) as count')
		->where('priority = 1')
		->where('status = 40')
		->where('active = 1');
		if($user_id){
			$select->where('user_id = ?',$user_id);
		}
		$return=$this->getAdapter()->query($select)->fetch();
		if(!$return){
			return false;
		}
		return $return['count'];
	}
	
	public function del_salon($id){
		$this->getAdapter()->delete(self::TABLE,'id = '.$id);
	}
	
	public function get_user_salons($user_id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('user_id = ?', $user_id);
		$return = $this->getAdapter()->query($select)->fetchAll();
		return $return;
	}
	
}