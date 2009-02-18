<?php

function compareSoundex($v1, $v2) {
	$v1 = soundex($v1);
	$v2 = soundex($v2);

	$v1_major = (ord($v1[0])-ord('A'))*1000;
	$v2_major = (ord($v2[0])-ord('A'))*1000;

	$v1_minor = substr($v1, 1);
	$v2_minor = substr($v2, 1);

	// return similarity percentage
	$total_major = (ord('Z')-ord('A'))*1000;
	$total_minor = 999;
	return (($total_major-abs($v2_major-$v1_major))/$total_major*50)+(($total_minor-abs($v2_minor-$v1_minor))/$total_minor*50);
}

function compareMetaphone($v1, $v2) {
	$v1 = metaphone($v1);
	$v2 = metaphone($v2);

	similar_text($v1, $v2, $p);
	// return similarity percentage
	return $p;
}

function compareComplex($v1, $v2) {
	global $weightLevenshtein, $weightMetaphone, $weightSoundex;
	if (!strlen($v1)) echo "<br>".$v1." - ".$v2;
	$l = 100-levenshtein($v1, $v2)/strlen($v1)*100;
	$m = compareMetaphone($v1, $v2);
	$s = compareSoundex($v1, $v2);

	#echo $v1." - ".$v2. " - $l - $m - $s - " . ($m*2/3 + $l*2/3*1/3 + $s*1/3*1/3) . "<br>";
	return ($m*$weightMetaphone+$l*$weightLevenshtein+$s*$weightSoundex);
}

function compareComplexMulti($a1, $a2) {
	$intersection = array_intersect($a1, $a2);

	// similarity of names, no matter of name order
	$weightIdentical = count($intersection);
	$weightTotal = max(count($a1), count($a2));
	$weightSimilar = 0;

	$a1Available = array_diff($a1, $intersection);
	$weightSimilarTotal = count($a1Available);
	$a2Available = array_diff($a2, $intersection);

	while (count($a1Available)) {
		$v1 = array_shift($a1Available);

		// build comparison array
		$comparison = array ();
		foreach ($a2Available as $v2) {
			// calculate similarity based on weights
			$comparison[] = compareComplex($v1, $v2);
		}

		// get maximal match
		arsort($comparison);
		$index = array_shift(array_keys($comparison));
		$weightSimilar += $comparison[$index]/100;

		unset ($a2Available[$index]);
	}

	#echo "$weightIdentical - $weightSimilar - $weightTotal<br>";
	$similarity = ($weightIdentical+$weightSimilar)/$weightTotal*100;
	return $similarity;
}
?>
