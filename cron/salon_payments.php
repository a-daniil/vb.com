<?php 

chdir('/var/www/putanastars.com/cron');

/* get confige for db then connect */
require 'config.php';
$_db = mysql_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
mysql_select_db(DB_BASENAME, $_db);

/* get additional functions */
require 'functions.php';

/* get various anket's performer prices */
$prices = unserialize(file_get_contents('../application/configs/finance-settings.cfg'));

/* get all ankets with priority */
$salons = mysql_query('SELECT user_id, id FROM salons WHERE priority = 1 AND status = 40 AND active = 1', $_db);

while ( $row = mysql_fetch_assoc($salons) ) {
	$spend = ($prices['salon_hour_price'] - getDiscount($row['user_id'], $prices, 8, $_db)) / 24;

	if ($spend <= 0 ) {
		continue;
	}

	/* get balance and spent for user */
	$balance_spent = mysql_query('SELECT balance, spent FROM users WHERE id = ' . $row['user_id']);
	$balance_spent = mysql_fetch_assoc($balance_spent);
	$new_balance = $balance_spent['balance'] - $spend;
	
	/* disable priority when balance of user less then spend per anket */
	if ( $new_balance < $spend ) {
		mysql_query('UPDATE salons SET priority = 0, status = 30 WHERE id = '. $row['id'], $_db );
	}

	if ( $new_balance < 0 ) {
		$new_balance = 0;
	}

	$new_spent = $balance_spent['spent'] + $spend;
	mysql_query('UPDATE users SET balance = ' . $new_balance . ', spent = ' . $new_spent . ' WHERE id = ' . $row['user_id'] . ' LIMIT 1', $_db);
}

