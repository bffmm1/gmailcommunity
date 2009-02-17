<?php

function logMsg($type, $msg){
	global $log, $logEcho;
	$output = "$type - $msg";
	fwrite($log, "\n$output");
	if (($logEcho === true) || (is_array($logEcho) && in_array($type, $logEcho))){
		echo "<div class=\"log\">$output</div>";
	}
}

function shutdown(){
	global $log;
	@fclose($log);
}

function compareSoundex($v1, $v2){
	$v1 = soundex($v1);
	$v2 = soundex($v2);
	
	$v1_major = (ord($v1[0])-ord('A'))*1000;
	$v2_major = (ord($v2[0])-ord('A'))*1000;
	
	$v1_minor = substr($v1,1);
	$v2_minor = substr($v2,1);
	
	// return similarity percentage
	$total_major = (ord('Z')-ord('A'))*1000;
	$total_minor = 999;
	return (($total_major-abs($v2_major-$v1_major))/$total_major*50) + (($total_minor-abs($v2_minor-$v1_minor))/$total_minor*50);
}

function compareMetaphone($v1, $v2){
	$v1 = metaphone($v1);
	$v2 = metaphone($v2);
	
	similar_text($v1, $v2, $p);
	// return similarity percentage
	return $p;
}

function compareComplex($v1, $v2){	
	$l = 100-levenshtein($v1, $v2)/strlen($v1)*100;
	$m = compareMetaphone($v1, $v2);
	$s = compareSoundex($v1, $v2);
	
	#echo $v1." - ".$v2. " - $l - $m - $s - " . ($m*2/3 + $l*2/3*1/3 + $s*1/3*1/3) . "<br>";
	return ($m*2/3 + $l*2/3*1/3 + $s*1/3*1/3);
}

function compareComplexMulti($a1, $a2){
				$intersection = array_intersect($a1, $a2);
				
				// similarity of names, no matter of name order
				$weightIdentical = count($intersection);
				$weightTotal = max(count($a1), count($a2));
				$weightSimilar = 0;
				
				$a1Available = array_diff($a1, $intersection);
				$weightSimilarTotal = count($a1Available);
				$a2Available = array_diff($a2, $intersection);
				
				while (count($a1Available)){
					$v1 = array_shift($a1Available);
					
					// build comparison array
					$comparison = array();
					foreach ($a2Available as $v2){
						// calculate similarity based on weights
						$comparison[] = compareComplex($v1, $v2);
					}
					
					// get maximal match
					arsort($comparison);
					$index = array_shift(array_keys($comparison));
					$weightSimilar += $comparison[$index]/100;
					
					unset($a2Available[$index]);
				}
				
				#echo "$weightIdentical - $weightSimilar - $weightTotal<br>";
				$similarity = ($weightIdentical + $weightSimilar) / $weightTotal * 100;
				return $similarity;
}

/**
 * Strip punctuation from text.
 */
function noPunctuation( $text )
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

function noDiacritics($text) {
	return iconv('UTF-8', 'US-ASCII//TRANSLIT', $text);
}
?>