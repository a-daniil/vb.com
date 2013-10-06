<?php

class iCrop {
	/* source */
	protected $_src;

	/* dest */
	protected $_dest;

	/* type */
	protected $_type;

	protected $_cropRec = array();

	/* dest */
	public function __construct(){}

	public function load($filename = null) {
		$img_info = getimagesize($filename);

		$this->_type = $img_info[2];
		if( $this->_type == IMAGETYPE_JPEG ) {
			$this->_src = imagecreatefromjpeg($filename);
		} elseif( $this->$_type == IMAGETYPE_GIF ) {
			$this->_src = imagecreatefromgif($filename);
		} elseif( $this->$_type == IMAGETYPE_PNG ) {
			$this->_src = imagecreatefrompng($filename);
		}
	}

	protected function prepareCrop() {
		$this->_cropRec = array(
			'x1' => $_REQUEST['x1'],
			'y1' => $_REQUEST['y1'],
			'x2' => $_REQUEST['x2'],
			'y2' => $_REQUEST['y2'],
			'width' => $_REQUEST['x2'] - $_REQUEST['x1'],
			'height' => $_REQUEST['y2'] - $_REQUEST['y1'],
		);
	}

	protected function copy() {
		$this->_dest = imagecreatetruecolor($this->_cropRec['width'],$this->_cropRec['height']);
		imagecopy($this->_dest, $this->_src, 0, 0, $this->_cropRec['x1'], $this->_cropRec['y1'],
			$this->_cropRec['x2'], $this->_cropRec['y2']);
	}

	public function crop($width, $height) {
		$this->prepareCrop();
		$this->copy();

		$this->_src = imagecreatetruecolor($width, $height);
		imagecopyresized($this->_src, $this->_dest, 0, 0, 0, 0,
			$width, $height, $this->_cropRec['width'], $this->_cropRec['height'] );
	}

	public function save($filename) {
		if( $this->_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->_src,$filename);
		} elseif( $this->_type == IMAGETYPE_GIF ) {
			imagegif($this->_src,$filename);
		} elseif( $this->_type == IMAGETYPE_PNG ) {
			imagepng($this->_src,$filename);
		}
	}
}