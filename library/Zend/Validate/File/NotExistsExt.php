<?php
require_once 'Zend/Validate/File/Exists.php';

class Zend_Validate_File_NotExistsExt extends Zend_Validate_Abstract{
	const EXIST='fileExist';
	protected $_messageTemplates = array(self::EXIST=>'File exist');
	private $_file;
	
	public function __construct($file=null){
		if(is_string($file) || !empty($file)){$this->_file=$file;}
	}
	
    public function isValid($value,$file=null){
    	if(!$this->_file){return false;}
    	if(file_exists($this->_file)){
	    	$this->_error(self::EXIST);
	        return false;
    	}
    	return true;
    }
}