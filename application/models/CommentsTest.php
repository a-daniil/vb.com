<?

class Model_CommentsTest extends Zend_Db_Table_Abstract {

	protected $_name = 'comments';
	
	function getToModerNum()
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('confirm = false');
		
		$row = $this->fetchRow($select);
		if ( !$row['count'] ) {
			return false;
		}
		return $row['count'];
	}

	public function fetchCommentsPerUsersPaginationAdapter () {
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, array('COUNT(*) as count', 'users.user_login', 'comments.user_id'));
		$select->join('users',
				'users.id = comments.user_id',
				'user_login');
		$select->where('comments.confirm = false');
		$select->group('comments.user_id');
		$select->order('users.balance DESC');

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}

	public function fetchPaginatorAdapter( $user_id = null )
	{
		$select = $this->select();		
		$select->where('confirm = false');
		if ( $user_id ) {
			$select->where('user_id = ?', $user_id);		
		}
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getUserComments( $user_id = null) 
	{
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, array(
			'ankets.name as name',
			'ankets.id as ank_id',
			'comments.text as text',
			'comments.timestamp as time',
			'comments.id as id'));
		
		$select->join('ankets',
				'ankets.id = comments.owner_id',
				'name');
		$select->where('confirm = true');
		if ( $user_id ) {
			$select->where('comments.user_id = ?', $user_id);
		}	
		$select->order('comments.timestamp DESC');
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getNewComments( $user_id = null )
	{
		$select = $this->select()->from($this->_name, 'COUNT(*) as count');
		$select->where('user_id = ?', $user_id );
		$select->where('confirm = true');
		
		$row = $this->fetchRow($select);
		return $row['count'];
	}
	
	public function getOwnerId ( $id ) 
	{
		$select = $this->select()->from($this->_name, 'owner_id');
		$select->where('id = ?', $id);
		
		$row = $this->fetchRow($select);
		return $row['owner_id'];
	}
}