<?php 

class Model_UsersLog extends Zend_Db_Table_Abstract {

	const SUCCESS = 'success';
	const FAILED  = 'failed';

	protected $_name = 'users_log';

	public function createSuccess( $username ) {
		$this->insert(array(
			"username" => $username,
			"status"   => self::SUCCESS, 
			"date"     => date('Y-m-d H:i:s')
		));
	}

	public function createFailed( $username ) {
		$this->insert(array(
			"username" => $username,
			"status"   => self::FAILED,
			"date"     => date('Y-m-d H:i:s')
		));
	}

	public function hasBanned( $username ) {
		$sql = "SELECT COUNT(*) as count FROM
				(SELECT * FROM users_log
					 WHERE
					 username = '" . $username ."'
					 ORDER BY date DESC
					 LIMIT 5) AS log2
				WHERE
					log2.status = 'failed'
					AND log2.date > DATE_SUB(NOW(), INTERVAL 20 MINUTE)";
		$statement = $this->getAdapter()->query($sql);
		$rows = $statement->fetch();

		if ( $rows['count'] == 5 ) {
			return true;
		} else {
			return false;
		}
	}
}
