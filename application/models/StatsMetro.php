<?php 

class Model_StatsMetro extends Zend_Db_Table_Abstract {

	protected $_name = 'stats_metro';

	public function fetchPaginatorAdapter( )
	{
		$select = $this->select();
		$select->from($this->_name, array('id', 'today', 'week', 'month', 'all'));
	
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}	

	public function incMetro( $id )
	{
		if ( !$row = $this->find($id)->current() ) {
			$row = $this->createRow();
			
			$row->id = $id;
			$row->today = 1;
			$row->date = time();
			$row->week = 1;
			$row->week_date = time();
			$row->month = 1;
			$row->month_date = time();
			
			$row->save();
		} else {		
			$data = array(
				'today'	=> new Zend_Db_Expr('today+1'),
				'week'	=> new Zend_Db_Expr('week+1'),
				'month'	=> new Zend_Db_Expr('month+1'),
				'all'	=> new Zend_Db_Expr('`all`+1'),
				'date'	=> new Zend_Db_Expr('`date`'),
				'week_date'	=> new Zend_Db_Expr('`week_date`'),
				'month_date'	=> new Zend_Db_Expr('`month_date`')
			);
		
			if ( time() - $row['date'] >= 3600*24 ) {
				$data['today']=1;
				$data['date']= time();				
			}
		
			if ( time() - $row['week_date'] >= 3600*24*7 ) {
				$data['week']=1;
				$data['week_date']  = time();
			}
		
			if( time() - $row['month_date'] >= 3600*24*30 ) {
				$data ['month'] = 1;
				$data['month_date']  = time();
			}	
	
			$this->update($data, 'id = ' . $id );
		}
	}
		
}