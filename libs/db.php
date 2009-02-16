<?php

function db_choose(){
	global $andrei_db, $aron_db, $db_filename;
	$up = dirname(__FILE__) . '/../';
	if (file_exists($up . $andrei_db)){
		$db_filename = $andrei_db;
	} elseif  (file_exists($up . $aron_db)){
		$db_filename = $aron_db;
	}
}

function db_connect(){
	global $db, $db_filename;
	$up = dirname(__FILE__) . '/../';
	if ($db = new PDO('sqlite:' . $up . $db_filename)) {
		logmsg('DB', 'Db opened ok');
	} else {
		logmsg('ERROR', 'Db cannot be opened');
		die();
	}
}

?>