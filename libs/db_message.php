<?php
    
	// parses messagesft_content record by record, collects emails.
	// parse $addressesif at least one email is in $addresses then
	// extract body and append to content/[primary_email].txt
	function getMessages(){
		global $allAddresses, $allAddressesReference;
		// $allAddresses = array of all emails available in $contacts
		// $allAddressesReference['neculau@kth.se'] = 'andrei.neculau@gmail.com' where the latter is the primary email
	}
	
	// for all files content/
	// clean, lematize, stopwords, etc
	// keep only the first 25?!? most used words
	function processMessages(){
		
	}
?>