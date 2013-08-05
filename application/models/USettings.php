<?

class Model_USettings extends Zend_Db_Table_Abstract {
	
	protected $_name = 'usettings';
	
	public function setConfig( $user_id, $name, $value ) 
	{		
		$select = $this->select('id');
		$select->where('user_id = ?', $user_id );
		$select->where('name = ?', $name );
		
		if ( $row = $this->fetchRow($select) ) {
			return $this->update(array("value" => $value), $row['id']);
		} else {
			$row = $this->createRow();
			$row->user_id = $user_id;
			$row->name = $name;
			$row->value = $value;
			
			$row->save();
			
			/* emulate behavior of lastInserId(); */
			$select = $this->select('id');
			$select->order('id desc');
			$row =  $this->fetchRow($select);
			return $row['id'];
		}		
	}
	
	public function getConfig( $user_id, $name, $default = null ) 
	{	
		$select = $this->select();
		$select->from($this->_name, 'value');
		$select->where('name = ?', $name);
		$select->where('user_id = ?', $user_id );
		
		$row = $this->fetchRow($select);
		if ( !$row['value'] ) {
			return $default;
		} else {
			return $row['value'];
		}
	}
}