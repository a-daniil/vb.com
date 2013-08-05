<?

class Model_Postpone extends Zend_Db_Table_Abstract {
	
	protected $_name = 'postpone';
	
	public function addPostpone( $user_id, $ank_id )
	{
		$select = $this->select('id');
		$select->where('user_id = ?', $user_id);
		$select->where('ank_id = ?', $ank_id);
		$row =  $this->fetchRow($select);
		
		if ( $id = $row['id'] ) {
			return $id;
		}		
		
		$row = $this->createRow();
		
		$row->user_id = $user_id;
		$row->ank_id = $ank_id;
		
		$row->save();
		
		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row =  $this->fetchRow($select);
		return $row['id'];
	}
	
	public function getUserPostpone ( $user_id = null ) {
		$select = $this->select()->from($this->_name, 'COUNT(*) as count');
		$select->where('user_id = ?', $user_id );

		$row = $this->fetchRow($select);
		return $row['count'];
	}
	
	public function getUserAnkPostponeIds( $user_id )
	{
		$select = $this->select()->from($this->_name, 'ank_id');
		$select->where('user_id = ?', $user_id);
		
		$rows = $this->fetchAll($select);
		
		if ( $rows->count() > 0) {
			foreach ( $rows as $row ) {
				$result[] = $row['ank_id'];
			}
			
			return $result;
		} else {
			return array();
		}		
	}
	
}