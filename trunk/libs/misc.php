<?php

function logmsg($type, $msg){
	global $log, $log_echo;
	$output = "$type - $msg";
	fwrite($log, $output);
	if ($log_echo){
		echo "<div class=\"log\">$output</div>";
	}
}

function shutdown(){
	global $log;
	@fclose($log);
}

?>