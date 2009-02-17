<?php

require('libs/misc.php');

$weightMetaphone = 2/3;
$weightLevenshtein = 2/3*1/3;
$weightSoundex = 1/3*1/3;

$matrix = array();

if ($_GET['w1'] && $_GET['w2']) {
	$matrix[] = array($_GET['w2'], $_GET['w2']);
}else{
	$matrix[] = array('Maria Kirilenko', 'Masha Kirilenko');
	$matrix[] = array('Andrei Neculau', 'Andrei N.');
	$matrix[] = array('Neculau', 'N.');
	$matrix[] = array('Andrei Neculau', 'Neculau Andrei');
	$matrix[] = array('Luminita', 'Luminița');
}

function noDiacriticsParse(&$item){
	$item = noDiacritics($item);
}

array_walk_recursive($matrix, 'noDiacriticsParse');

$result = array();
foreach ($matrix as $row){
	list($w1, $w2) = $row;
	$result[] = array(
		'w1' => $w1,
		'w2' => $w2,
		'levenshtein' => levenshtein($w1, $w2),
//		'similar' => similar_text($w1, $w2, $p),
//		'similar_%' => $p,
		'metaphone' => metaphone($w1) . " - " . metaphone($w2),
		'metaphone_compare' => compareMetaphone($w1, $w2),
		'soundex' => soundex($w1) . " - " . soundex($w2),
		'soundex_compare' => compareSoundex($w1, $w2),
		'compare' => compareComplexMulti(explode(' ', $w1), explode(' ', $w2))
	);
}

print_a($result);

?>