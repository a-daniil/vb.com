<?php

class PaymentController extends Zend_Controller_Action {
	public function resultAction()
	{
		include_once 'WebMoneySettings.php';
		$webmoney_settings = new WebMoneySettings();
		$data = $webmoney_settings->get();

		$response = array(
			'LMI_PAYEE_PURSE'    => $this->_getParam('LMI_PAYEE_PURSE'),
			'LMI_PAYMENT_AMOUNT' => $this->_getParam('LMI_PAYMENT_AMOUNT'),
			'LMI_PAYMENT_NO'     => $this->_getParam('LMI_PAYMENT_NO'),
			'LMI_MODE'           => $this->_getParam('LMI_MODE'),
			'LMI_SYS_INVS_NO'    => $this->_getParam('LMI_SYS_INVS_NO'),
			'LMI_SYS_TRANS_NO'   => $this->_getParam('LMI_SYS_TRANS_NO'),
			'LMI_SYS_TRANS_DATE' => $this->_getParam('LMI_SYS_TRANS_DATE'),
			'LMI_SECRET_KEY'     => $data['SECRET_KEY'],
			'LMI_PAYER_PURSE'    => $this->_getParam('LMI_PAYER_PURSE'),
			'LMI_PAYER_WM'       => $this->_getParam('LMI_PAYER_WM')
		);

		$LMI_HASH = $this->_getParam('LMI_HASH');

		if ( $LMI_HASH == $this->getMd5($response) ) {
			$payment = new Model_Payment();
			$payment->update( array(
				'transact' => $response['LMI_SYS_TRANS_NO'],
				'date'     => date('Y-m-d H:i:s', strtotime($response['LMI_SYS_TRANS_DATE'])),
				'mode'     => $response['LMI_MODE'],
				'wmpayer'  => $response['LMI_PAYER_PURSE'],
				'wmidpayer'=> $response['LMI_PAYER_WM'],
				'wmsysno'  => $response['LMI_SYS_INVS_NO'],
				'state'    => true
			), "number = " . $response['LMI_PAYMENT_NO']);

			$user_id = $payment->getUserIdByNo($response['LMI_PAYMENT_NO']);
			$user = new Model_UsersTest();
			$user->update( array(
				'balance' => $user->getBalance($user_id) + $response['LMI_PAYMENT_AMOUNT']
			), "id = " . $user_id );
		}
	}

	public function createPaymentAction() {
		$LMI_PAYMENT_AMOUNT = $this->_getParam('LMI_PAYMENT_AMOUNT');
		$LMI_PAYMENT_NO     = $this->_getParam('LMI_PAYMENT_NO');
		$LMI_PAYMENT_PURSE  = $this->_getParam('LMI_PAYEE_PURSE');
		$user_id            = $this->_getParam('user_id');
		$date               = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

		$payment = new Model_Payment();
		return $payment->addPayment($LMI_PAYMENT_AMOUNT, $LMI_PAYMENT_NO, $LMI_PAYMENT_PURSE, $user_id, $date);
	}

	protected function getMd5( $response ) {
		foreach ( $response as $k => $v ) {
			$hash .= $v;
		}

		return strtoupper(md5($hash));
	}

}