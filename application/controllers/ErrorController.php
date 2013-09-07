<?php

require_once 'IndexController.php';

class ErrorController extends IndexController {

	public function errorAction(){

		$errors = $this->_getParam('error_handler');
		switch ($errors->type){
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
				$this->_helper->viewRenderer->setScriptAction('404');
				$this->view->title = 'HTTP/1.1 Not Found';
				$this->view->message = 'Нет такой страницы';
				break;
			default:
				$this->view->title = 'Ошибка приложения';
				$this->view->message = 'Ошибка приложения';
				break;
		}

		if ( $errors->exception instanceof Ps_Anketa_Exception ) {
			$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
			$this->_helper->viewRenderer->setScriptAction('404');
			$this->view->title = 'HTTP/1.1 Not Found';
			$this->view->message = 'Нет такой страницы';
		}

		if ( $errors->exception instanceof Ps_Salon_Exception ) {
			$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
			$this->_helper->viewRenderer->setScriptAction('404');
			$this->view->title = 'HTTP/1.1 Not Found';
			$this->view->message = 'Нет такой страницы';
		}

		$this->view->request = $errors->request;
		$this->view->exception = $errors->exception;
	}

}