<?php

function civicrm_direct_debit_admin_settings() {
  $form['settings_general'] = array(
    '#type' => 'fieldset',
    '#title' => t('General settings'),
    //'#collapsible' => TRUE,
  );
  $form['settings_general']['civicrm_direct_debit'] = array(
    '#type' => 'textarea',
    '#title' => t('DD Template'),
    '#default_value' => variable_get('civicrm_direct_debit', '<link href="./sites/all/modules/civicrm_direct_debit/templates/Images/invoice.css" media="all" rel="stylesheet" type="text/css" />
<table align="center" border="0" cellpadding="10" cellspacing="10" width="100%">
	<tbody>
		<tr>
			<td align="left" width="50%">
				<br />
				{contact.display_name}<br />
				{contact.street_address}<br />
				{contact.city}<br />
				{contact.postal_code}<br />
				&nbsp;</td>
			<td align="right" width="50%">
				<img src="./sites/all/modules/civicrm_direct_debit/templates/Images/DD_wide_240x100.png" /></td>
		</tr>
		<tr>
			<td align="left">
				Dear {contact.display_name}<br />
				&nbsp;</td>
			<td align="center">
				{invoice_date}</td>
		</tr>
	</tbody>
</table>
<table>
	<tbody>
		<tr>
			<td align="justify">
				<br />
				I wish to advise you that your subscription payment by Direct Debit has now been set up. I would like you to confirm the details we have for you are correct. Please can you check the details below:</td>
		</tr>
		<tr>
			<td align="left">
				<br />
				<b>Originator Identification Number: </b>940424<br />
				<b>Day of&nbsp;collection : </b>{day_of_collection}<br />
				<b>First Collection Date: </b>{first_collection_date}</td>
		</tr>
	</tbody>
</table>
<table>
	<tbody>
		<tr>
			<td align="left" colspan="3">
				<b>Total amount to be debited : &pound; {amount}</b></td>
		</tr>
		<tr>
			<td align="left">
				<b>Account Name : </b><b>{account_name}</b></td>
			<td align="left">
				<b>Account Number : {account_number}</b></td>
			<td align="left">
				<b>Sort code : {sort_code}</b></td>
		</tr>
	</tbody>
</table>
<table>
	<tbody>
		<tr>
			<td>
				<br />
				For your information, the collections will be made using reference: <b>LCC</b></td>
		</tr>
		<tr>
			<td align="justify">
				<br />
				If any of the details are incorrect please call us as soon as possible on 0800 000 000. However if your details are correct you need do nothing and your Direct Debit will be processed as normal. You have the right to cancel your direct debit at any time. A copy of the Direct Debit Guarantee is attached.</td>
		</tr>
		<tr>
			<td>
				<br />
				<b>Please contact us if you have any queries or problems on the operation of the Direct Debit system.</b></td>
		</tr>
		<tr>
			<td align="left">
				LCC, info@lcc.org.uk</td>
		</tr>
	</tbody>
</table>'),
    '#size' => 100,
    '#required' => TRUE,
    '#description' => t("Enable <strong>CKEditor Module</strong> for a nice visual HTML editor.<br />Please dont remove the tokens(enclosed with curly brackets), which are used to replace the address and contribution details."),
    '#rows' =>  25,
    '#wysiwyg' => TRUE,
  );
  
  return system_settings_form($form);
}

?>