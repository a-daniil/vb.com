<?

class Model_Invoice extends Zend_Db_Table_Abstract {
	
	const TABLE = 'invoice';
	
	const PENDING = 0; // pendign, not processed yet - initial status
	const PAID = 1;
	const NOT_CONFIRMED = 8;
	
	static $statusText = array(
		self::PENDING       => "Pending",
		self::PAID          => "Paid",
		self::NOT_APPROVED => 'Not Approved'	
	);	
	
	private $user_id;
	private $currency;
	private $paysys_id;
	private $amount;	
	private $status;
	
	function setUserId ( $user_id ) {
		$this->user_id = $user_id;
	}
	
	function setCurrency ( $currency ) {
		$this->currency = $currency;
	}
	
	function setPaysysId ( $paysys_id ) {
		$this->paysys_id = $paysys_id;
	}	
	
	function setAmount ( $amount ) {
		$this->amount = $amount;
	}
	
	function setStatus ( $status ) {
		$this->status = $status;
	}
	
	function getUserId () {
		return $this->user_id;
	}
	
	function getCurrency () {
		return $this->currency;
	}
	
	function getPaysysId () {
		return $this->paysys_id;
	}
	
	function getAmount () {
		return $this->amount;
	}
	
	function getStatus () {
		return $this->status;
	}
	
	function add () {
		$this->getAdapter()->insert( self::TABLE, $this->getFields() );
		return $this->getAdapter()->lastInsertId(self::TABLE, 'id');
	}
	
	function renew () {
		$this->delete();
		$this->getAdapter()->update( self::TABLE, $this->getFields() );
	}
	
	function remove () {
		$this->getAdapter()->delete( self::TABLE, 'id = ' . $id );
	}
	
	
	private function getFields () {
		return array (
			'user_id'   => $this->user_id,
			'currency'  => $this->currency,
			'paysys_id' => $this->paysys_id,
			'amount'    => $this->amount,
			'status'    => $this->status
		);
	}
	
}