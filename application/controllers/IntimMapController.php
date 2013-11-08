<?php

require_once 'IndexController.php';

class IntimMapController extends IndexController {

	public function indexAction() {
		//start of gathering ankets coords
		include_once 'Ankets.php';
		$ankets = new Ankets();
		$coords = $ankets->get_coords_per_ankets();

		if ( !empty($coords) ) {
			$res = array();
			foreach ($coords as $coord) {
				if ( !empty($coord['coords']) ) {
					$temp_coords = unserialize($coord['coords']);
					if(!empty($coord['photolist'])){
						$photolist=unserialize($coord['photolist']);
						if(isset($photolist['preview'])){$photo=$photolist[$photolist['preview']];}
						else{$photo=array_shift($photolist);}
					}else{
						$photo=false;
					}
					$per_out = $this->view->performer_out[$coord['performer']];
					$res[] = $coord['id'].",".$coord['performer'].",".$temp_coords['lan'].",".$temp_coords['lng'].",".$coord['user_id'].",'".$photo."','".$coord['name']."', '".$per_out."'";
				}
			}

			if ( !empty($res) ) {
				$this->view->coords = "[[".implode("],[", $res)."]]";
			} else {
				$this->view->coords = null;
			}
		}

		//start of gathering  salosn coords
		include_once 'Salons.php';
		$salons = new Salons();
		$salons_coords = $salons->get_coords_per_salons();

		if ( !empty($salons_coords) ) {
			$res = array();
			foreach ($salons_coords as $coord) {
				if ( !empty($coord['coords']) ) {
					$temp_coords = unserialize($coord['coords']);
					if(!empty($coord['photolist'])){
						$photolist=unserialize($coord['photolist']);
						if(isset($photolist['preview'])){
							$photo=$photolist[$photolist['preview']];
						}
						else{$photo=array_shift($photolist);
						}
					}else{
						$photo=false;
					}
					$type_out = $this->view->types_of_salon[$coord['type']];
					$res[] = $coord['id'].",".$coord['type'].",".$temp_coords['lan'].",".$temp_coords['lng'].",".$coord['user_id'].",'".$photo."','".$coord['name']."', '".$type_out."'";
				}
			}
		
			if ( !empty($res) ) {
				$this->view->salons_coords = "[[".implode("],[", $res)."]]";
			} else {
				$this->view->salons_coords = null;
			}
		}
	}

}