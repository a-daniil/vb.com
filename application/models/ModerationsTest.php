<?php

class Model_ModerationsTest extends Zend_Db_Table_Abstract {

	protected $_name = 'moderations';

	public function getByAnkId( $ank_id )
	{
		$select = $this->select();
		$select->where('owner_id = ?', $ank_id);
		
		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
}