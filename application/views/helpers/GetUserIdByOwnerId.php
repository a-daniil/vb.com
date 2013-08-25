<?php

class Zend_View_Helper_GetUserIdByOwnerId extends Zend_View_Helper_Abstract {

	public function GetUserIdByOwnerId( $oid ) {
		$ankets = new Model_AnketsTest();
		$info = $ankets->getById($oid);

		return $info['user_id'];
	}
}