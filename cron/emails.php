<?php 

chdir('/var/www/putanastars.com/cron');

/* get config for db then connect */
require 'config.php';
$_db = mysql_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
mysql_select_db(DB_BASENAME, $_db);

/* enable rus_date */
require '../application/app.php';

/* get additional functions */
require 'functions.php';

/* get various anket's performer prices */
$prices = unserialize(file_get_contents('../application/configs/finance-settings.cfg'));

/* get config per user from users_config */
$configs = mysql_query('SELECT user_id, days_info FROM users_config WHERE balance = 1', $_db);

/* loop throgh configs */
while ( $row = mysql_fetch_assoc($configs) ) {

	/* get ankets with priority for user */
	$ankets = mysql_query('SELECT id, performer FROM ankets WHERE user_id = ' .  $row['user_id'] . ' AND priority = 1 AND status = 40 AND active = 1', $_db);

	/* store spend */
	$spend = 0;
	/* loop through user ankets */
	while ( $ank = mysql_fetch_assoc($ankets) ) {
		if ( $ank['performer'] == 1 ){
			$spend+= $prices['girl_hour_price'];
		} elseif ( $ank['performer'] == 2 ) {
			$spend+= $prices['lesb_hour_price'];
		} elseif ( $ank['performer'] == 3 ) {
			$spend+= $prices['mass_hour_price'];
		} elseif ( $ank['performer'] == 4 ) {
			$spend+= $prices['bdsm_hour_price'];
		} elseif ( $ank['performer'] == 5 ) {
			$spend+= $prices['pair_hour_price'];
		} elseif ( $ank['performer'] == 6 ) {
			$spend+= $prices['man_hour_price'];
		} elseif ( $ank['performer'] == 7 ) {
			$spend+= $prices['trans_hour_price'];
		}
		
		/* get discount */
		$spend -= getDiscount($row['user_id'], $prices, $ank['performer'], $_db);
	}

	$salons = mysql_query('SELECT id FROM salons WHERE user_id = ' . $row['user_id'] . ' AND priority = 1 AND status = 40 AND active = 1', $_db);

	/* loop through user salons */
	while ( $salon = mysql_fetch_assoc($salons) ) {
		$spend += $prices['salon_hour_price'];

		/* get discount */
		$spend -= getDiscount($row['user_id'], $prices, 8, $_db);
	}

	if ( $spend == 0 ) {
		continue;
	}

	/* get balance and spent for user */
	$balance_spent = mysql_query('SELECT balance, spent FROM users WHERE id = ' . $row['user_id']);
	$balance_spent = mysql_fetch_assoc($balance_spent);

	/* break if balance equal or less then 0 */
	if ( $balance_spent['balance'] <= 0 || is_null($balance_spent['balance'])) {
		continue;
	}

	$new_balance = $balance_spent['balance'] - $spend * $row['days_info'];

	/* calculate forecast */
	if ( $spend != 0 ) {
		$days = $balance_spent['balance'] / $spend;
		$forecast = rusdate(time() + $days * 24 * 60 * 60);
		$days = ceil($days);
	}

	/* mail to user if balance less then spend * number of notifications day */
	if ( $new_balance <= $spend * $row['days_info'] ) {
		$header = "Content-type: text/html; charset=utf-8 \r\n";
		$header.= "From: admin@putanastars.com \r\n";

		$subj = "На вашем балансе заканчиваются средства.";
		$message = "<p>На вашем балансе заканчиваются средства. До отключения приоритетного показа анкет осталось {$days} дней. Дата отключения анкет(прогноз) {$forecast} года.<br /> С уважением, робот сайта putanastars.com.<br /> Сообщение отправлено автоматически, отвечать на него не нужно!.</p>";

		/* get user mail */
		$user = mysql_query('SELECT mail FROM users WHERE id = ' . $row['user_id'], $_db);
		$user = mysql_fetch_assoc($user);

		mail($user['mail'], $subj, $message, $header);
	}
}

mysql_close($_db);