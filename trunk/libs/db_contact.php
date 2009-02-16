<?php

function getContacts()
{
	global $db;
	
	//Generate query
	$all = $db->query('SELECT * FROM Contacts');
	
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		//echo($alls['PrimaryEmail']);
	}
}

function getContactsFromMessages(){
	return;
}

// returns a float from 0 to 100, by applying a match-compare formula check
function compareTwoContacts($c1, $c2){
	function clearName($name){
		$name = strip_punctuation($name);
		$name = strtolower($name);
	}
	
	// merge primary name with secondary
	$c1Names = array_unshift($c1['secondaryNames'], $c1['name']);
	$c2Names = array_unshift($c2['secondaryNames'], $c2['name']);

	// clean names
	array_walk($c1Names, 'clearName');
	array_walk($c2Names, 'clearName');
	
	// clean usernames
	array_walk($c1Usernames, 'clearName');
	array_walk($c2Usernames, 'clearName');
	
	// check for exact items
	$commonNames = array_intersect($c1Names, $c2Names);
	$commonUsernames = array_intersect($c1Usernames, $c2Usernames);
	
	if (empty($commonNames) && empty($commonUsernames)){
		$nameSimilarity = 0;
		$usernameSimilarity = 0;
		foreach($c1Names as $c1Name){
			$c1NamesSplit = explode(' ', $c1Names);
			foreach($c2Names as $c2Name){
				$c2NamesSplit = explode(' ', $c2Names);
				
				$weightNamesSplit = count(array_intersect($c1NamesSplit, $c2NamesSplit)) / max(count($c1NamesSplit), count($c2NamesSplit));
				
				
				$nameSimilarity = max($nameSimilarity, );
			}
		}
	}else{
		// full match
		return 100;
	}
}

function matchContacts(){
	global $contacts;
	$contactsWeight = array();
	for (var $i; $i=0; $i<count($contacts)){
		for (var $j; $j=0; $j<count($contacts)){
			$contactsWeight[$i][$j] = compareContacts($contacts[$i], $contact[$j]);
		}
	}
}

?>