<?php 

class Ankety_Eception extends Zend_View_Exception {};

include_once 'IndexController.php';
class AnketyController extends IndexController {

	public function novyeAction() {	

		switch ( $this->_getParam('novelty') ) {
			case '1' :
				$start_date = date( 'Y-m-d', time());
				break;
			case '2' :
				$start_date = date( 'Y-m-d', time() - 604800);
				break;
			case '3' :
				$current_month = mktime( 0,0,0,date('n'),date('j'),date('Y') );
				if ( date('n') == 1) {
					$last_month = mktime( 0,0,0,12,date('j'),date('Y') - 1 );
				}
				else
				{
					$last_month = mktime( 0,0,0,date('n') - 1, date('j'), date('Y') );
				}
				$start_date = date( 'Y-m-d', time() - ( $current_month - $last_month));
				break;
			default :
				$start_date = date( 'Y-m-d', strtotime("-1 month"));
		}

		$ankets = new Model_AnketsTest();
		$adapter = $ankets->getLatestAnkets( $start_date, $this->city );

		$paginator = new Zend_Paginator($adapter);
		$paginator->setItemCountPerPage($this->settings['girls_per_page']);
		$paginator->setCurrentPageNumber($page);
		$this->view->ankets = $paginator;
	}

}