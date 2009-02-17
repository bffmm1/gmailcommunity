<?php

$a = "asdasd,asdas <asd@asd.se>, asdas <asd@asd>";
preg_match("/(.+@.+)(?:,\s*(.+@.+))*/", $a, $b);
print_r($b);
//phpinfo();

?>