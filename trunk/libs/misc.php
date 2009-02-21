<?php

function logMsg($type, $msg) {
	global $log, $logEcho;
	$output = "$type - $msg";
	fwrite($log, "\n$output");
	fflush($log);
	if (($type == 'USER') || ($logEcho === true) || (is_array($logEcho) && in_array($type, $logEcho))) {
		echo "<div class=\"log\">".htmlentities($output, ENT_COMPAT, "UTF-8")."</div>";
		flush();
	}
}

function dumpVar($varName, $var) {
	global $filenameId, $up;
	$filename = $up.'/logs/'.$filenameId.'_'.$varName.'.txt';
	$f = fopen($filename, 'w');
	fwrite($f, print_r($var, true));
	fclose($f);
}

function dumpPhpVar($varName, $var) {
	global $filenameId, $up;
	$filename = $up.'/results/'.$varName.'.txt';
	$f = fopen($filename, 'w');
	fwrite($f, var_export($var, true));
	fclose($f);
}

function shutdown() {
	global $log;
	@fclose($log);
}

function noPunctuation($text) {
	$urlbrackets = '\[\]\(\)';
	$urlspacebefore = ':;\'_\*%@&?!'.$urlbrackets;
	$urlspaceafter = '\.,:;\'\-_\*@&\/\\\\\?!#'.$urlbrackets;
	$urlall = '\.,:;\'\-_\*%@&\/\\\\\?!#'.$urlbrackets;

	$specialquotes = '\'"\*<>';

	$fullstop = '\x{002E}\x{FE52}\x{FF0E}';
	$comma = '\x{002C}\x{FE50}\x{FF0C}';
	$arabsep = '\x{066B}\x{066C}';
	$numseparators = $fullstop.$comma.$arabsep;

	$numbersign = '\x{0023}\x{FE5F}\x{FF03}';
	$percent = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
	$prime = '\x{2032}\x{2033}\x{2034}\x{2057}';
	$nummodifiers = $numbersign.$percent.$prime;

	return preg_replace(
	array (
	// Remove separator, control, formatting, surrogate,
	// open/close quotes.
	'/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
	// Remove other punctuation except special cases
	'/\p{Po}(?<!['.$specialquotes.
	$numseparators.$urlall.$nummodifiers.'])/u',
	// Remove non-URL open/close brackets, except URL brackets.
	'/[\p{Ps}\p{Pe}](?<!['.$urlbrackets.'])/u',
	// Remove special quotes, dashes, connectors, number
	// separators, and URL characters followed by a space
	'/['.$specialquotes.$numseparators.$urlspaceafter.
	'\p{Pd}\p{Pc}]+((?= )|$)/u',
	// Remove special quotes, connectors, and URL characters
	// preceded by a space
	'/((?<= )|^)['.$specialquotes.$urlspacebefore.'\p{Pc}]+/u',
	// Remove dashes preceded by a space, but not followed by a number
	'/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
	// Remove consecutive spaces
	'/ +/'
	),
	' ',
	$text
	);
}

function print_a($TheArray) { // Note: the function is recursive
	echo "<table border=0 cellspacing=1 cellpadding=1>\n";

	$Keys = array_keys($TheArray);
	foreach ($Keys as $OneKey) {
		echo "<tr>\n";

		echo "<td bgcolor='#AAAAAA' valign='top'>";
		echo "<B>".$OneKey."</B>";
		echo "</td>\n";

		echo "<td bgcolor='#EEEEEE' valign='top'>";
		if (is_array($TheArray[$OneKey])) {
			print_a($TheArray[$OneKey]);
		} else {
			echo str_replace("\n", "<br>\n", $TheArray[$OneKey]);
		}
		echo "</td>\n";

		echo "</tr>\n";
	}
	echo "</table>\n";
}

function noDiacritics($text) {
	return iconv('UTF-8', 'US-ASCII//TRANSLIT', $text);
}

function rmdirr($dirname)
{
	// Sanity check
	if (!file_exists($dirname)) {
		return false;
	}

	// Simple delete for a file
	if (is_file($dirname)) {
		return unlink($dirname);
	}

	// Loop through the folder
	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue ;
		}

		// Recurse
		rmdirr( "$dirname/$entry");
	}

	// Clean up
	$dir->close();
	return rmdir($dirname);
}

/**
* Returns a filename based on the $name paramater that has been
* striped of special characters, it's spaces changed to underscores,
* and shortened to 50 characters... but keeping it's extension
* PD: Updated to keep extensions, based on code by timdw at
* <a href="http://forums.codecharge.com/posts.php?post_id=75694
" title="http://forums.codecharge.com/posts.php?post_id=75694
" rel="nofollow">http://forums.codecharge.com/posts.php?post_id=75694
</a> */
function sanitizeFilename($name) {
  $limit = 100;
  $special_chars = array ("#","$","%","^","&","*","!","~","‘","\"","’","'","=","?","/","[","]","(",")","|","<",">",";","\\",",",".");
  $name = preg_replace("/^[.]*/","",$name); // remove leading dots
  $name = preg_replace("/[.]*$/","",$name); // remove trailing dots
  
  $lastdotpos=strrpos($name, "."); // save last dot position
  
  $name = str_replace($special_chars, "", $name);  // remove special characters
  
  $name = str_replace(' ','_',$name); // replace spaces with _
  
  $afterdot = "";
  if ($lastdotpos !== false) { // Split into name and extension, if any.
    if ($lastdotpos < (strlen($name) - 1))
        $afterdot = substr($name, $lastdotpos);
    
    $extensionlen = strlen($afterdot);
    
    if ($lastdotpos < ($limit - $extensionlen) )
        $beforedot = substr($name, 0, $lastdotpos);
    else
        $beforedot = substr($name, 0, ($limit - $extensionlen));
  }
  else   // no extension
   $beforedot = substr($name,0,$limit);

  
  if ($afterdot)
    $name = $beforedot . "." . $afterdot;
  else
    $name = $beforedot;
  
  return $name;

}
?>
