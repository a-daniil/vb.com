<?php 

function getDiscount( $user_id, $prices, $performer, $_db ) {
	if ( $performer < 1 || $performer > 8 ) return 0;	
	
	if ( $performer == 8 ) {
		$count = mysql_query('SELECT COUNT(*) as count FROM salons WHERE priority = 1 AND active = 1 AND status = 40 AND user_id = ' . $user_id, $_db);
	} else {
		$count = mysql_query('SELECT COUNT(*) as count FROM ankets WHERE priority = 1 AND active = 1 AND status = 40 AND user_id = ' . $user_id . ' AND performer = ' . $performer, $_db);
	}

	$row = mysql_fetch_assoc($count);

	if ( $row['count'] <= 0 ) {
		return 0;
	}

	$range = array(
		"0" => array(1,2),
		"1" => array(3,4),
		"2" => array(5,6,7,8,9),
		"3" => array(10,11,12,13,14)
	);

	foreach ($range as $k => $v) {
		if ( in_array($row['count'], $v) ) {
			$range = (int)$k; break;
		}
	}

	if ( is_array($range)) {
		$range = 4;
	}

	$discount = 0;
	if ( $performer == 1 ){
		$discount += $range * $prices['girl_hour_discount'];
	} elseif ( $performer == 2 ) {
		$discount += $range * $prices['lesb_hour_discount'];
	} elseif ( $performer == 3 ) {
		$discount += $range * $prices['mass_hour_discount'];
	} elseif ( $performer == 4 ) {
		$discount += $range * $prices['bdsm_hour_discount'];
	} elseif ( $performer == 5 ) {
		$discount += $range * $prices['pair_hour_discount'];
	} elseif ( $performer == 6 ) {
		$discount += $range * $prices['man_hour_discount'];
	} elseif ( $performer == 7 ) {
		$discount += $range * $prices['trans_hour_discount'];
	} elseif ( $performer == 8 ) {
		$discount += $range * $prices['salon_hour_discount'];
	}

	return $discount;
}