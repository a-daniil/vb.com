<?

function rusdate($d, $format = 'j %MONTH% Y', $offset = 0)
{
    $montharr = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    $dayarr = array('понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье');
 
    $d += 3600 * $offset;
 
    $sarr = array('/%MONTH%/i', '/%DAYWEEK%/i');
    $rarr = array( $montharr[date("m", $d) - 1], $dayarr[date("N", $d) - 1] );
 
    $format = preg_replace($sarr, $rarr, $format); 
    return date($format, $d);
}

function getLink($value, $boundries, $links) {
	for ( $i = 0; $i < count($boundries); $i++ ) {
		if ( $value <= $boundries[$i][1] && $value >=  $boundries[$i][0] ) {
			if ( $links[$i] ) {
				return "/".$links[$i];
			}
			
			return "#";
		}
	}
	return "#";
}

function debugVarDump ( $par ) {
	echo "<html><head>";
	echo "<meta http-equiv='Content-Type' content='text/html;charset=utf-8'>";
	echo "</head><body>";
	var_dump($par);
	echo "</body></head>";
	die();
}

function checkIfArrayHasValues ( $arr ) {
	foreach ( $arr as $k => $v ) {
		if ( is_array($v) ) {
			$res = checkIfArrayHasValues( $v );
			
			if ( $res ) return true;
		}

		if ( !empty($v) ) return true;
	}

	return false;
}

function getPublicPath() {
	chdir(APP_PATH);
	return realpath("../public");
}
