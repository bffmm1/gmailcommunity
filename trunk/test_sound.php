<?php

require('libs/misc.php');

$matrix = array();

if ($_GET['w1'] && $_GET['w2']) {
	$matrix[] = array($_GET['w2'], $_GET['w2']);
}else{
	$matrix[] = array('Maria Kirilenko', 'Masha Kirilenko');
	$matrix[] = array('Andrei Neculau', 'Andrei N.');
	$matrix[] = array('Andrei Neculau', 'Neculau Andrei');
}

$result = array();
foreach ($matrix as $row){
	list($w1, $w2) = $row;
	$result[] = array(
		'w1' => $w1,
		'w2' => $w2,
		'levenshtein' => levenshtein($w1, $w2),
		'similar' => similar_text($w1, $w2, $p),
		'similar_%' => $p,
		'metaphone' => metaphone($w1) . " - " . metaphone($w2),
		'soundex' => soundex($w1) . " - " . soundex($w2)
	);
}

print_a($result);

?>