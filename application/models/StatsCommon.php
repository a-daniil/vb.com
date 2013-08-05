<?php 

class Model_StatsCommon extends Zend_Db_Table_Abstract {
	
	const VIDEO = 1;
	const VERIFIED = 2;
	const MAN = 3;
	
	protected $_name = 'stats_common';
	
	public function getCommon( $name )
	{
		$select = $this->select();
		$select->from($this->_name, array('id', 'today', 'all'));
		
		switch ( $name ) {
			case 'video' :				
				$select->where('id = ?', self::VIDEO);
				break;
			case 'verified' :
				$select->where('id = ?', self::VERIFIED);
				break;
			case 'man' :
				$select->where('id = ?', self::MAN);
				break;
		}
	
		$row = $this->fetchRow($select);
		return $row->toArray();		
	}		
	
	public function incCommon( $name ) 
	{		
		switch ( $namme ) {
			case 'video' :
				$id = self::VIDEO;
				break;
			case 'verified' :
				$id = self::VERIFIED;
				break;
			case 'man' :
				$id = self::MAN;
				break;
		}
		
		if ( !$row = $this->find($id)->current() ) {
			$row = $this->createRow();
			
			$row->id = $id;
			$row->today = 1;
			$row->date = time();
			$row->all = 1;
			
			$row->save();
		} else {
			$data=array(
				'today'	=> new Zend_Db_Expr('today+1'),
				'all'	=> new Zend_Db_Expr('`all`+1')
			);
			
			if ( time() - $row['date'] >= 3600*24 ) {
				$data = array(
					'today'	=> 1,
					'all'  => new Zend_Db_Expr('`all`+1'),
					'date'  => time()
				);
			}
			
			$this->update($data, 'id = ' . $id );
		}
	}	
}