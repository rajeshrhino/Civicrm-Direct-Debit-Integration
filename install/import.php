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
 * $Id: index.php
 * To import the table used for civicrm ticketing system
 */
 
 
function runCodeTableImport()  {
    session_start( );                               
                                            
    require_once '../../civicrm/civicrm.config.php'; 
    require_once 'CRM/Core/Config.php'; 
    require_once 'CRM/Core/DAO.php';
    
    $config =& CRM_Core_Config::singleton();
    
    CRM_Utils_System::authenticateScript( true );
    
$sql = "
CREATE TABLE IF NOT EXISTS `civicrm_value_direct_debit_display_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Default MySQL primary key',
  `entity_id` int(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entity_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_dd_block` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    //echo $sql;exit;
    
    CRM_Core_DAO::executeQuery( $sql);
    
    echo "CiviCRM Direct Debit - table(s) created Successful";
    
    //CRM_Utils_System::redirect( );
    
    CRM_Utils_System::civiExit( );
}


runCodeTableImport(); 

?>
