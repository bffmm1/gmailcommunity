<?php

function logMsg($type, $msg){
	global $log, $logEcho;
	$output = "$type - $msg";
	fwrite($log, $output);
	if ($logEcho){
		echo "<div class=\"log\">$output</div>";
	}
}

function shutdown(){
	global $log;
	@fclose($log);
}

/**
 * Strip punctuation from text.
 */
function strip_punctuation( $text )
{
    $urlbrackets    = '\[\]\(\)';
    $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
    $urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
    $urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;
 
    $specialquotes  = '\'"\*<>';
 
    $fullstop       = '\x{002E}\x{FE52}\x{FF0E}';
    $comma          = '\x{002C}\x{FE50}\x{FF0C}';
    $arabsep        = '\x{066B}\x{066C}';
    $numseparators  = $fullstop . $comma . $arabsep;
 
    $numbersign     = '\x{0023}\x{FE5F}\x{FF03}';
    $percent        = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
    $prime          = '\x{2032}\x{2033}\x{2034}\x{2057}';
    $nummodifiers   = $numbersign . $percent . $prime;
 
    return preg_replace(
        array(
        // Remove separator, control, formatting, surrogate,
        // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
        // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
                $numseparators . $urlall . $nummodifiers . '])/u',
        // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
        // Remove special quotes, dashes, connectors, number
        // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
                '\p{Pd}\p{Pc}]+((?= )|$)/u',
        // Remove special quotes, connectors, and URL characters
        // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
        // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
        // Remove consecutive spaces
            '/ +/',
        ),
        ' ',
        $text );
}


function print_a( $TheArray )
{ // Note: the function is recursive
	echo "<table border=0 cellspacing=1 cellpadding=1>\n";

	$Keys = array_keys($TheArray);
	foreach ($Keys as $OneKey)
	{
		echo "<tr>\n";

		echo "<td bgcolor='#AAAAAA' valign='top'>";
		echo "<B>" . $OneKey . "</B>";
		echo "</td>\n";

		echo "<td bgcolor='#EEEEEE' valign='top'>";
		if (is_array($TheArray[$OneKey]))
		{
			print_a($TheArray[$OneKey]);
		}
		else
		{
			echo str_replace("\n", "<br>\n", $TheArray[$OneKey]);
		}
		echo "</td>\n";

		echo "</tr>\n";
	}
	echo "</table>\n";
}

?>