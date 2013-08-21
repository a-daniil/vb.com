<?php 

class Model_SectionsTest extends Zend_Db_Table_Abstract {
	
	protected $_name = 'sections';
	
	public function getAllDistinctUri() {
		$select = $this->select();
		$select->from($this->_name, array('uri'));
		
		$row = $this->fetchAll($select);
		if ( $row ) {
			return $row->toArray();
		}
		return array();
	}
	
	public function getId( $uri ){
		$select = $this->select();
		$select->from($this->_name, array('id'));
		$select->where('uri = ?', $uri);
		
		$row = $this->fetchRow($select);
		if ( $row ) {
			return $row['id'];
		}
		return array();
	}
}