<?php
class CountersAnkets extends Zend_Db_Table_Abstract{
	const TABLE='stats_ankets';
	const TABLE_METRO='stats_metro';
	const TABLE_COMMON = 'stats_common';
	const TABLE_SALON = 'stats_salons';
	public function add_ank($id){
		$data=array(
			'owner_id'	=>$id,
			'date'		=> time(),
			'today'		=>0,
			'week'		=>0,
			'month'		=>0,
			'all'		=>0
		);
		$this->getAdapter()->insert(self::TABLE,$data);
	}
	public function inc_ank($id){
		
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE)
			->where('owner_id = '.$id)
			->limit(1);
		$dt=$this->getAdapter()
			->query($select)
			->fetch();
		
		/* if row for this anket not exist - create new */
		if ( !$dt ) {
		    $this->add_ank($id);
		}
		
		$data=array(
			'today'	=> new Zend_Db_Expr('today+1'),
			'week'	=> new Zend_Db_Expr('week+1'),
			'month'	=> new Zend_Db_Expr('month+1'),
			'all'	=> new Zend_Db_Expr('`all`+1'),
			'date'	=> new Zend_Db_Expr('`date`'),
			'week_date'	=> new Zend_Db_Expr('week_date'),
			'month_date'	=> new Zend_Db_Expr('month_date')
		);
		
				#print_r($dt);
	if(time() - $dt['date'] >= 3600*24){
			$data['today']=1;
			$data['date']= time();
			
		}
		if(time() - $dt['week_date'] >= 3600*24*7){
				
			$data['week']=1; 
				$data['week_date']  = time();
			
		}
		if(time() - $dt['month_date'] >= 3600*24*30){
			$data ['month'] = 1;
				$data['month_date']  = time();
		}		
		$this->getAdapter()->update(self::TABLE,$data,'owner_id = '.$id);
		
	}
	public function get_ank($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE,array('today','week','month','all'))
			->where('owner_id = '.$id)
			->limit(1);
		$ret=$this->getAdapter()
			->query($select)
			->fetch();
		return $ret?$ret:array('today'=>0,'all'=>0);
	}
	public function del_ank($id){
		$this->getAdapter()->delete(self::TABLE,'owner_id = '.$id);
	}
	
	//salon counters
	public function inc_salon($id){
		$select = $this->getAdapter()
		->select()
		->from(self::TABLE_SALON)
		->where('owner_id = '.$id)
		->limit(1);
		$dt=$this->getAdapter()
		->query($select)
		->fetch();
		
		/* if row for this anket not exist - create new */
		if ( !$dt ) {
			$this->add_salon($id);
		}
		
		$data=array(
				'today'	=> new Zend_Db_Expr('today+1'),
				'week'	=> new Zend_Db_Expr('week+1'),
				'month'	=> new Zend_Db_Expr('month+1'),
				'all'	=> new Zend_Db_Expr('`all`+1'),
				'date'	=> new Zend_Db_Expr('`date`'),
				'week_date'	=> new Zend_Db_Expr('week_date'),
				'month_date'	=> new Zend_Db_Expr('month_date')
		);
	
		#print_r($dt);
		if(time() - $dt['date'] >= 3600*24){
		$data['today']=1;
		$data['date']= time();
			
	}
	if(time() - $dt['week_date'] >= 3600*24*7){
	
	$data['week']=1;
		$data['week_date']  = time();
			
	}
	if(time() - $dt['month_date'] >= 3600*24*30){
	$data ['month'] = 1;
	$data['month_date']  = time();
	}
	$this->getAdapter()->update(self::TABLE_SALON,$data,'owner_id = '.$id);
	}
	
	public function add_salon($id){
		$data=array(
				'owner_id'	=>$id,
				'date'		=> time(),
				'today'		=>0,
				'week'		=>0,
				'month'		=>0,
				'all'		=>0
		);
		$this->getAdapter()->insert(self::TABLE_SALON,$data);
	}
	
	public function get_salon($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE_SALON,array('today','week','month','all'))
			->where('owner_id = '.$id)
			->limit(1);
		$ret=$this->getAdapter()
			->query($select)
			->fetch();
		return $ret?$ret:array('today'=>0,'all'=>0);
	}
	
	public function del_salon($id){
		$this->getAdapter()->delete(self::TABLE_SALON,'owner_id = '.$id);
	}
	
	// metro counters
	public function inc_metro($id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE_METRO)
			->where('id = '.$id)
			->limit(1);
		$dt=$this->getAdapter()
			->query($select)
			->fetch();
		$data=array(
			'today'	=> new Zend_Db_Expr('today+1'),
			'week'	=> new Zend_Db_Expr('week+1'),
			'month'	=> new Zend_Db_Expr('month+1'),
			'all'	=> new Zend_Db_Expr('`all`+1'),
			'date'	=> new Zend_Db_Expr('`date`'),
			'week_date'	=> new Zend_Db_Expr('`week_date`'),
			'month_date'	=> new Zend_Db_Expr('`month_date`')
		);
	if(time() - $dt['date'] >= 3600*24){
			$data['today']=1;
			$data['date']= time();
			
		}
		if(time() - $dt['week_date'] >= 3600*24*7){
			$data['week']=1; 
				$data['week_date']  = time();
		}
		if(time() - $dt['month_date'] >= 3600*24*30){
			$data ['month'] = 1;
				$data['month_date']  = time();
		}	
		
		
		$this->getAdapter()->update(self::TABLE_METRO,$data,'id = '.$id);
	}
	
	public function get_metro() {
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE_METRO,array('today','week','month','all'));
		$ret=$this->getAdapter()
			->query($select)
			->fetchall();
		return $ret?$ret:array('today'=>0,'all'=>0);
	}
	
	// common counters
	public function inc_common($id){
		$select = $this->getAdapter()
			->select()
			->from(self::TABLE_COMMON,date)
			->where('id = '.$id)
			->limit(1);
		$dt=$this->getAdapter()
			->query($select)
			->fetch();
		$data=array(
			'today'	=> new Zend_Db_Expr('today+1'),
			'all'	=> new Zend_Db_Expr('`all`+1')
		);
		if(time() - $dt['date'] >= 3600*24){
			$data = array(
				'today'	=> 1,
				'all'  => new Zend_Db_Expr('`all`+1'),
				'date'  => time()
			);
		}
		
		$this->getAdapter()->update(self::TABLE_COMMON,$data,'id = '.$id);
	}
	public function get_common($id){
		$select=$this->getAdapter()
			->select()
			->from(self::TABLE_COMMON,array('today','all'))
			->where('id = '.$id)
			->limit(1);
		$ret=$this->getAdapter()
			->query($select)
			->fetch();
		return $ret?$ret:array('today'=>0,'all'=>0);
	}
	
	
}
