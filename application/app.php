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