<?php

require 'config.php';

$_db=mysql_connect(DB_HOST,DB_LOGIN,DB_PASSWORD);
mysql_select_db(DB_BASENAME,$_db);
mysql_query('UPDATE stats_ankets SET today=0 WHERE today>0',$_db);
mysql_close($_db);