<?php

// returns a multidimensional array with contacts from Contacts table
function getContacts()
{
	global $db, $contacts;
	
	//Generate query
	$all = $db->query('SELECT * FROM Contacts');
	
	//Get all contacts
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		// some contacts have no email
		if (!$alls['PrimaryEmail']) continue;
		
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
function getContactsFromMessages()
{
	global $db, $contacts;
	
	//Generate query
	$all = $db->query('SELECT c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content');
	$all_flags = $db->query('SELECT IsInbox, IsSent FROM Messages');
	
	//Get all messages
	while ($alls = $all->fetch(PDO::FETCH_ASSOC))
	{
		$alls_flags = $all_flags->fetch(PDO::FETCH_ASSOC);
		
		if (!$alls_flags['IsInbox'] && !$alls_flags['IsSent']) continue;
		
		foreach ($alls as $type => $addresses){
			$type = substr($type, 2);
			$type = str_replace('Addresses', '', $type);
			$type = str_replace('Address', '', $type);
		
			$addresses = explode(',', $addresses);
			
			foreach($addresses as $address){
				
				$fullname = '';
				$address = trim($address);
				if (!$address) continue;
				
				logMsg('CONTACTS', 'Parsing '.htmlentities($address, ENT_COMPAT, 'UTF-8'));
				
				if (preg_match("/^(.+) <(.+)>$/", $address, $matches)){
					array_shift($matches);
					logMsg('CONTACTS', 'Matched: ' . implode(' -- ', $matches));
					$fullname = $matches[0];
					$address = $matches[1];
				}
				
				$address = strtolower($address);
				
				// safety
				if (!$address) continue;
				
				//If e-mail exists
				if (isset($contacts[$address]))
				{
					logMsg('DB', 'Updating '.$address.' inside the matrix');
					
					//Increment count
					$contacts[$address]['count'.$type] += 1;
					$contacts[$address]['countTotal'] += 1;
					
					if ($fullname) {
						if ($contacts[$address]['name']){
							if ($fullname != $contacts[$address]['name'] && !in_array($fullname, $contacts[$address]['secondaryNames'])) {
								$contacts[$address]['secondaryNames'][] = $fullname;
							}
						}else{
							$contacts[$address]['name'] = $fullname;
						}
					}
				}
				else //If not, add e-mail to array
				{
					logMsg('CONTACTS', 'Adding '.$address.' to the matrix');
					
					$contacts[$address]['email'] = $address;
					$contacts[$address]['usernames'] = array(array_shift(split('@', $address)));
					$contacts[$address]['secondaryEmails'] = array();
					$contacts[$address]['name'] = $alls['Name'];
					$contacts[$address]['secondaryNames'] = array();
					
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

	function cleanName(&$name){
		$name = noPunctuation($name);
		$name = noDiacritics($name);
		$name = strtolower($name);
		$name = trim($name);
	}
	
// returns a float from 0 to 100, by applying a match-compare formula check
function compareTwoContacts($c1, $c2){
	global $thresholdUsernameSimilarity, $thresholdNameSimilarity;
	#logMsg('CONTACTS_MATCH', "Comparing " . $c1['email'] . " with " . $c2['email']);

	// merge primary name with secondary names
	$c1Names = $c1['secondaryNames'];
	$c2Names = $c2['secondaryNames'];
	if ($c1['name']) $c1Names[] = $c1['name'];
	if ($c2['name']) $c2Names[] = $c2['name'];
	
	// if we have names
	if (count($c1Names) && count($c2Names)){
		// clean names
		array_walk($c1Names, 'cleanName');
		array_walk($c2Names, 'cleanName');
	
		// check for exact items
		$commonNames = array_intersect($c1Names, $c2Names);
	}
	
	$c1Username = $c1['usernames'][0];
	cleanName($c1Username);
	
	$c2Username = $c2['usernames'][0];
	cleanName($c2Username);
	
	// check similarity
	if (!empty($commonNames)){
		// full names match
		return 1;
	}elseif ($c1Username && $c2Username && ($usernameSimilarity = compareComplex($c1Username, $c2Username)) && $usernameSimilarity > $thresholdUsernameSimilarity){
		// very high username similarity
		logMsg('CONTACTS_MATCH', "Username similarity between '".$c1Username."' and '".$c2Username."' is $usernameSimilarity");
		return 1;
	}elseif (count($c1Names) && count(c2Names)){
		// check names similarity
		$nameSimilarity = 0;
		
		foreach($c1Names as $c1Name){
			$c1NamesSplit = explode(' ', $c1Name);
			$nameSimilarity = 0;
			foreach($c2Names as $c2Name){
				$c2NamesSplit = explode(' ', $c2Name);
				$similarity = compareComplexMulti($c1NamesSplit, $c2NamesSplit);
				logMsg('CONTACTS_MATCH', "Name similarity between '".$c1Name."' and '".$c2Name."' is $similarity");
				
				if ($similarity > $nameSimilarity){
					$nameSimilarity = $similarity;
				}
			}
		}
		return ($nameSimilarity > $thresholdUsernameSimilarity)?1:0;
	}else{
		return 0;
	}
}

function mergeContacts($a){
	global $contacts;
	$indexPrimary = $a[0];
	$countTotal = 0;
	foreach($a as $key){
		if ($contacts[$key]['countTotal'] > $countTotal){
			$indexPrimary = $key;
			$countTotal = $contacts[$key]['countTotal'];
		}
	}
	foreach($a as $key){
		if ($key != $indexPrimary){
			# merge contacts[$key] into contacts[indexPrimary]
			$contacts[$indexPrimary]['secondaryEmails'] = array_unique(array_merge($contacts[$indexPrimary]['secondaryEmails'],  $contacts[$key]['secondaryEmails']));
			$contacts[$indexPrimary]['secondaryNames'] = array_unique(array_merge($contacts[$indexPrimary]['secondaryNames'],  $contacts[$key]['secondaryNames']));
			$contacts[$indexPrimary]['usernames'] = array_unique(array_merge($contacts[$indexPrimary]['usernames'],  $contacts[$key]['usernames']));
			$contacts[$indexPrimary]['countTo'] += $contacts[$key]['countTo'];
			$contacts[$indexPrimary]['countFrom'] += $contacts[$key]['countFrom'];
			$contacts[$indexPrimary]['countCc'] += $contacts[$key]['countCc'];
			$contacts[$indexPrimary]['countBcc'] += $contacts[$key]['countBcc'];
			$contacts[$indexPrimary]['countTotal'] += $contacts[$key]['countTotal'];
			unset($contacts[$key]);
		}
	}
}

function matchContacts(){
	global $contacts;
	print_a($contacts); die();
	$contactsWeight = array();
	$contactsCopy = $contacts;
	while ($contact1 = array_shift($contactsCopy)){
		$key1 = $contact1['email'];
		foreach ($contactsCopy as $key2 => $contact2){
			$contactsWeight[$key1][$key2] = compareTwoContacts($contact1, $contact2);
		}
	}
	
	// match based on matrix
	foreach ($contactsWeight as $key1 => $a){
		$toMerge = array();
		foreach ($a as $key2 => $score) {
			if ($score) {
				$toMerge[] = $key2;
			}
		}
		if (count($toMerge)){
			$toMerge[] = $key1;
			mergeContacts($toMerge);
		}
	}
	
	print_a($contacts);
}

?>