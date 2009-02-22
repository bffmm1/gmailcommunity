<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Gmail Community Discovery</title>
    </head>
    <body>
        <img src="logo.jpg" style="float:left; margin-right:50px"><h1>Gmail Community Discovery</h1>
		<h3>Aron HENRIKSSON &amp; Andrei NECULAU<br></>Web-Mining project &middot; KTH, Sweden &middot; February 2009 </h3>
        <hr style="clear:both">
        <?php
		require_once ('libs/misc.php');

		@rmdirr('./content');
		@mkdir('./content');
		
		@rmdirr('./results');
		@mkdir('./results');
		
        require_once ('config.php');
        
		logMsg('MEMORY', 'Current allocated memory (initial): '. memory_get_usage());
        getContacts();
		logMsg('MEMORY', 'Current allocated memory (after Contacts): '. memory_get_usage());
        getContactsFromMessages();
		logMsg('MEMORY', 'Current allocated memory (after Contacts from Messages): '. memory_get_usage());
		dumpPhpVar('contactsCount', count($contacts));
		
		ksort($contacts);
        
        matchContacts();
		logMsg('MEMORY', 'Current allocated memory (after Matching Contacts): '. memory_get_usage());
		dumpPhpVar('contactsCountMatched', count($contacts));
		
		pruneContacts();
		logMsg('MEMORY', 'Current allocated memory (after Pruning Contacts): '. memory_get_usage());
		dumpPhpVar('contactsCountPruned', count($contacts));
		
        /*global $allAddresses;
        global $allAddressesReference;
		
        $allAddresses['aron.henriksson@gmail.com'] = "dfs";
        $allAddresses['aronhen@kth.se'] = "fdfd";
        $allAddresses['andrei.neculau@gmail.com'] = "fdf";
        $allAddresses['neculau@kth.se'] = "fd";
        
        $allAddressesReference['andrei.neculau@gmail.com'] = 'andrei.neculau@gmail.com';
        $allAddressesReference['neculau@kth.se'] = 'andrei.neculau@gmail.com';
        $allAddressesReference['aron.henriksson@gmail.com'] = 'aron.henriksson@gmail.com';
        $allAddressesReference['aronhen@kth.se'] = 'aron.henriksson@gmail.com';
		*/
		getMessages();
		logMsg('MEMORY', 'Current allocated memory (after Messages): '. memory_get_usage());
		
		#detectLanguage();
		#logMsg('MEMORY', 'Current allocated memory (after Language Detection): '. memory_get_usage());
		
		topWords();
		logMsg('MEMORY', 'Current allocated memory (after Top Words): '. memory_get_usage());
		
		dumpPhpVar('contacts', $contacts);
		dumpPhpVar('allAddressesReference', $allAddressesReference);
		dumpPhpVar('contacts', $contacts);
		
		relateContacts();
		ksort($contactsRelate);
		foreach ($contactsRelate as $key => $relationship){
			ksort($contactsRelate[$key]);
		}
		dumpPhpVar('contactsRelate', $contactsRelate);
		logMsg('MEMORY', 'Current allocated memory (after Relating Contacts): '. memory_get_usage());
        ?>
		<hr>
		<big><a href="display.php">Now go play with the results!!!</a></big>
    </body>
</html>
