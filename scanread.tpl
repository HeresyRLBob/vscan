{*config_load file="test.conf" section="setup"*}
{include file="header.tpl" title=foo}


<p>Number of Issues Read in: {$submitCount}</p>

<p>Number of Issues in Spreadsheet: {$newIssuedCount}</p>

<p>Number of Issues To Be Created: {$insertedCount}</p> 

<p>Number of Issues Blocked: {$blockedCount}</p>

<p>
The value of {ldelim}$ProgramName{rdelim} is <b>{$ProgramName}</b>
variable modifier example of {ldelim}$ProgramName|upper{rdelim}
<b>{$ProgramName|upper}</b>
</P>


<div class="divTable" style="width: 60%;">
<div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell">Vuln. Title</div>
<div class="divTableCell">Vuln. Risk Level</div>
<div class="divTableCell">Public IP</div>
<div class="divTableCell">vUID</div>
<div class="divTableCell">CREATE</div></div>
{section name=sec1 loop=$newScanData}
<div class="divTableRow">
<div class="divTableCell">{$newScanData[sec1].vTitle}</div>
<div class="divTableCell">{$newScanData[sec1].vLevel}</div>
<div class="divTableCell">{$newScanData[sec1].public_ip}</div>
<div class="divTableCell">{$newScanData[sec1].vUID}</div>
{if $newScanData[sec1].hasAddressData}
<div class="divTableCell"><a target='new' href='{$JIRA_URL}issuetype={$JIRAIssueType}&pid={$newScanData[sec1].team_pid}&reporter={$JIRAReporter}&components={$newScanData[sec1].jira_components}&customfield_10006={$newScanData[sec1].jira_epic}&priority={$newScanData[sec1].priority}&description={$newScanData[sec1].desc}&summary={$newScanData[sec1].vTitle}'>{$newScanData[sec1].jira_browse}</a></div>
{else}
<div class="divTableCell">Missing Data</div>
{/if}
</div>
{/section}
</div>


<div class="container">
  <h2>Table</h2>    
  <p>The .table-striped class adds zebra-striping to any table row within tbody (not available in IE8):</p>                  
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Vuln. Title</th>
        <th>Vuln. Risk Level</th>
		<th>Public IP</th>
        <th>vUID</th>
		<th>Create URL</th>
      </tr>
    </thead>
    <tbody>
{section name=sec1 loop=$newScanData}
	<tr>
        <td>{$newScanData[sec1].vTitle}</td>
        <td>{$newScanData[sec1].vLevel}</td>
		<td>{$newScanData[sec1].public_ip}</td>
		<td>{$newScanData[sec1].vUID}</td>
	{if $newScanData[sec1].hasAddressData}
		<td><a target='new' href='{$JIRA_URL}issuetype={$JIRAIssueType}&pid={$newScanData[sec1].team_pid}&reporter={$JIRAReporter}&components={$newScanData[sec1].jira_components}&customfield_10006={$newScanData[sec1].jira_epic}&priority={$newScanData[sec1].priority}&description={$newScanData[sec1].desc}&summary={$newScanData[sec1].vTitle}'>{$newScanData[sec1].jira_browse}</a></td>
	{else}
		<td>Missing Data</td>
	{/if}
	</tr>
{/section}
    </tbody>
  </table>
</div>




{include file="footer.tpl"}
