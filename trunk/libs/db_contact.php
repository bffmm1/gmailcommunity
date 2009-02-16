<?php

function getContacts()
{
	global $db;
	
	//Generate query
	$all = $db->query('SELECT * FROM Contacts');
	
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		echo($alls['PrimaryEmail']);
	}
}
?>