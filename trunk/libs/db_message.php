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
	global $allAddresses, $allAddressesReference, $db, $limitRecords, $up, $contactsRelate;

	//Generate log message
	logMsg('USER', 'Fetching relevant messages and putting into files...');

	//Generate query
	$all = $db->query('SELECT c1Body, c4FromAddress, c5ToAddresses, c6CcAddresses, c7BccAddresses FROM MessagesFT_content'.$limitRecords);

	//Generate query
	$all_flags = $db->query('SELECT IsInbox, IsSent FROM Messages');

	//Get all messages
	while ($row = $all->fetch(PDO::FETCH_ASSOC))
	{
		$dumpAddress = array ();
		//Get flags
		$alls_flags = $all_flags->fetch(PDO::FETCH_ASSOC);

		//Parse only inbox and sent
		if (!$alls_flags['IsInbox'] && !$alls_flags['IsSent'])
		{
			continue ;
		}

		//Gauge
		echo ' <span>.</span>';
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

			if (!$addresses) {
				continue ;
			}
			//Retrieve an array with recipients
			$addresses .= ',';
			preg_match_all(REGEXP_RECIPIENTS, $addresses, $matches);
			array_shift($matches);
			$addresses = array_merge($matches[0], $matches[1]);

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
				if (preg_match(REGEXP_FULLNAME_EMAIL, $address, $matches))
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

				if ($type == 'Cc') {
					$type = 'To';
				}
				$dumpAddress[$type][] = $email;

				//Check if sender/recipient is 'interesting'
				//if (isset($allAddresses[$email]))
				//{
				//The path of the file to open
				$filename = $up.'/content/'.sanitizeFilename($email).'.txt';

				//Check if file exists
				//$fileExists = file_exists($filename);

				//Open the file for appending (will create if nonexistent)
				$file = fopen($filename, 'a');

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

					//For romanian consistency
					$body = str_replace('ș', 'ş', $body);
					$body = str_replace('ț', 'ţ', $body);

					//Append body to end of file
					if (!fwrite($file, $body))
					{
						//Generate log message if nothing was written
						logMsg("DEBUG", "Unable to write to: ".basename($filename));
					}

					//Flush the output buffer
					//fflush($file);

					//Close the file
					fclose($file);
				}
				else
				{
					//Generate log message
					logMsg('DEBUG', "Unable to open file: ".basename($filename));
				}
				//}
			}
		}
		unset ($body);

		// memory problems with the following two loops
		for ($i = 0; $i < count($dumpAddress['To']); $i++) {
			$tmp = $dumpAddress['To'];
			if ($dumpAddress['From']) {
				$tmp[] = $dumpAddress['From'][0];
			}
			for ($j = $i+1; $j < count($tmp); $j++) {
				if (($email1 = $allAddressesReference[$tmp[$i]]) && ($email2 = $allAddressesReference[$tmp[$j]])) {
				} else {
					continue ;
				}
				if ($email1 == $email2) {
					continue ;
				}
				logMsg('DEBUG', 'Weighing relationship based on exchanged messages (To/Cc/From) for '.$email1.' and '.$email2.'.');
				$contactsRelate[$email1][$email2]['count'] += 2/count($dumpAddress['To']);
				$contactsRelate[$email2][$email1]['count'] += 2/count($dumpAddress['To']);
			}
			unset ($tmp);
		}

		for ($i = 0; $i < count($dumpAddress['Bcc']); $i++) {
			$tmp = $dumpAddress['Bcc'];
			if ($dumpAddress['From']) {
				$tmp[] = $dumpAddress['From'][0];
			}
			for ($j = $i+1; $j < count($tmp); $j++) {
				if (($email1 = $allAddressesReference[$tmp[$i]]) && ($email2 = $allAddressesReference[$tmp[$j]])) {
				} else {
					continue ;
				}
				if ($email1 == $email2) {
					continue ;
				}
				logMsg('DEBUG', 'Weighing relationship based on exchanged messages (Bcc/From) for '.$email1.' and '.$email2.'.');
				$contactsRelate[$email1][$email2]['count'] += 1/count($dumpAddress['Bcc']);
				$contactsRelate[$email2][$email1]['count'] += 1/count($dumpAddress['Bcc']);
			}
			unset ($tmp);
		}
		unset ($dumpAddress);
	}
	unset ($all);
	unset ($alls);
	unset ($all_flags);
	unset ($alls_flags);
	logMsg('USER', 'Fetching done!');
	logMsg('USER', 'Done weighing relationships based on number of exchanged messages!');
}

function detectLanguage() {
	global $contacts, $up;
	logMsg('USER', "Detecting most often used language for each contact..");
	foreach (array_keys($contacts) as $email) {
		#echo ' <span>.</span>';
		#flush();

		$filename = $up.'/content/'.sanitizeFilename($email).'.txt';
		if (file_exists($filename)) {
			$language = new LangDetect($filename, 1);
			$contacts[$email]['language'] = $language->Analyze();
			unset ($language);
			logMsg('USER', "Language for $email is ".$contacts[$email]['language']);
		}
	}
	logMsg('USER', "Language detection done!");
}

function topWords() {
	global $contacts, $thresholdWords, $up;
	logMsg('USER', "Finding top $thresholdWords most often used words for each contact..");
	foreach (array_keys($contacts) as $email) {
		echo ' <span>.</span>';
		flush();

		$language = $contacts[$email]['language'];
		if ($language != "english" && file_exists($up."/stopwords/$language.txt")) {
			$extraStopWords = " | grep -v -w -f {$up}/stopwords/$language.txt";
		}
		$f = sanitizeFilename($email);
		$filename = $up.'/content/'.$f.'.txt';
		$filenameWords = $up.'/content/'.$f.'_words.txt';
		$filenameWordsStem = $up.'/content/'.$f.'_words_stem.txt';
		chdir($up);
		if (file_exists($filename)) {
			$cmd = 'cat '.$filename.' | tr "A-Z" "a-z" | tr -c "[:alpha:]" " " | tr " " "\n" | sort | uniq -c | sort | grep -v -w -f '.$up.'/stopwords/english.txt | grep -E [a-z]{3,} | tr -d " *[:digit:]*\t" | tail -n '.($thresholdWords*4).' > '.$filenameWords;
			logMsg('DEBUG', "Running CMD: $cmd");
			shell_exec($cmd);
			
			#detect language
			$language = new LangDetect($filenameWords, -1);
			$lang = $language->Analyze();
			print_r($lang);
			$languages = array_keys($lang);
			$contacts[$email]['language'] = $languages[0];
			$language = $languages[0];
			$score = array_shift($score = $lang);
			array_shift($lang);
			
			foreach ($lang as $l => $lscore){
				if ($lscore-$score > 7000){
					break;
				}
				if ($l != 'english') {
					unset ($language);
					$language = $l;
					break;
				}
			}
			if ($language != 'english'){
				logMsg('DEBUG', "Language for $email is ".$contacts[$email]['language']." (but removing also $language stopwords)");
			} else {
				logMsg('DEBUG', "Language for $email is ".$contacts[$email]['language']);
			}
			
			if ($language != 'english'){
				$cmd = 'cat '.$filenameWords.' | tr "A-Z" "a-z" | tr -c "[:alpha:]" " " | tr " " "\n" | sort | uniq -c | sort | grep -v -w -f '.$up.'/stopwords/'.$contacts[$email]['language'].'.txt | grep -E [a-z]{3,} | tr -d " *[:digit:]*\t" | tail -n '.$thresholdWords.' > '.$filenameWords;
				logMsg('DEBUG', "Running CMD: $cmd");
				shell_exec($cmd);
			}
			$contacts[$email]['words'] = array_reverse(array_trim(file($filenameWords)));

			if ($language == 'english' || $language == 'swedish') {
				$languageShort = substr($language, 0, 2);
				$cmd = $up.'/cstlemma/bin/vc2008/cstlemma.exe -e1 -L -f '.$up.'/cstlemma/flexrules_'.$languageShort.' -t- -c"$B" -B"$w\n" < '.$filenameWords.' > '.$filenameWordsStem;
				logMsg('DEBUG', "Running CMD: $cmd");
				shell_exec($cmd);
				$contacts[$email]['wordsStem'] = array_reverse(array_trim(file($filenameWordsStem)));
				array_pop($contacts[$email]['wordsStem']);
			}
		}
	}
	logMsg('USER', "Done!");
}

?>
