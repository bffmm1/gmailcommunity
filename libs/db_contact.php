<?php

// returns a multidimensional array with contacts from Contacts table
function getContacts()
{
	global $db;
	
	//Generate query
	$all = $db->query('SELECT * FROM Contacts');
	
	//Get all contacts
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		/* Generate multidimensional array */
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['SecondaryEmail'] = '';
		
		$contacts[$alls['PrimaryEmail']]['PrimaryName'] = $alls['Name'];
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['SecondaryNames'] = '';
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['CountTo'] = 0;
		$contacts[$alls['PrimaryEmail']]['CountFrom'] = 0;
		$contacts[$alls['PrimaryEmail']]['CountCC'] = 0;
		$contacts[$alls['PrimaryEmail']]['CountBCC'] = 0;
		$contacts[$alls['PrimaryEmail']]['CountTotal'] = 0;
	}
	
	return $contacts;
}

// returns a multidimensional array with contacts from MessagesFT_content table
// takes an optional multidimensional array
function getContactsFromMessages($contacts = '')
{
	global $db;
	
	//Generate query
	$all = $db->query('SELECT c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content');
	
	//Get all messages
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		// TODO: Need to parse address part
		$address = $alls['c4FromAddress'];
		
		//If e-mail exists
		if (array_key_exists($address, $contacts))
		{
			//Increment count
			$contacts[$address]['CountFrom'] += 1;
			
			//Name part?
		}
		else //If not, add e-mail to array
		{
			//Necessary?
			$contacts[$address]['SecondaryEmail'] = '';
		
			$contacts[$address]['PrimaryName'] = $alls['Name'];
			
			//Necessary?
			$contacts[$address]['SecondaryNames'] = '';
			
			//Necessary?
			$contacts[$address]['CountTo'] = 0;
			$contacts[$address]['CountFrom'] = 1;
			$contacts[$address]['CountCC'] = 0;
			$contacts[$address]['CountBCC'] = 0;
			$contacts[$address]['CountTotal'] = 0;
		}
		
		//TODO: Do similar for the remaining address fields, in loop if possible 
	}
	
	return $contacts;
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