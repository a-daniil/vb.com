<?

/*
 * Control flow of anket's statuses 
 */


class Ps_Statuses_ControlStatuses 
{
	private function __construct(){}
	
	static function getStatus($current, $action, $params = null) {
		
		switch ( $action ) {
			
			case 'addAnket' :
				return 10;
				
			case 'addCheckPhoto' :
				if ( $current = 10 && $params['count'] >= 3 ) {
					return 20;
				}
				return 10;				
				
			case 'addPhoto' :
				if ( $params['count'] >= 3 && $params['photo_check'] ) {
					return 20;
				} 
				
				return 10;	

			case 'delPhoto' : 
				if ( $params['count'] >= 3 && $params['photo_check'] ) {
					return 20;
				}
				return 10;
				
			case 'priorityWrite' :
				if ( $current == 30 && $params ) {
					return 40;
				} elseif ( $current == 40 && !$params ) {
					return 30;
				}
				return $current;
				
			case 'ankStatusSet' :
				if ( $params['priority'] && $current == 30) {
					return 40;
				} 
				return $current;
				
			case 'addSalon' :
				return 10;
				
			case 'addPhotoSalon' :
				if ( $params['count'] >= 3 ) {
					return 20;
				}
				return 10;
				
			case 'salonStatusSet' :
				if ( $params['priority'] && $current == 30) {
					return 40;
				}
				return $current;
				
			case 'priorityWriteSalon' :
				if ( $current == 30 && $params ) {
					return 40;
				} elseif ( $current == 40 && !$params ) {
					return 30;
				}
				return $current;

			case 'delPhotoSalon' :
				if ( $params['count'] >= 3 ) {
					return 20;
				}
				return 10;			
				
			default :
				return 10;
		}
		
	}
}