# vscan
issue tracking vs. public/private ip addresses

Version .002


/* Vulnerability Scan v.02
Bob Kelly


Step 0: vAction = SCANAUDIT
	a. display statuses of current scans
	b. display form for searching scans
	c. display basic use of tool


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
