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
        
        $contacts = file_get_contents('./results/contacts.txt');
        $contactsRelate = file_get_contents('./results/contactsRelate.txt');
        $contactsCount = file_get_contents('./results/contactsCount.txt');
        $contactsMatched = file_get_contents('./results/contactsCountMatched.txt');
        $contactsPruned = file_get_contents('./results/contactsCountPruned.txt');
        
        $contacts = eval ('return '.$contacts.';');
        $contactsRelate = eval ('return '.$contactsRelate.';');
        
        function sortContactsByCountTotal($a, $b) {
        	return ($a['countTotal'] < $b['countTotal']);
        
        }
        function sortContactsRelateByStrength($a, $b) {
        	return ($a['strength'] < $b['strength']);
        
        }
        uasort($contacts, 'sortContactsByCountTotal');
		
		
		if ($_GET['c1'] && $contacts[$_GET['c1']]) {
			$tmp = $contactsRelate[$_GET['c1']];
			uasort($tmp, 'sortContactsRelateByStrength');
			$tmp2 = array_keys($tmp);
			foreach ($tmp2 as $email) {
				$tmp[$email]['name'] = $contacts[$email]['name'];
			}
			$contacts[$_GET['c1']]['relatedContacts'] = $tmp;
		}
		if ($_GET['c2'] && $contacts[$_GET['c2']]) {
			$tmp = $contactsRelate[$_GET['c2']];
			uasort($tmp, 'sortContactsRelateByStrength');
			$tmp2 = array_keys($tmp);
			foreach ($tmp2 as $email) {
				$tmp[$email]['name'] = $contacts[$email]['name'];
			}
			$contacts[$_GET['c2']]['relatedContacts'] = $tmp;
		}
        ?>
		<div style="float:left; padding:5px; width:33%; height:600px; overflow:auto;">
	        <h2>Contact 1</h2>
	        <form method="get">
	            <select name="c1">
	                <option value="">(Message Count) Name</option>
	                <? foreach ($contacts as $key=>$contact) { ?>
	                <option value="<?= $key ?>"<?= ($key == $_GET['c1'])?' selected':'' ?>>
	                    (<?= $contact['countTotal'] ?>) <?= $contact['name']?$contact['name']:$contact['email'] ?>
	                </option>
	                <? } ?>
	            </select>
				<input type="submit">
				<? if ($_GET['c1'] && $contacts[$_GET['c1']]) { print_a($contacts[$_GET['c1']]); } ?>
	        </form>
		</div>
		<? if ($_GET['c1'] && $contacts[$_GET['c1']]) { ?>
			<div style="float:left; padding:5px; width:33%; height:600px; overflow:auto;">
		        <h2>Contact 2</h2>
		        <form method="get">
		        	<input type="hidden" name="c1" value="<?= $_GET['c1'] ?>">
		            <select name="c2">
		                <option value="">(Strength) Name</option>
		                <?
							foreach (array_keys($contacts[$_GET['c1']]['relatedContacts']) as $key) {
								$contact = $contacts[$key]; ?>
		                <option value="<?= $key ?>"<?= ($key == $_GET['c2'])?' selected':'' ?>>
		                    (<?= round($contacts[$_GET['c1']]['relatedContacts'][$key]['strength'], 2) ?>) <?= $contact['name']?$contact['name']:$contact['email'] ?>
		                </option>
		                <? } ?>
		            </select>
					<input type="submit">
					<? if ($_GET['c2'] && $contacts[$_GET['c2']]) { print_a($contacts[$_GET['c2']]); } ?>
		        </form>
			</div>
		<? } ?>
		<? if ($_GET['c1'] && $contacts[$_GET['c1']] && $_GET['c2'] && $contacts[$_GET['c2']]) { ?>
			<div style="float:left; padding:5px; width:33%; height:600px; overflow:auto;">
			</div>
		<? } ?>
    </body>
</html>
