<?php

require_once ('db_contact.php');
require_once ('db_message.php');

function dbChoose() {
	global $andreiDb, $aronDb, $dbFilename;
	$up = dirname( __FILE__ ).'/../';
	if (file_exists($up.$andreiDb)) {
		$dbFilename = $andreiDb;
	} elseif (file_exists($up.$aronDb)) {
		$dbFilename = $aronDb;
	}
}

function dbConnect() {
	global $db, $dbFilename;
	$up = dirname( __FILE__ ).'/../';
	if ($db = new PDO('sqlite:'.$up.$dbFilename)) {
		logMsg('DB', 'Db opened ok');
	} else {
		logMsg('ERROR', 'Db cannot be opened');
		die ();
	}
}

?>
