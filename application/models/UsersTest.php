<?

class Model_UsersTest extends Zend_Db_Table_Abstract {

	protected $_name = 'users';

	public function fetchPaginatorAdapter( $where = null )
	{
		$select = $this->select();
		$select->from($this->_name, array(
			'id',
			'user_login', 
			'balance',
			'spent', 
			'ankets',
			'timestamp',
			'status',
			'mail'
		));
		
		if ( $where || $where === '0' ) {
			$select->where( 'flags = ?', $where );
		}
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getSumBalance( $where = null )
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(balance) as sum' );
		
		if ( $where || $where === '0' ) {
			$select->where( 'flags = ?', $where );
		}
		
		$row = $this->fetchRow($select);
		return $row['sum'];
	}	
	
	public function getSumSpent( $where = null )
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(spent) as sum' );
		
		if ( $where || $where === '0' ) {
			$select->where( 'flags = ?', $where );
		}
	
		$row = $this->fetchRow($select);
		return $row['sum'];
	}
	
	public function getSumAnket( $where = null )
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(ankets) as sum' );
		
		if ( $where || $where === '0' ) {
			$select->where( 'flags = ?', $where );
		}
	
		$row = $this->fetchRow($select);
		return $row['sum'];
	}
	
	public function getLogin ( $user_id ) 
	{
		$select  = $this->select();
		$select->from($this->_name, 'user_login');
		$select->where('id = ?', $user_id);
		$row = $this->fetchRow($select);
		return $row['user_login'];		
	}
	
	public function getBalance ($uid)
	{
		$select = $this->select();
		$select->from($this->_name, 'balance');
		$select->where('id = ?', $uid);
		$row = $this->fetchRow($select);
		
		if ( $row['balance'] ) {
			return $row['balance'];
		} else {
			return 0;
		}		
	}
	
	public function getEmail ( $user_id )
	{
		$select = $this->select();
		$select->from($this->_name, 'mail');
		$select->where('id = ?', $user_id);
		$row = $this->fetchRow($select);
		return $row['mail'];
	}
	
	public function getFlags( $user_id )
	{
		$select = $this->select();
		$select->from($this->_name, 'flags');
		$select->where('id = ?', $user_id);
		$row = $this->fetchRow($select);
		return $row['flags'];
	}
	
	public function getModerators()
	{
		$select = $this->select();
		$select->from($this->_name, array('id', 'mail'));
		$select->where('flags = 8');
		$res = $this->fetchAll($select);
		return $res;
	}	
}