{*config_load file="test.conf" section="setup"*}
{include file="header.tpl" title=foo}


<p>Total Count of All Scans: {$countOfAllScans}</p>

<p>Total Count of Open Scans: {$countOfOpenScans}</p>

<p>Total Count of New/Unworked Scans: {$countOfNullScans}</p> 

<p>Total Count of Closed Scans: {$countOfClosedScans}</p>

<p><form action="" method='get'>
<input type='hidden' name='vAction' value='4'><!-- 4 == SCANSEARCH -->
Value: <input type='text' name='searchFor'> in Field: <select name='inColumn'><option value='ticket'>Tickets</option><option value='Public IP'>Public IP</option><option value='Private IP'>Private IP</option><option value='URL'>URL</option></select>
</form>
</p>


{include file="footer.tpl"}
