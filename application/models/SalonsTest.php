<?php 

class Model_SalonsTest extends Zend_Db_Table_Abstract {

	protected $_name = 'salons';
	
	public function getSalonsCount()
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		
		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function getPriority(  )
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');		
		$select->where('priority = ?', 100);
	
		$row = $this->fetchRow($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function getById ($id)
	{
		$select = $this->select();
		$select->where('id = ?', $id);
	
		$row = $this->fetchRow($select);
		return $row;
	}
	
	public function getToModerNum()
	{
		$select=$this->select();
		$select->from($this->_name,'COUNT(*) as count');
		$select->where('status = 20');
	
		$row = $this->fetchRow($select);
		if( !$row['count'] ){
			return false;
		}
		return $row['count'];
	}
	
	public function fetchSalonsPerUsersPaginationAdapter () {
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, array('COUNT(*) as count', 'users.user_login', 'salons.user_id'));
		$select->join('users',
				'users.id = salons.user_id',
				'user_login');
		$select->where('salons.status = 20');
		$select->group('salons.user_id');
		$select->order('users.balance DESC');
	
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getSalonsCountByPriority()
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('priority = 1');
	
		$row = $this->fetchRow($select);
		if( !$row['count'] ){
			return false;
		}
		return $row['count'];
	}
	
	public function getPrioritySalonsByUserId( $uid )
	{
		$select = $this->select();
		$select->from($this->_name, array('COUNT(*) as count'));
		$select->where('user_id = ?', $uid);
		$select->where('priority = 1');	
		$select->where('status = 40');
		$select->where('active = 1');	
	
		$row = $this->fetchRow($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function fetchSalonsList( $page = 1, array $params = null, $metro = false, $type = false )
	{
		$select = $this->select();

		$select->where('active = 1')
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

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
}