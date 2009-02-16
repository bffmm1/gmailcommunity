<?php

require('libs/misc.php');
require('libs/db.php');

@ob_end_clean();
register_shutdown_function('shutdown');

global $db_filename, $db;
$andrei_db = '../db/andrei.neculau@gmail.com-GoogleMail#database[1]';
$aron_db = '../db/aron.henriksson@gmail.com-GoogleMail#database[1]';
db_choose();

$log_echo = true;
$log_filename = './logs/' . array_shift(explode('@', basename($db_filename))) . '_' . date('YmdHis') . '.txt';
$log = fopen($log_filename, 'w');
if ($log) {logmsg('FILE', 'Log opened for writing');}

if (!$db_filename){
	logmsg('LOG', 'Database missing');
	die();
} else {
	logmsg('DB', 'Chosen database: ' . array_shift(explode('@', basename($db_filename))));
	db_connect();
}

?>