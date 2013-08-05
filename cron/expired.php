<?php 

//chdir('/var/www/putanastars.com/cron');

/* get config for db then connect */
require 'config.php';
$_db = mysql_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
mysql_select_db(DB_BASENAME, $_db);

/* enable rus_date */
require '../application/app.php';

/* get config per user from users_config */
$configs= mysql_query('SELECT user_id FROM users_config WHERE moderation = 1', $_db);

while ( $row = mysql_fetch_assoc($configs) ) {
	/* get anket for users that have moderation flag in config */
	$ankets = mysql_query('SELECT id, user_id, photo_finish, name, phone FROM ankets WHERE status >= 20 AND UNIX_TIMESTAMP(photo_finish) <> 0 AND user_id = ' . $row['user_id']);
		
	while ( $anketa = mysql_fetch_assoc($ankets) ) {
		if ( date('Y-m-d', strtotime($anketa['photo_finish'])) == date('Y-m-d', strtotime("+1 week")) ) {
			$header = "Content-type: text/html; charset=utf-8 \r\n";
			$header.= "From: admin@putanastars.com \r\n";

			$subj = "Необходима проверка фотографий.";
			$data = rusdate(strtotime($anketa['photo_finish']));
			$message = "{$data} у анкеты {$anketa['name']} +7 {$anketa['phone']} по адресу <a href='http://putanastars.com/anketa/{$anketa['name']}-{$anketa['id']}'>http://putanastars.com/anketa/{$anketa['name']}-{$anketa['id']}</a>  истекает срок проверки фотографий. Чтобы анкета не была снята с показа, необходимо пройти проверку фотографий заново.<br /> С уважением, робот сайта putanastars.com.<br /> Сообщение отправлено автоматически, отвечать на него не нужно!";

			/* get user mail */
			$user = mysql_query('SELECT mail FROM users WHERE id = ' . $row['user_id'], $_db);
			$user = mysql_fetch_assoc($user);			

			mail($user['mail'], $subj, $message, $header);
		}	

		if ( date('Y-m-d', strtotime($anketa['photo_finish'])) == date('Y-m-d', time()) ) {
			mysql_query('UPDATE ankets SET status = 10 WHERE id = ' . $anketa['id']);			
		}
	}
}

mysql_close($_db);

