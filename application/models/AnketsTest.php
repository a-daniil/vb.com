<?php 

class Model_AnketsTest extends Zend_Db_Table_Abstract {

	protected $_name = 'ankets';
	
	public function getHistoryAnkets ( $ids ) 
	{
		$select = $this->select()
			->from($this->_name, array('id', 'user_id', 'name_eng', 'age', 'name', 'photolist'))
			->where(' id IN ('.join(", ", $ids ). ') ');

		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function fetchClearAnketsList ( $param = null)
	{
		$select = $this->select();	
			
		$select->where('active = 1');
		$select->where('status >= 30');
		
		if ( $param ) {
			$select->where($param);
		}
		
		$select->order('
			priority DESC,
			case priority when 1 then RAND() end DESC,
			case priority when 0 then timestamp end DESC
		');
		
		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function fetchAnketsList ( array $params = null, $video = false, $metro = false, $mtr_id = false, $per = false)
	{	
		$select = $this->select();		
		
		if ( $per ) {	
			$select->where('performer = ?', $per);
		}	
					
		$select->where('active = 1');
		$select->where('status >= 30');
	
		
		if ( is_array($params) ) {
			foreach( $params as $key => $value ) {
				$select->where($value);
			}
		}

		if ( $video ){
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(1);
			$select->where('videolist <> ""');
		}
	
		if ( $mtr_id ) {
			$select->where("metro = ?", $mtr_id);
			$select->where("metro = ?", $mtr_id);	
			
			$statsMetro = new Model_StatsMetro();
			$statsMetro->incMetro( $mtr_id );
		}
		
		if ( $params[2] === 'status>=50' ) {
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(2);
		}
		
		if( $params[2] === 'breast=0' ) {
			include_once 'CountersAnkets.php';
			$counters=new CountersAnkets();
			$counters->inc_common(3);
		}
		
		$select->order('
			priority DESC,
			case priority when 1 then RAND() end DESC,
			case priority when 0 then timestamp end DESC
		');
				
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;		
	}

	public function fetchPaginatorAdapter( $user_id )
	{
		$select = $this->select();
		$select->where('user_id = ?' , $user_id);
	
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function fetchAnketsPerUsersPaginationAdapter () {
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, array('COUNT(*) as count', 'users.user_login', 'ankets.user_id'));
		$select->join('users',
			'users.id = ankets.user_id',
			'user_login');
		$select->where('ankets.status = 20');
		$select->group('ankets.user_id');
		$select->order('users.balance DESC');
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function fetchAnketsByIds( $ids )
	{
		$select = $this->select();
		$select->where('id IN (' . implode(',', $ids) . ')' );
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}

	public function getAnketsStats() 
	{
		
	}
	
	public function getAnketsCount()
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->group('performer');
		
		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function getAnketsCountByPriority()
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
	
	public function getPriority( $perfomer )
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('performer = ?', $perfomer);
		$select->where('priority = ?', 1);
		
		$row = $this->fetchRow($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
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

	public function getById ($id)
	{
		$select = $this->select();
		$select->where('id = ?', $id);

		$row = $this->fetchRow($select);
		return $row;
	}

	public function getPriorityAnketsByUserId( $uid ) 
	{
		$select = $this->select();
		$select->from($this->_name, array('performer', 'COUNT(*) as count'));
		$select->where('user_id = ?', $uid);
		$select->where('priority = 1');
		$select->where('status = 40');
		$select->where('active = 1');
		$select->group('performer');

		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}


}