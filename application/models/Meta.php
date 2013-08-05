<?php
class Meta{
	protected $store_path;
	public function __construct(){
		$this->store_path=Zend_Registry::get('config')->path->meta;		
	}
	public function get(){
		return unserialize(file_get_contents($this->store_path));
	}
	public function set($data){
		$file=fopen($this->store_path,'w');
		fwrite($file,serialize($data));
		fclose($file);
	}
}