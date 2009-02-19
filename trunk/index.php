<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Gmail Community Discovery</title>
    </head>
    <body>
        <?php
        require_once ('config.php');
        
        //getContacts();
        //getContactsFromMessages();
		
		//ksort($contacts);
        
        //matchContacts();
		
		//pruneContacts();
		
        global $allAddresses;
        global $allAddressesReference;
		
        $allAddresses['aron.henriksson@gmail.com'] = "dfs";
        $allAddresses['aronhen@kth.se'] = "fdfd";
        $allAddresses['andrei.neculau@gmail.com'] = "fdf";
        $allAddresses['neculau@kth.se'] = "fd";
        
        $allAddressesReference['andrei.neculau@gmail.com'] = 'andrei.neculau@gmail.com';
        $allAddressesReference['neculau@kth.se'] = 'andrei.neculau@gmail.com';
        $allAddressesReference['aron.henriksson@gmail.com'] = 'aron.henriksson@gmail.com';
        $allAddressesReference['aronhen@kth.se'] = 'aron.henriksson@gmail.com';
		
		getMessages();
        ?>
    </body>
</html>
