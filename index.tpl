{*config_load file="test.conf" section="setup"*}
{include file="header.tpl" title=foo}


<p>Number of Issues Read in: {$submitCount}</p>

<p>Number of Issues Read New: {$newIssuedCount}</p>

<p>Number of Issues To Be Created: {$insertedCount}</p>

<p>
The value of {ldelim}$ProgramName{rdelim} is <b>{$ProgramName}</b>

variable modifier example of {ldelim}$ProgramName|upper{rdelim}

<b>{$ProgramName|upper}</b>
</P>

<P>
An example of section looped key values:

    {section name=sec1 loop=$contacts}
        phone: {$contacts[sec1].phone}
        <br>

            fax: {$contacts[sec1].fax}
        <br>

            cell: {$contacts[sec1].cell}
        <br>
    {/section}
</P>

<P>
An example of section looped key values:<br>

    {section name=sec1 loop=$newScanData}
        vTitle: {$newScanData[sec1].vTitle}
        <br>
        vLevel: {$newScanData[sec1].vLevel}
        <br>
        public_ip: {$newScanData[sec1].public_ip}
        <br>
		vUID: {$newScanData[sec1].vUID}
        <br>

    {/section}
</P>

<div class="divTable" style="width: 60%;">
<div class="divTableBody">
<div class="divTableRow">
<div class="divTableCell">Vuln. Title</div>
<div class="divTableCell">Vuln. Risk Level</div>
<div class="divTableCell">Public IP</div>
<div class="divTableCell">vUID</div>
</div>
{section name=sec1 loop=$newScanData}
<div class="divTableRow">
<div class="divTableCell">{$vTitle}</div>
<div class="divTableCell">{$vLevel}</div>
<div class="divTableCell">{$public_ip}</div>
<div class="divTableCell">{$vUID}</div>
<div class="divTableCell">{$vTitle}</div>
</div>
{/section}

</div>

{include file="footer.tpl"}
