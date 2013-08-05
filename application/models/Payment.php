<?php 

class Model_Payment extends Zend_Db_Table_Abstract {

	protected $_name = 'payment';

	public function addPayment( $amount, $number, $purse, $user_id, $date ) {
		$data = array(
			'amount'  => $amount,
			'number'  => $number,
			'purse'	  => $purse,
			'user_id' => $user_id,
			'date'    => $date
		);

		return $this->insert( $data );
	}

	public function getUserIdByNo( $number ) {
		$select = $this->select();
		$select->from($this->_name, 'user_id');
		$select->where('number = ?', $number);
		$row = $this->fetchRow($select);
		return $row['user_id'];		
	}

	public function getAmountByPaymentNo( $number )
	{
		$select = $this->select();
		$select->from($this->_name, 'amount');
		$select->where('number = ?', $number);
		$row = $this->fetchRow($select);
		return $row['amount'];
	}

	public function getPaymentsByUserId( $uid )
	{
		$select = $this->select();
		$select->where("user_id = ?", $uid);

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}

	public function fetchPaginatorAdapter( $test = false, $filter )
	{
		$select = $this->select();
		if ( $test ) {
			$select->where('mode = 1');
		} else {
			$select->where('mode = 0');
		}
		
		if ( $filter ) {
			$select->where($filter);
		}

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getSumAmount( $test = false, $filter )
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(amount) as sum');
		if ( $test ) {
			$select->where('mode = 1');
		} else {
			$select->where('mode = 0');
		}
		
		if ( $filter ) {
			$select->where($filter);
		}
		$row = $this->fetchRow($select);
		return $row['sum'];
	}

}