<?php

require('libs/misc.php');
require('libs/db.php');

@ob_end_clean();
register_shutdown_function('shutdown');

global $dbFilename, $db;
$andreiDb = '../db/andrei.neculau@gmail.com-GoogleMail#database[1]';
$aronDb = '../db/aron.henriksson@gmail.com-GoogleMail#database[1]';
dbChoose();

#$logEcho = true;
#$logEcho = false;

$logEcho = array('CONTACTS_MATCH');
$logFilename = './logs/' . array_shift(explode('@', basename($dbFilename))) . '_' . date('YmdHis') . '.txt';
$log = fopen($logFilename, 'w');
if ($log) {logMsg('FILE', 'Log opened for writing');}

if (!$dbFilename){
	logMsg('LOG', 'Database missing');
	die();
} else {
	logMsg('DB', 'Chosen database: ' . array_shift(explode('@', basename($dbFilename))));
	dbConnect();
}

?>