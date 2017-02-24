<?php

/* Vulnerability Scan v.02
Bob Kelly
bob.kelly@nuance

Step 1: vAction = SCANREAD
	a. Input: Read in Excel Spreadsheet
	b. Do: Add all NEW entries into the ScanList table
	c. Output: Display all NEW entries

Step 2: vAction = SCANMAP
	a. Input: Read all NEW Entries from Database
	b. Do: Map NEW entries to Teams
	c. Output: Create list of potential defects to be created, links to created

Step 3:vAcgtion = SCANLOG
	a. Input: list of potential defects
	b. Do: Create NEW JIRA Tickets
	c. Do: Capture JIRA ids and entering into ScanList
	d. Output: Display all new Scan entires with Jira Id

*/

/*
@TODO
	Default Display for Tool
	Search Features
	Error Codes for Create URL replacing Missing Data
	UPDATE scanlist.ticket_id also updates Status
	CLEAN data of scanlist-map to ip_addresses tables

*/

/*
@DONE
	remove duplicates and add unique to ip_addresses

*/



// Libraries
require $_SERVER['DOCUMENT_ROOT'] . '/classes/smarty/libs/Smarty.class.php'; #smarty templating




// Filename to read in
define('XLSFILENAME', 'temp.xlsx');

// MySQL 
define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', 'kekeke');
define('MYSQL_DATABASE', 'vscan');

//All pages use MySQL so let's get the connection over with
$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if ($mysqli->connect_error) {
	die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}


// JIRA Issue Definitions
define ("JIRA_CREATE_URL", "https://jira.nuancehce.com/secure/CreateIssueDetails!init.jspa?");
define ('JIRA_CREATE_ISSUETYPE', 7);  // 7 = JIRA story
define ('JIRA_CREATE_REPORTER', 'bob_kelly1');

// JIRA priority
define ('JIRA_PRIORITY_CRITICAL', 2); // 8 or above is CRITICAL
define ('JIRA_PRIORITY_MAJOR', 3); // 4 or above is MAJOR
define ('JIRA_PRIORITY_MINOR', 4); // all else is MINOR
define ('VULN_SCAN_CRITICAL', 8); // 8 or above is CRITICAL
define ('VULN_SCAN_MAJOR', 4); // 4 or above is MAJOR


// Actions
define('SCANAUDIT', 0); 
define('SCANREAD', 1);
define('SCANMAP', 2);
define('SCANLOG', 3);
define('SCANSEARCH', 4);
define('PAGEDEFAULT', SCANAUDIT); // Audit is the default page now


//allow us to manipulate data cleanly
$getVars = $_GET;

if (!isset($getVars['vAction'])) {
	$vAction = PAGEDEFAULT; 
} else {
	$vAction = $getVars['vAction']; 
}


//kick of the template
$smarty = new Smarty; 
$smarty->caching = true;
$smarty->cache_lifetime = 120;
$smarty->assign('ProgramName', 'VScanner');


switch ($vAction) {
	
	case SCANAUDIT:
		/* Scan Audit page is a series of stats based on the database
		*/
		$smarty->assign('Title', 'Audit Scanning List'); 
		$sql = 'SELECT COUNT(*)  AS AllScans FROM `scanlist`';
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}
		$countScans = $result->fetch_all(MYSQLI_ASSOC); 
		$smarty->assign('countOfAllScans', $countScans[0]['AllScans']); 
		
		$sql = "SELECT COUNT(*) As NullScans FROM `scanlist` WHERE `status` IS NULL";
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}
		$countScans = $result->fetch_all(MYSQLI_ASSOC); 
		$smarty->assign('countOfNullScans', $countScans[0]['NullScans']); 		
		
		$sql = "SELECT COUNT(*) As OpenScans FROM `scanlist` WHERE `status` = 'OPEN'";
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}
		$countScans = $result->fetch_all(MYSQLI_ASSOC); 
		$smarty->assign('countOfOpenScans', $countScans[0]['OpenScans']); 

		$sql = "SELECT COUNT(*) As ClosedScans FROM `scanlist` WHERE `status` = 'CLOSED'";
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}
		$countScans = $result->fetch_all(MYSQLI_ASSOC); 
		$smarty->assign('countOfClosedScans', $countScans[0]['ClosedScans']); 
		$smarty->display('scanaudit.tpl');
	
		break; 
	case SCANREAD:
		/*  Scan Read page reads in an excel  file XLSFILENAME in the /vscan directory, 
			parses it, enters it into the DB, displaying links to JIRA to create new issues. 
			It is slow and there's a lot of mysql processing to ensure you don't create dupes. 
		*/
		include($_SERVER['DOCUMENT_ROOT'] ."/classes/phpexcel.php");
		$XLS = PHPExcel_IOFactory::load(XLSFILENAME);
		$scanData = $XLS->getActiveSheet()->toArray(null,true,true,true);
		$smarty->assign('Title', 'Read Scanning List'); 
		$smarty->assign('JIRA_URL', JIRA_CREATE_URL);
		$smarty->assign('JIRAIssueType', JIRA_CREATE_ISSUETYPE); 
		$smarty->assign('JIRAReporter', JIRA_CREATE_REPORTER); 
		$smarty->assign('submitCount', count($scanData) -1); 

		
		$cleanScanData = array();
		//translate $scanData into a set of Associative Array so that you can match apples to apples in the comparision
		foreach ($scanData as $scan) {
			// eliminate the first row with headers and the empty rows
			if ($scan['A'] != 'Asset IP Address' && $scan['A'] != '') {
				$tmpScan['vTitle'] = $scan['G'];
			    $tmpScan['vLevel'] = $scan['J'];
				$tmpScan['public_ip'] = $scan['A'];
			    $tmpScan['vUID'] = $scan['D'];
				$cleanScanData[] = $tmpScan; 
			}
		}
		$smarty->assign('newIssuedCount', count($cleanScanData) ); 

		
		//get ScanList table data
		$sql = "select * from SCANLIST ORDER BY `vUID`";
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}		
		$oldScanData = $result->fetch_all(MYSQLI_ASSOC);
		
		// Go through all the CLEAN scans, compare to OLD scans and Only keep the NEW scans
		$newScanData = array();
		foreach ($cleanScanData as $cleanScan) {
			$found = FALSE; 
			foreach ($oldScanData as $oldScan) {
				// compare public_ip AND vUID - if we have NO match, add to $newScanData
				if ($oldScan['public_ip'] == $cleanScan['public_ip'] && $oldScan['vUID'] == $cleanScan['vUID'] ) {
					$found = TRUE;
					break; 
				}
			}
			//if not found add to array
			if (!$found) {
				$newScanData[] = $cleanScan;
			}
		}

		
		// OK, so $newScanData is ready for entry into scanlist table
		// We could do this as a grouping of SQL inserts or as one per one.
		$sql = 'INSERT INTO scanlist (`vTitle`, `vLevel`, `public_ip`, `vUID`, `created`) VALUES ';
		$sqlTeamIPs = '('; 
		foreach ($newScanData as $newScan) {
			$sqlValues = "('". $newScan['vTitle'] ."','". $newScan['vLevel'] ."','". $newScan['public_ip'] ."','". $newScan['vUID'] ."','". date("Y-m-d H:i:s") ."')";
			$sqlTeamIPs .= "'". $newScan['public_ip'] ."',";
			if (!$result = $mysqli->query($sql . $sqlValues)) {
				die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
			} 
		}
		$sqlTeamIPs .= "'0.0.0.0')"; 
		
		//Now fetch an associative array from teams table
		$sql = "SELECT * FROM ip_addresses  INNER JOIN teams ON ip_addresses.team_id=teams.team_id AND ip_addresses.public_ip IN ". $sqlTeamIPs;
		if (!$result = $mysqli->query($sql)) {
			die('Result Error (' . $mysqli->errno . ') ' . $mysqli->error);
		}
		$addressData = $result->fetch_all(MYSQLI_ASSOC);
		
		$goodCount = 0; // count of issues with good data
		for ($i=0; $i<count($newScanData);$i++) {
			$newScanData[$i]['hasAddressData'] = FALSE;
			$newScanData[$i]['desc'] = 'NULL';
			for ($j=0; $j<count($addressData); $j++) {
				if ($addressData[$j]['public_ip'] == $newScanData[$i]['public_ip']){
					$newScanData[$i] = array_merge($newScanData[$i], $addressData[$j]);
					$newScanData[$i]['desc'] = urlencode($newScanData[$i]['vTitle'] .'  Public IP: '. $newScanData[$i]['public_ip'] .' Private IP: '. $newScanData[$i]['private_ip'] .' URL: '. $newScanData[$i]['box_desc']); 
					$newScanData[$i]['priority'] = JIRA_PRIORITY_CRITICAL;
					$newScanData[$i]['hasAddressData'] = TRUE; 
					$goodCount++;
					break; 
				}
			}
		}

		// Grab a count of issues which are Blocked
		// Jeeez, PHP is a hack sometimes 
		$blockedCount = count($newScanData) - $goodCount;
		$smarty->assign('blockedCount', $blockedCount); 
		

		
		$smarty->assign('insertedCount', count($newScanData));
		$smarty->assign('newScanData', $newScanData);
		$smarty->display('scanread.tpl');
		
		
		break;
	case SCANMAP:
		break;
	case SCANLOG:
		break;
	case SCANSEARCH:
		$smarty->assign('Title', 'Search Scanning List'); 
		$smarty->assign('PageError', FALSE);
		if (0 == count(strlen($getvars['searchFor']))) {
			$smarty->assign('PageError', TRUE);
		} else if ($getvars['inColumn'] == 'ticket') {
			$sql = "SELECT * FROM SCANLIST WHERE `ticket_id` = '". $getvars['searchFor'] ."'";
		} else if ($getvars[''] == 'publicip'){
			$sql = "SELECT * FROM SCANLIST WHERE `public_ip` = '". $getvars['searchFor'] ."'";
		} else if ($getvars[''] == 'privateip'){
			$sql = "SELECT * FROM ip_addresses t1 INNER JOIN scanlist t2 ON t1.public_ip = t2.public_ip WHERE t1.private_ip = ". $getvars['searchFor'] ."'";
		} else if ($getvars[''] == 'url') {
			$sql = "SELECT * FROM ip_addresses t1 INNER JOIN scanlist t2 ON t1.public_ip = t2.public_ip WHERE t1.box_desc LIKE '%". $getvars['url'] ."%'";
		}	
		$smarty->display('scanread.tpl');
		break;
}




?>