<?php

/*
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
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

// Set whether the direct debit is Paperless. 1 - Paperless , 0 - Otherwise
define( 'CIVICRM_DIRECT_DEBIT_IS_PAPERLESS', 1 );

// Display the Direct Debit block in Online Contribution Page 
define( 'CIVICRM_DIRECT_DEBIT_DISPLAY_ONLINE_BLOCK', 1 );

// Set payment instrument id for Direct Debit
define( 'CIVICRM_DIRECT_DEBIT_PAYMENT_INSTRUMENT_ID', 6 );
  
// Important : Please dont remove the single quotes enclosing the value 
// Give the URL of the Direct Debit batch report
define( 'CIVICRM_DIRECT_DEBIT_BATCH_REPORT_URL',   'civicrm/report/instance/33&reset=1' );

// Activity - New DD SignUp 
define( 'CIVICRM_DIRECT_DEBIT_NEW_DIRECT_DEBIT_SIGNUP_ACTIVITY_ID', '33' );

// Activity - DD Awaiting Signed Declaration
define( 'CIVICRM_DIRECT_DEBIT_AWAITING_SIGNED_DECLARARION_ACTIVITY_ID', '34' );

// Activity - DD Authorization Required
define( 'CIVICRM_DIRECT_DEBIT_AUTHORISAION_REQUIRED_ACTIVITY_ID', '35' );

// Activity - DD First Collection
define( 'CIVICRM_DIRECT_DEBIT_FIRST_COLLECTION_ACTIVITY_ID', '32' );

// Activity - DD Standard Payment
define( 'CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID', '36' );

// Activity - DD Final Payment
define( 'CIVICRM_DIRECT_DEBIT_FINAL_PAYMENT_ACTIVITY_ID', '37' );

// Collection Days Global Array
$collectionDayArray = array('0'=>'--select--', '1'=>'1st' , '5'=>'5th' , '10' => '10th' , '15' => '15th' , '20'=>'20th' , '25'=>'25th' , '30'=>'30th');
$_ENV['collectionDayArray'] = $collectionDayArray;

// Default Collection Day
define( 'CIVICRM_DIRECT_DEBIT_DEFAULT_COLLECTION_DAY', '20' );

// Renewal - Membership Status Id
define( 'CIVICRM_DIRECT_DEBIT_RENEWAL_MEMBERSHIP_STATUS_ID', '8' );

?>
