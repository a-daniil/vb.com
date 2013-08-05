<?php 

class Model_Metro extends Zend_Db_Table_Abstract {

	protected $_name = 'metro';
	
	public function addMetro( $name )
	{
		$row = $this->createRow();
		$row->name = $name;
		$row->save();
		
		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row = $this->fetchRow($select);
		return $row['id'];		
	}	
	
	public function getName( $id )
	{
		$select = $this->select();
		$select->where('id = ?', $id );
		$row = $this->fetchRow($select);
		return $row['name'];
	}
}