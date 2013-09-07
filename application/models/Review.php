<?php

class Model_Review extends Zend_Db_Table_Abstract {

	protected $_name = 'review';

	public function getNotOriginalCount($owner_id)
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('owner_id = ?', $owner_id);
		$select->where('photo_original = ?', 0);
		
		$row = $this->fetchRow($select);
		if ( !$row['count'] ) {
			return false;
		}
		return $row['count'];
	}

	public function getSummaByRealIndividual($owner_id)
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(real_individual) as summa');
		$select->where('owner_id = ?', $owner_id);
		
		$row = $this->fetchRow($select);
		if ( !$row['summa'] ) {
			return false;
		}
		return $row['summa'];
	}
	
	public function getSumScore($owner_id)
	{
		$select = $this->select();
		$select->from($this->_name, 'SUM(score) as summa');
		$select->where('owner_id = ?', $owner_id);
		
		$row = $this->fetchRow($select);
		if ( !$row['summa'] ) {
			return false;
		} 
		return $row['summa'];
	}

	public function getTop100( $performer = false, $city = false )
	{
		if ($performer) {
			$per_query = "AND a." . $performer . " ";
		} else {
			$per_query = "";
		}

		if ($city) {
			$per_query.= "AND a." . $city . " ";
		}

		$sql = "SELECT r.owner_id, avg(r.ratio) as avg_ratio, a.photolist, a.user_id, a.name_eng, count(*) as count FROM
					review as r JOIN ankets as a on r.owner_id = a.id
					WHERE a.status = 40 AND a.priority = 1 " . $per_query . 
					"GROUP BY r.owner_id ORDER by avg_ratio DESC, count DESC LIMIT 100";

		$statement = $this->getAdapter()->query($sql);

		$rows = $statement->fetchAll();
		return $rows;
	}

	public function getCountVoices( $owner_id )
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('owner_id = ?', $owner_id );
		
		$row = $this->fetchRow($select);
		if ( !$row['count']) {
			return false;
		} 
		return $row['count'];
	}
	
	public function getRatioVoices( $owner_id )
	{
		$select = $this->select();
		$select->from($this->_name, 'AVG(ratio) as avg_ratio');
		$select->where('owner_id = ?', $owner_id);
		
		$row = $this->fetchRow($select);
		if ( !$row['avg_ratio']) {
			return false;
		}
		
		return $row['avg_ratio'];
	}
	
	public function getPositiveVoices ( $owner_id )
	{
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('owner_id = ?', $owner_id );
		$select->where('score = 1');
		
		$row = $this->fetchRow($select);
		if ( !$row['count']) {
			return false;
		}
		return $row['count'];
	}
	
	public function getNegativeVoices ( $owner_id ) {
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('owner_id = ?', $owner_id );
		$select->where('score = -1');
		
		$row = $this->fetchRow($select);
		if ( !$row['count']) {
			return false;
		}
		return $row['count'];
	}
	
	public function getNeutralVoices ( $owner_id ) {
		$select = $this->select();
		$select->from($this->_name, 'COUNT(*) as count');
		$select->where('owner_id = ?', $owner_id );
		$select->where('score = 0');
		
		$row = $this->fetchRow($select);
		if ( !$row['count']) {
			return false;
		}
		return $row['count'];
	}
	
	public function getLatest2Reviews( $owner_id )
	{
		$select = $this->select();		
		$select->where('owner_id = ?', $owner_id );
		$select->order('date DESC');
		$select->limit(2);
		
		$rows = $this->fetchAll($select);
		return $rows->toArray();
	}

	public function fetchPaginatorAdapter( $owner_id )
	{
		$select = $this->select();
		$select->where('owner_id = ?' , $owner_id);

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}

	public function countNewReviews( $user_id = null ) {
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, 'COUNT(*) as count');
		$select->join('ankets',
			'ankets.id = review.owner_id');
		$select->where('review.user_id = ?', $user_id);
		//$select->where('review.confirm = true');

		$row = $this->fetchRow($select);
		return $row['count'];
	}

	public function getUserReviews( $user_id = null)
	{
		$select = $this->select()->setIntegrityCheck(false);
		$select->from($this->_name, array(
			'ankets.name as name',
			'ankets.id as ank_id',
			'ankets.name',
			'ankets.price_1h_ap',
			'ankets.price_1h_ex',
			'ankets.metro',
			'ankets.photolist',
			'ankets.age',
			'ankets.height',
			'ankets.weight',
			'ankets.breast',
			'ankets.real',
			'ankets.status',
			'ankets.videolist',
			'review.ratio as r_ratio',
			'review.photo_original as r_photo_original',
			'review.real_individual as r_real_individual',
			'review.comment as r_comment',
			'review.apartment as r_apartment',
			'review.service as r_service',
			'review.score as r_score',
			'review.services as r_services',
			'review.date as r_date',
			'review.photo_original_select as r_photo_original_select'
			));

		$select->join('ankets',
			'ankets.id = review.owner_id');
		// must be uncommited after implement review moderation
		//$select->where('confirm = true');
		if ( $user_id ) {
			$select->where('review.user_id = ?', $user_id);
		}
		//select reviews only by active ankets

		//
		$select->order('review.date DESC');

		$adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
		return $adapter;
	}
}