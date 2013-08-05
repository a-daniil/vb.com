<?php

require 'config.php';

$_db=mysql_connect(DB_HOST,DB_LOGIN,DB_PASSWORD);
mysql_select_db(DB_BASENAME,$_db);
$response=mysql_query('SELECT user_id,priority FROM ankets WHERE priority>0 AND active=1 AND shedule&1<<'.(date('N')-1),$_db);
$users=array();
while($row=mysql_fetch_assoc($response)){
	$users[$row['user_id']]=isset($users[$row['user_id']])?$users[$row['user_id']]+$row['priority']:$row['priority'];
}
unset($response);
$users_null=array();
foreach($users as $id=>$value){
	$value=floor($value/24);
	$response=mysql_query('SELECT balance,spent FROM users WHERE id='.$id.' LIMIT 1',$_db);
	$row=mysql_fetch_assoc($response);
	$new_balance=$row['balance']-$value;
	if($new_balance<$value){mysql_query('UPDATE ankets SET priority=0 WHERE user_id='.$id,$_db);}
	if($new_balance<0){$new_balance=0;}
	$new_spent=$row['spent']+$value;
	mysql_query('UPDATE users SET balance='.$new_balance.', spent='.$new_spent.' WHERE id='.$id.' LIMIT 1',$_db);
}
mysql_close($_db);