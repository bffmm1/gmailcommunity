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
		$contacts[$alls['PrimaryEmail']]['secondaryEmails'] = '';
		
		$contacts[$alls['PrimaryEmail']]['name'] = $alls['Name'];
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['secondaryNames'] = '';
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['countTo'] = 0;
		$contacts[$alls['PrimaryEmail']]['countFrom'] = 0;
		$contacts[$alls['PrimaryEmail']]['countCc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countBcc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countTotal'] = 0;
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
		foreach ($alls as $type => $addresses){
			$type = substr($type, 2);
			$type = str_replace('Addresses', '', $type);
			$type = str_replace('Address', '', $type);
			
			$addresses = explode(',', $addresses);
			foreach($addresses as $address){
				// TODO: REGEXP split into $fullname (optional) and $address
				
		
				//If e-mail exists
				//if (array_key_exists($address, $contacts))
				if (isset($contacts['$address'])
				{
					//Increment count
					$contacts[$address]['count'.$type] += 1;
					
					//Name part?
				}
				else //If not, add e-mail to array
				{
					//Necessary?
					$contacts[$address]['secondaryEmail'] = '';
				
					$contacts[$address]['name'] = $alls['Name'];
					
					//Necessary?
					$contacts[$address]['SecondaryNames'] = '';
					
					//Necessary?
					$contacts[$address]['countTo'] = 0;
					$contacts[$address]['countFrom'] = 0;
					$contacts[$address]['countCC'] = 0;
					$contacts[$address]['countBCC'] = 0;
					$contacts[$address]['countTotal'] = 0;
					
					$contacts[$address]['count'.$type] = 1;
				}
			}
		}
		
		// TODO: Need to parse address part
		//$address = $alls['c4FromAddress'];
		
		//TODO: Do similarly for the remaining address fields, in loop if possible 
		// DONE :P
	}
	
	return $contacts;
}

// returns a float from 0 to 100, by applying a match-compare formula check
function compareTwoContacts($c1, $c2){
	function clearName(&$name){
		$name = noPunctuation($name);
		$name = noDiacritics($name);
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
	
	// if no common username or fullname
	if (empty($commonNames) && empty($commonUsernames)){
		$nameSimilarity = 0;
		$usernameSimilarity = 0;
		
		foreach($c1Names as $c1Name){
			$c1NamesSplit = explode(' ', $c1Names);
			$bestSimilarity = 0;
			foreach($c2Names as $c2Name){
				$c2NamesSplit = explode(' ', $c2Names);
				$similarity = compareComplexMulti($c1NamesSplit, $c2NamesSplit);
				logMsg('MATH', "Similarity between '$name' and '$comparisonName' is $similarity");
				
				if ($similarity > $bestSimilarity){
					$bestSimilarity = $similarity;
				}
			}
		}
		return $bestSimilarity;
	}else{
		// full match
		return 100;
	}
}

function matchContacts(){
	global $contacts;
	$contactsWeight = array();
	for (var $i; $i=0; $i<count($contacts)){
		for (var $j; $j=$i+1; $j<count($contacts)){
			$contactsWeight[$i][$j] = compareTwoContacts($contacts[$i], $contact[$j]);
		}
	}
	
	// match based on matrix
}

?>