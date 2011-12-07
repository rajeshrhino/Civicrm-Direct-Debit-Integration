{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}

{literal}
<style type="text/css">
<!--
.fiscal_receipt_report {
    font-size:13px;
    font-family : arial,sans-serif;
}
-->
</style> 
{/literal}

<div class="fiscal_receipt_report">

{* include file="CRM/Report/Form.tpl" *}

{* this div is being used to apply special css *}
    {if $section eq 1}
    <div class="crm-block crm-content-block crm-report-layoutGraph-form-block">
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    </div>
    {elseif $section eq 2}
    <div class="crm-block crm-content-block crm-report-layoutTable-form-block">
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}
	</div>
    {else}
    <div class="crm-block crm-form-block crm-report-field-form-block">
        {include file="CRM/Report/Form/Fields.tpl"}
    </div>
    
    <div class="crm-block crm-content-block crm-report-form-block">
        {*include actions*}
        {include file="CRM/Report/Form/Actions.tpl"}

        {*Statistics at the Top of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" top=true}
      
        {*Statistics at the bottom of the page*}
        {include file="CRM/Report/Form/Statistics.tpl" bottom=true}
      
        {*include the graph*}
        {include file="CRM/Report/Form/Layout/Graph.tpl"}
    
        {*include the table layout*}
        {include file="CRM/Report/Form/Layout/Table.tpl"}    
    	<br />
        
        {include file="CRM/Report/Form/ErrorMessage.tpl"}
    </div>
    {/if}

{if !$printOnly}
    {assign var="batch_ids" value=""}
    {foreach from=$statistics.batch_array item=item key=key}
    {if $batch_ids != null}
        {assign var="batch_ids" value="`$batch_ids`,"}
    {/if}
    {assign var="batch_ids" value="`$batch_ids``$item`"}
    {/foreach}
    
    <div class="crm-block crm-content-block crm-report-form-block"><div class="crm-tasks">
    <table>
      <tr>
      <td>
        <table>
          <!--<tr><td>
            <label><span class="marker" title="This field is required.">
            Press this button to produce customer letters.<br />
            </span>
            <a class="button" href="{crmURL p="civicrm/contribute" q="reset=1&action=produce_customer_letters&batch_ids=$batch_ids"}" accesskey='e'><span>Produce SetUp Letters</span></a></label>
          </td></tr>-->
          <tr><td>
            <span class="marker" title="This field is required.">
            Press this button to generate BACS file for all the contribution in this DD batch.
            <!--<br />(Please note, pressing this button will overwrite the existing PDF fiscal receipt files)-->
            </span><br />
            {* if $statistics.block_produce eq 1 *}
            <!--<a class="button" href="#" onClick="alert('Fiscal Receipts are being produced for few of the contributions in this batch.\nPlease review the selected batches before producing the receipts');return false;"><span>Produce Fiscal Receipts</span></a>-->
            {* elseif $statistics.block_produce eq 0 *}
            <a class="button" href="{crmURL p="civicrm/contribute" q="reset=1&action=produce_csv_file&batch_ids=$batch_ids"}" accesskey='e'><span>Generate BACS File</span></a>
            {* /if *}
          </td></tr>
        </table>
      </td>
      <td valign="middle">
          <table height="100%">
          <tr><td align="center">
          <!--<b><font size="3px"><a href="{crmURL p="civicrm/contribute" q="reset=1&action=export_to_csv&batch_ids=$batch_ids"}">Click here</a> to download the Batch CSV file</font></b>-->
          <!--<b><font size="3px"><a href="{$statistics.csvFileName}" target="_blank">Click here</a> to download the Batch CSV file</font></b>-->
          </td></tr>
          <!--<tr><td align="center">
            <a class="button" href="{crmURL p="civicrm/contribute" q="reset=1&action=export_to_csv&batch_ids=$batch_ids"}" accesskey='e'><span>&nbsp;Export to CSV&nbsp;</span></a>
          </td></tr>-->
          </table>          
      </td>
      </tr>
      </table>  
    </div></div>
{/if}

</div>