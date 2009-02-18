<?php

require_once ('libs/misc.php');
require_once ('libs/compare.php');
require_once ('libs/db.php');

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { @ob_end_flush(); }
@ob_implicit_flush(1);

register_shutdown_function('shutdown');
@set_time_limit(0);
@ini_set("memory_limit", "128M");

global $dbFilename, $db;
$andreiDb = '../db/andrei.neculau@gmail.com-GoogleMail#database[1]';
$aronDb = '../db/aron.henriksson@gmail.com-GoogleMail#database[1]';
dbChoose();

$thresholdUsernameSimilarity = 90;
$thresholdNameSimilarity = 70;

$weightMetaphone = 2/3;
$weightLevenshtein = 2/3*1/3;
$weightSoundex = 1/3*1/3;

#$logEcho = true;
#$logEcho = array('USER');
$filenameId = array_shift(explode('@', basename($dbFilename))).'_'.date('YmdHis');
$logFilename = './logs/'.$filenameId.'.txt';
$log = fopen($logFilename, 'w');
if ($log) {
	logMsg('DEBUG', 'Log opened for writing');
}

if (!$dbFilename) {
	logMsg('USER', 'Database missing');
	die ();
} else {
	logMsg('USER', 'Chosen database: '.array_shift(explode('@', basename($dbFilename))));
	dbConnect();
}

?>
