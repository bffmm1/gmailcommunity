<?php

require_once ('libs/compare.php');
require_once ('libs/db.php');
require_once ('libs/db_message.php');
require_once ('libs/langDetect.php');

@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { @ob_end_flush(); }
@ob_implicit_flush(1);

register_shutdown_function('shutdown');
@set_time_limit(0);
@ini_set("memory_limit", "1024M");
@ini_set("max_execution_time", "1200");

$up = dirname(__FILE__);
$andreiDb = '../db/andrei.neculau@gmail.com-GoogleMail#database[1]';
$aronDb = '../db/aron.henriksson@gmail.com-GoogleMail#database';
dbChoose();

$thresholdUsernameSimilarity = 90;
$thresholdNameSimilarity = 70;

$weightMetaphone = 2/3;
$weightLevenshtein = 2/3*1/3;
$weightSoundex = 1/3*1/3;

$thresholdWords = 50;
$meanMultiplier = 4;
$thresholdMean = 5;
$thresholdSharedMessages = 3;

#$logEcho = true;
$logEcho = array('USER', 'MEMORY');
#$limitRecords = " LIMIT 0,100";
$filenameId = array_shift(explode('@', basename($dbFilename))).'_'.date('YmdHis');
$logFilename = $up.'/logs/'.$filenameId.'.txt';
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
