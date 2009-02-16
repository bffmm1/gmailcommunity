<?php

$w1 = 'Maria';
$w2 = 'Masha';

echo "Lev $w1-$w2 " . levenshtein($w1, $w2);

echo "<br><br>Similar $w1-$w2 " . similar_text($w1, $w2, $p) . " $p%";

echo '<br><br>Metaphone ' . $w1 . ' - ' . metaphone($w1);
echo '<br>Metaphone ' . $w2 . ' - ' . metaphone($w2);

echo '<br><br>Soundex ' . $w1 . ' - ' . soundex($w1);
echo '<br>Soundex ' . $w2 . ' - ' . soundex($w2);

?>