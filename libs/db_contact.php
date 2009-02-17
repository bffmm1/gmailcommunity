<?php

// returns a multidimensional array with contacts from Contacts table
function getContacts()
{
	global $db, $contacts;
	
	//Generate query
	$all = $db->query('SELECT * FROM Contacts LIMIT 0,100');
	
	//Get all contacts
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		$alls['PrimaryEmail'] = strtolower($alls['PrimaryEmail']);
		/* Generate multidimensional array */
		
		$contacts[$alls['PrimaryEmail']]['email'] = $alls['PrimaryEmail'];
		
		$contacts[$alls['PrimaryEmail']]['usernames'] = array(array_shift(split('@', $alls['PrimaryEmail'])));
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['secondaryEmails'] = array();
		
		$contacts[$alls['PrimaryEmail']]['name'] = $alls['Name'];
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['secondaryNames'] = array();
		
		//Necessary?
		$contacts[$alls['PrimaryEmail']]['countTo'] = 0;
		$contacts[$alls['PrimaryEmail']]['countFrom'] = 0;
		$contacts[$alls['PrimaryEmail']]['countCc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countBcc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countTotal'] = 0;
	}
}

// returns a multidimensional array with contacts from MessagesFT_content table
// takes an optional multidimensional array
function getContactsFromMessages($contacts = '')
{
	global $db, $contacts;
	
	//Generate query
	$all = $db->query('SELECT c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content LIMIT 0,100');
	
	//Get all messages
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		foreach ($alls as $type => $addresses){
			$type = substr($type, 2);
			$type = str_replace('Addresses', '', $type);
			$type = str_replace('Address', '', $type);
		
			$addresses = explode(',', $addresses);
			foreach($addresses as $address){
				$address = trim($address);
				if (!$address) continue;
				
				logMsg('CONTACTS', 'Parsing '.htmlentities($address, ENT_COMPAT, 'UTF-8'));
				// TODO: REGEXP split into $fullname (optional) and $address
				if (preg_match("/^(.+) <(.+)>$/", $address, $matches)){
					array_shift($matches);
					logMsg('CONTACTS', 'Matched: ' . implode(' -- ', $matches));
					$fullname = $matches[0];
					$address = $matches[1];
				}
				
				$address = strtolower($address);
				
				//If e-mail exists
				//if (array_key_exists($address, $contacts))
				if (isset($contacts[$address]))
				{
					logMsg('DB', 'Updating '.$address.' inside the matrix');
					//Increment count
					$contacts[$address]['count'.$type] += 1;
					$contacts[$address]['countTotal'] += 1;
					
					//Name part?
				}
				else //If not, add e-mail to array
				{
					logMsg('CONTACTS', 'Adding '.$address.' to the matrix');
					
					$contacts[$address]['email'] = $address;
		
					$contacts[$address]['usernames'] = array(array_shift(split('@', $address)));
					
					//Necessary?
					$contacts[$address]['secondaryEmails'] = array();
				
					$contacts[$address]['name'] = $alls['Name'];
					
					//Necessary?
					$contacts[$address]['secondaryNames'] = array();
					
					//Necessary?
					$contacts[$address]['countTo'] = 0;
					$contacts[$address]['countFrom'] = 0;
					$contacts[$address]['countCc'] = 0;
					$contacts[$address]['countBcc'] = 0;
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
}

	function clearName(&$name){
		$name = noPunctuation($name);
		$name = noDiacritics($name);
		$name = strtolower($name);
	}
	
// returns a float from 0 to 100, by applying a match-compare formula check
function compareTwoContacts($c1, $c2){
	#logMsg('CONTACTS_MATCH', "Comparing " . $c1['email'] . " with " . $c2['email']);

	// merge primary name with secondary
	$c1Names = $c1['secondaryNames'];
	$c2Names = $c2['secondaryNames'];
	$c1Names[] = $c1['name'];
	$c2Names[] = $c2['name'];
	
	if (count($c1Names) && count($c2Names)){
		// clean names
		array_walk($c1Names, 'clearName');
		array_walk($c2Names, 'clearName');
	
		// check for exact items
		$commonNames = array_intersect($c1Names, $c2Names);
	}
	
	$c1Username = $c1['usernames'][0];
	clearName($c1Username);
	
	$c2Username = $c2['usernames'][0];
	clearName($c2Username);
	
	// check similarity
	if (!empty($commonNames)){
		// full names match
		return 100;
	}elseif (($usernameSimilarity = compareComplex($c1Username, $c2Username)) && $usernameSimilarity > 90){
		// very high username similarity
		logMsg('CONTACTS_MATCH', "Username similarity between '".$c1Username."' and '".$c2Username."' is $usernameSimilarity");
		return $usernameSimilarity;
	}elseif (count($c1Names) && count(c2Names)){
		// check names similarity
		$nameSimilarity = 0;
		
		foreach($c1Names as $c1Name){
			$c1NamesSplit = explode(' ', $c1Names);
			$nameSimilarity = 0;
			foreach($c2Names as $c2Name){
				$c2NamesSplit = explode(' ', $c2Names);
				$similarity = compareComplexMulti($c1NamesSplit, $c2NamesSplit);
				logMsg('CONTACTS_MATCH', "Name similarity between '$name' and '$comparisonName' is $similarity");
				
				if ($similarity > $nameSimilarity){
					$nameSimilarity = $similarity;
				}
			}
		}
		return $nameSimilarity;
	}else{
		return 0;
	}
}

function matchContacts(){
	global $contacts;
	$contactsWeight = array();
	$contactsCopy = $contacts;
	while ($contact1 = array_shift($contactsCopy)){
		$key1 = $contact1['email'];
		foreach ($contactsCopy as $key2 => $contact2){
			$contactsWeight[$key1][$key2] = compareTwoContacts($contact1, $contact2);
		}
	}
	
	// match based on matrix
}

?>