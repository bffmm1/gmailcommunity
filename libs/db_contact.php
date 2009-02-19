<?php

function automaticUsername($username) {
	if ((strpos($username, 'office') !== false)
	|| (strpos($username, 'reply') !== false)
	|| (strpos($username, 'newsletter') !== false)
	|| (strpos($username, 'support') !== false)
	|| (strpos($username, 'info') !== false)
	|| (strpos($username, 'consult') !== false)
	|| (strpos($username, 'list') !== false)
	|| (strpos($username, 'webmaster') !== false)
	|| (strpos($username, 'spam') !== false)
	|| (strpos($username, 'mailing') !== false)) {
		return true;
	}
	return false;
}

// returns a multidimensional array with contacts from Contacts table
function getContacts() {
	global $db, $contacts, $limitRecords;

	//Generate query
	$all = $db->query('SELECT * FROM Contacts'.$limitRecords);

	logMsg('USER', 'Fetching Contacts...');

	//Get all contacts
	while ($alls = $all->fetch(PDO::FETCH_ASSOC)) {
		echo ' .';
		flush();

		// some contacts have no email
		if (!$alls['PrimaryEmail']) {
			continue ;
		}

		$alls['PrimaryEmail'] = strtolower($alls['PrimaryEmail']);
		$username = array_shift(split('@', $alls['PrimaryEmail']));
		if (automaticUsername($username)) {
			continue ;
		}
		// Generate multidimensional array

		$contacts[$alls['PrimaryEmail']]['email'] = $alls['PrimaryEmail'];

		$contacts[$alls['PrimaryEmail']]['usernames'] = array ($username);

		//Necessary?
		$contacts[$alls['PrimaryEmail']]['secondaryEmails'] = array ();

		$contacts[$alls['PrimaryEmail']]['name'] = $alls['Name'];

		//Necessary?
		$contacts[$alls['PrimaryEmail']]['secondaryNames'] = array ();

		//Necessary?
		$contacts[$alls['PrimaryEmail']]['countTo'] = 0;
		$contacts[$alls['PrimaryEmail']]['countFrom'] = 0;
		$contacts[$alls['PrimaryEmail']]['countCc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countBcc'] = 0;
		$contacts[$alls['PrimaryEmail']]['countTotal'] = 0;
	}
	logMsg('USER', 'Fetching done!');
	dumpVar('1_contacts', $contacts);
}

// returns a multidimensional array with contacts from MessagesFT_content table
// takes an optional multidimensional array
function getContactsFromMessages() {
	global $db, $contacts, $limitRecords;

	logMsg('USER', 'Fetching Contacts by parsing available Messages...');

	//Generate query
	$all = $db->query('SELECT c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content'.$limitRecords);
	$all_flags = $db->query('SELECT IsInbox, IsSent FROM Messages');

	//Get all messages
	while ($alls = $all->fetch(PDO::FETCH_ASSOC)) {
		$alls_flags = $all_flags->fetch(PDO::FETCH_ASSOC);

		// parse only inbox and sent
		if (!$alls_flags['IsInbox'] && !$alls_flags['IsSent']) {
			continue ;
		}
		echo ' .';
		flush();

		foreach ($alls as $type=>$addresses) {
			$type = substr($type, 2);
			$type = str_replace('Addresses', '', $type);
			$type = str_replace('Address', '', $type);

			// TODO: new REGEXP needed!!!!! for cases when fullname is email-like
			$addresses .= ',';
			preg_match_all("/(?:(?:[,;\s]*)([^@]+@[^@]+)(?=,))/", $addresses, $matches);
			array_shift($matches);
			$addresses = $matches[0];

			foreach ($addresses as $address) {
				$fullname = '';
				$address = trim($address);
				if (!$address) {
					continue ;
				}

				logMsg('DEBUG', 'Parsing '.$address);

				if (preg_match("/^(.+) <([^@]+@[^@]+)>$/", $address, $matches)) {
					array_shift($matches);
					logMsg('DEBUG', 'Matched: '.join(' -- ', $matches));
					$fullname = $matches[0];
					$fullname = trim($fullname, " \"\\");
					$address = $matches[1];
				}

				$address = strtolower($address);

				// safety
				if (!$address) {
					continue ;
				}

				//If e-mail exists
				if ( isset ($contacts[$address])) {
					logMsg('DEBUG', 'Updating '.$address.' inside the matrix');

					//Increment count
					$contacts[$address]['count'.$type] += 1;
					$contacts[$address]['countTotal'] += 1;

					if ($fullname) {
						if ($contacts[$address]['name']) {
							if ($fullname != $contacts[$address]['name'] && !in_array($fullname, $contacts[$address]['secondaryNames'])) {
								$contacts[$address]['secondaryNames'][] = $fullname;
							}
						} else {
							$contacts[$address]['name'] = $fullname;
						}
					}
				} else {
					$username = array_shift(split('@', $address));
					if (automaticUsername($username)) {
						continue ;
					}
					logMsg('DEBUG', 'Adding '.$address.' to the matrix');

					$contacts[$address]['email'] = $address;
					$contacts[$address]['usernames'] = array ($username);
					$contacts[$address]['secondaryEmails'] = array ();
					$contacts[$address]['name'] = $alls['Name'];
					$contacts[$address]['secondaryNames'] = array ();

					$contacts[$address]['countTo'] = 0;
					$contacts[$address]['countFrom'] = 0;
					$contacts[$address]['countCc'] = 0;
					$contacts[$address]['countBcc'] = 0;
					$contacts[$address]['countTotal'] = 0;

					$contacts[$address]['count'.$type] = 1;
				}
			}
		}
	}
	logMsg('USER', 'Fetching done!');
	dumpVar('2_contacts_and_messages', $contacts);
}

// removes by reference the punctuation, diacritics and converts to lowercase
function cleanName( & $name) {
	$name = noPunctuation($name);
	$name = noDiacritics($name);
	$name = strtolower($name);
	$name = trim($name);
}

// returns a float from 0 to 100, by applying a match-compare formula check
function compareTwoContacts($c1, $c2) {
	global $thresholdUsernameSimilarity, $thresholdNameSimilarity;
	logMsg('DEBUG', "Comparing ".$c1['email']." with ".$c2['email']);

	// merge primary name with secondary names
	$c1Names = $c1['secondaryNames'];
	$c2Names = $c2['secondaryNames'];
	if ($c1['name']) {
		$c1Names[] = $c1['name'];
	}
	if ($c2['name']) {
		$c2Names[] = $c2['name'];
	}

	// if we have names
	if (count($c1Names) && count($c2Names)) {
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
	if (! empty($commonNames)) {
		// full names match
		return 1;
	} elseif ($c1Username && $c2Username && ($usernameSimilarity = compareComplex($c1Username, $c2Username)) && $usernameSimilarity > $thresholdUsernameSimilarity) {
		// very high username similarity
		logMsg('DEBUG', "Username similarity between ".$c1Username." and ".$c2Username." is $usernameSimilarity");
		return 1;
	} elseif (count($c1Names) && count($c2Names)) {
		// check names similarity
		$nameSimilarity = 0;

		foreach ($c1Names as $c1Name) {
			if (!$c1Name) {
				continue ;
			}
			$c1NamesSplit = explode(' ', $c1Name);
			$nameSimilarity = 0;
			foreach ($c2Names as $c2Name) {
				if (!$c2Name) {
					continue ;
				}
				$c2NamesSplit = explode(' ', $c2Name);
				$similarity = compareComplexMulti($c1NamesSplit, $c2NamesSplit);

				if ($similarity > $nameSimilarity) {
					logMsg('DEBUG', "Name similarity between ".$c1Name." and ".$c2Name." is $similarity");
					$nameSimilarity = $similarity;
				}
			}
		}
		return ($nameSimilarity > $thresholdUsernameSimilarity)?1:0;
	} else {
		return 0;
	}
}

function mergeContacts($a) {
	global $contacts;
	$indexPrimary = $a[0];
	$countTotal = 0;
	foreach ($a as $key) {
		if ($contacts[$key]['countTotal'] > $countTotal) {
			$indexPrimary = $key;
			$countTotal = $contacts[$key]['countTotal'];
		}
	}
	foreach ($a as $key) {
		// merge all into a primary contact, and check to see if the to-be-merged contact hasn't been merged already
		if ($key != $indexPrimary && isset ($contacts[$key])) {
			logMsg('USER', "Merge ".$key." into ".$indexPrimary."...");
			# merge contacts[$key] into contacts[indexPrimary]
			$contacts[$indexPrimary]['secondaryEmails'] = array_unique(array_merge($contacts[$indexPrimary]['secondaryEmails'], $contacts[$key]['secondaryEmails']));
			$contacts[$indexPrimary]['secondaryNames'] = array_unique(array_merge($contacts[$indexPrimary]['secondaryNames'], $contacts[$key]['secondaryNames']));
			$contacts[$indexPrimary]['usernames'] = array_unique(array_merge($contacts[$indexPrimary]['usernames'], $contacts[$key]['usernames']));
			$contacts[$indexPrimary]['countTo'] += $contacts[$key]['countTo'];
			$contacts[$indexPrimary]['countFrom'] += $contacts[$key]['countFrom'];
			$contacts[$indexPrimary]['countCc'] += $contacts[$key]['countCc'];
			$contacts[$indexPrimary]['countBcc'] += $contacts[$key]['countBcc'];
			$contacts[$indexPrimary]['countTotal'] += $contacts[$key]['countTotal'];
			unset ($contacts[$key]);
		}
	}
}

function matchContacts() {
	global $contacts;
	logMsg('USER', 'Matching Contacts based on E-mail addresses and names...');

	$contactsWeight = array ();
	$contactsCopy = $contacts;
	while ($contact1 = array_shift($contactsCopy)) {
		$key1 = $contact1['email'];
		logMsg('DEBUG', "Matching ".$contact1['email']."...");
		
		echo ' .';
		flush();
		
		foreach ($contactsCopy as $key2=>$contact2) {
			$contactsWeight[$key1][$key2] = compareTwoContacts($contact1, $contact2);
			logMsg('DEBUG', "Comparing contacts ".$contact1['email']." and ".$contact2['email']." => ".$contactsWeight[$key1][$key2]);
		}
	}
	logMsg('USER', 'Matching done!');
	foreach ($contactsWeight as $key1=>$contactWeight) {
		$toMerge = array ();
		foreach ($contactWeight as $key2=>$score) {
			if ($score) {
				$toMerge[] = $key2;
			}
		}
		if (count($toMerge)) {
			$contactsWeight[$key1] = $toMerge;
		} else {
			unset ($contactsWeight[$key1]);
		}
	}
	dumpVar('3_contacts_matched', $contactsWeight);

	logMsg('USER', 'Merging Contacts based on match results...');
	// match based on matrix
	foreach ($contactsWeight as $key1=>$toMerge) {
		if (count($toMerge)) {
			$toMerge[] = $key1;
			mergeContacts($toMerge);
		}
	}
	logMsg('USER', 'Merging done!');
	dumpVar('4_contacts_merged', $contacts);
}

function pruneContacts() {
	global $contacts, $allAddresses, $allAddressesReference;
	$count = array ();
	foreach ($contacts as $contact) {
		if ($contact['countTotal']){
			$count[] = $contact['countTotal'];
		}
	}
	dumpVar('5_contacts_countTotal', $count);
	$mean = stats_harmonic_mean($count);
	logMsg('USER', 'Mean set to '.$mean.'*2 (messages count) and pruning contacts...');

	$allAddresses = array();
	$allAddressesReference = array();
	
	// delete all contacts that do not reach a certain number of messages
	foreach ($contacts as $key=>$contact) {
		if ($contact['countTotal'] < $mean*2) {
			logMsg('DEBUG', 'Pruning contact '.$key.' ('.$contact['countTotal'].')...');
			unset ($contacts[$key]);
		}else{
			$allAddresses = array_merge($allAddresses, array($key));
			$allAddresses = array_merge($allAddresses, $contacts[$key]['secondaryEmails']);
			$allAddressesReference[$key] = $key;
			foreach ($contacts[$key]['secondaryEmails'] as $key2){
				$allAddressesReference[$key2] = $key;
			}
		}
	}
	logMsg('USER', 'Pruning done!');
	dumpVar('6_contacts_pruned', $contacts);
	
	
}

?>
