<?php 

class Model_AnketsModerationComm extends Zend_Db_Table_Abstract {
	
	protected $_name = 'ankets_moderation_comm';
	
	public function addAnketaModerationCommm ( $comm, $owner_id ) {
		$select = $this->select('id');
		$select->where('owner_id = ?', $owner_id);
		$row = $this->fetchRow($select);
		
		$id = $row['id'];
		
		if ( !$row = $this->find($id)->current() ) {
			$row = $this->createRow();
		}
		
		$row->comm  = $comm;
		$row->owner_id = $owner_id;
		$row->save();

		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row =  $this->fetchRow($select);
		return $row['id'];
	}
	
	public function getByOwnerId ( $oid ) {
		$select = $this->select();
		$select->where('owner_id = ?', $oid);
		
		$row = $this->fetchRow($select);
		if ( !$row ) {
			return false;
		}
		return $row['comm'];
	}
	
}