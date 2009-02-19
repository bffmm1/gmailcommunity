<?php

/* 
 * Extracts relevant messages, based on e-mail addresses stored in $allAddresses,
 * and dumps body in text files (one per address: content/[primary_email].txt)
 * corresponding to sender and/or recipient.
 *
 * */
function getMessages()
{
	//$allAddresses - array of all e-mails available in $contacts after pruning
	//$allAddressesReference['neculau@kth.se'] = 'andrei.neculau@gmail.com' where the latter is the primary email
	//$db - reference to database connection
	global $allAddresses, $allAddressesReference, $db, $limitRecords;

	//Generate log message
	logMsg('USER', 'Fetching relevant messages and putting into files...');

	//Generate query
	$all = $db->query('SELECT c1Body, c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content'.$limitRecords);

	//Generate query
	$all_flags = $db->query('SELECT IsInbox, IsSent FROM Messages');

	//Get all messages
	while ($row = $all->fetch(PDO::FETCH_ASSOC))
	{
		//Get flags
		$alls_flags = $all_flags->fetch(PDO::FETCH_ASSOC);

		//Parse only inbox and sent
		if (!$alls_flags['IsInbox'] && !$alls_flags['IsSent'])
		{
			continue ;
		}

		//Gauge
		echo ' .';
		flush();

		//Retrieve body of e-mail
		//Ignore body in the following loop
		$body = array_shift($row);

		//Iterate through the fields
		foreach ($row as $type=>$addresses)
		{
			//Modify result keys to 'From', 'To', 'Cc', 'Bcc'
			$type = substr($type, 2);
			$type = str_replace('Addresses', '', $type);
			$type = str_replace('Address', '', $type);

			//TODO: new REGEXP needed!!!!! for cases when fullname is email-like

			//Retrieve an array with recipients
			$addresses .= ',';
			preg_match_all("/(?:(?:[,;\s]*)([^@]+@[^@]+)(?=,))/", $addresses, $matches);
			array_shift($matches);
			$addresses = $matches[0];

			//Iterate through the recipients (as there may be multiple)
			foreach ($addresses as $address)
			{
				//Clean
				$fullname = '';
				$address = trim($address);

				//Skip if blank
				if (!$address)
				{
					continue ;
				}

				//If there is a match
				if (preg_match("/^(.+) <([^@]+@[^@]+)>$/", $address, $matches))
				{
					//Extract the name and address portions
					array_shift($matches);
					//$fullname = $matches[0];
					//$fullname = trim($fullname, " \"\\");
					$address = $matches[1];
				}

				//Change to lower for consistency
				$address = strtolower($address);

				//Skip if address is empty or if contact was pruned beforehand
				if (!$address || !$allAddressesReference[$address])
				{
					continue ;
				}

				//Get primary e-mail of contact
				$email = $allAddressesReference[$address];

				//Check if sender/recipient is 'interesting'
				//if (isset($allAddresses[$email]))
				//{
				//The path of the file to open
				$filename = 'content/'.str_replace('@', '_', $email).'.txt';

				//Check if file exists
				//$fileExists = file_exists($filename);

				//Open the file for appending (will create if nonexistent)
				$file = fopen(dirname(__FILE__).'/../'.$filename, 'a');

				//If file opens successfully
				if ($file)
				{
					//Generate log message
					//logMsg("USER", "Appending body to file: $filename");

					//Strip body of html tags
					$body = strip_tags($body);

					//Convert html entities/special chars to applicable characters
					$body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
					//$body = htmlspecialchars_decode($body, ENT_QUOTES);

					//Strip body of new lines
					$body = str_replace("\r\n", " ", $body);
					$body = str_replace("\n", " ", $body);

					//Add new line breaks if file has been written to already
					//if ($fileExists)
					//{
					$body = "\n\n".$body;
					//}

					//Append body to end of file
					if (!fwrite($file, $body))
					{
						//Generate log message if nothing was written
						logMsg("DEBUG", "Unable to write to: $filename");
					}

					//Flush the output buffer
					//fflush($file);

					//Close the file
					fclose($file);
				}
				else
				{
					//Generate log message
					logMsg('DEBUG', "Unable to open file: $filename");
				}
				//}
			}
		}
	}
	logMsg('USER', 'Fetching done!');
}

// for all files content/
// clean, lematize, stopwords, etc
// keep only the first 25?!? most used words
function processMessages()
{

}

?>
