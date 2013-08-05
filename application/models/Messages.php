<?php

class Model_Messages extends Zend_Db_Table_Abstract {
	
	protected $_name = 'messages';
	
	public function addMessage( $send_to, $subject, $body, $user_id, $file = null )
	{
		$row = $this->createRow();		
		
		$row->send_to = $send_to;
		$row->subject = $subject;
		$row->body = $body;
		$row->user_id = $user_id;
		if ( $file ) {
			$row->file = $file;
		}		
		
		$row->save();
		
		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row =  $this->fetchRow($select);
		return $row['id'];
	}	
	
	public function fetchPaginatorAdapter( $send_to, $user_id = null )
	{
		$select = $this->select();
		$select->where('send_to = ?' , $send_to);
		
		if ( $user_id ) {
			$select->orWhere('user_id = ?', $user_id);
		} else {
			$select->orWhere('user_id = ?', $send_to);
		}
		
		$select->order('timestamp DESC');		
		
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function markViewed( $id ) {			
		$row = $this->find($id)->current();
		
		if( $row ){
			$row->viewed = true;
			$row->save();
			return true;
		}
	}
	
	public function getNewMessagesCount( $user_id )
	{
		$select = $this->select()->from($this->_name, 'COUNT(*) as count');
		$select->where('send_to = ?', $user_id );		
		$select->where('viewed IS NOT TRUE');

		$row = $this->fetchRow($select);
		return $row['count'];
	}
	
	public function getMessageUserId ( $id )
	{
		$row = $this->find($id)->current();
		
		return $row['user_id'];
	}
	
}