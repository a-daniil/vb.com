<?php

class Model_UsersConfig extends Zend_Db_Table_Abstract {
	
	protected $_name = 'users_config';
	
	public function addUserMessagesConfig( $balance, $days_info, $comments, $moderation, $messages, $news, $user_id )
	{
		$select = $this->select('id');
		$select->where('user_id = ?', $user_id);
		$row =  $this->fetchRow($select);
		$id = $row['id'];
		
		if ( !$row = $this->find($id)->current() ) {
			$row = $this->createRow();
		}
		
		$row->balance = $balance;
		$row->days_info = $days_info;
		$row->comments = $comments;
		$row->moderation = $moderation;
		$row->messages = $messages;
		$row->news = $news;
		$row->user_id = $user_id;
	
		$row->save();
	
		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row =  $this->fetchRow($select);
		return $row['id'];
	}
	
	public function addUserCabMessagesConfig( $comments, $messages, $news, $user_id )
	{
		$select = $this->select('id');
		$select->where('user_id = ?', $user_id);
		$row = $this->fetchRow($select);
		$id = $row['id'];
		
		if ( !$row = $this->find($id)->current() ) {
			$row = $this->createRow();
		}
		
		$row->comments = $comments;
		$row->messages = $messages;
		$row->news = $news;
		$row->user_id = $user_id;
		
		$row->save();
		
		/* emulate behavior of lastInserId(); */
		$select = $this->select('id');
		$select->order('id desc');
		$row =  $this->fetchRow($select);
		return $row['id'];
	}
	
	public function fetchPaginatorAdapter( $user_id )
	{
		$select = $this->select();
		$select->where('user_id = ?' , $user_id);
		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
	
	public function getUserMessagesConfig( $user_id )
	{
		$select = $this->select();
		$select->where('user_id = ?', $user_id);	
		$row =  $this->fetchRow($select);
		if ( $row ) {
			return $row->toArray();
		} 
		return array();
	}
}