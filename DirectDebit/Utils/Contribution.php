<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.0                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2011                                |
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
 * @copyright CiviCRM LLC (c) 2004-2011
 * $Id$
 *
 */
              
class DirectDebit_Utils_Contribution {
    
    /**
     * Given an array of contributionIDs, add them to a batch
     *
     * @param array  $contributionIDs (reference ) the array of contribution ids to be added
     * @param int    $batchID - the batchID to be added to
     *
     * @return array             (total, added, notAdded) ids of contributions added to the batch
     * @access public
     * @static
     */
    static function addContributionToBatch( $contributionIDs, $batchID ) {
        $date = date('YmdHis');
        $contributionsAdded    = array( );
        $contributionsNotAdded = array( );

        require_once "DirectDebit/Utils/DirectDebit.php";
        require_once "CRM/Contribute/BAO/Contribution.php";
        require_once 'CRM/Core/DAO/EntityBatch.php';
        require_once "CRM/Core/BAO/Address.php";
        require_once "CRM/Contact/BAO/Contact.php";
        require_once "CRM/Utils/Address.php";

        foreach ( $contributionIDs as $contributionID ) {
           	$batchContribution =& new CRM_Core_DAO_EntityBatch( );
            $batchContribution->entity_table = 'civicrm_contribution';
			      $batchContribution->entity_id    = $contributionID;
		
			// check if the selected contribution id already in a batch
			// if not, add to batchContribution else keep the count of contributions that are not added
		
            if ( $batchContribution->find( true ) ) {
                $contributionsNotAdded[] = $contributionID;
                continue;
            }
                
            // get additional info
            // get contribution details from Contribution using contribution id
            $params    = array( 'id' => $contributionID );
            CRM_Contribute_BAO_Contribution::retrieve( $params, $contribution, $ids );
            $contactId = $contribution['contact_id'];

            // check if contribution is valid for gift aid
            if ( DirectDebit_Utils_DirectDebit::isEligibleForGiftAid( $contactId, $contribution['receive_date'] , $contributionID ) ) {
                $batchContribution->batch_id = $batchID;
                $batchContribution->save( );

                // get display name
                $displayName = CRM_Contact_BAO_Contact::displayName( $contactId );

                // get Address & Postal Code from Address
                $params  = array( 'contact_id' => $contactId,
                                  'is_primary' => 1 );
                $address = CRM_Core_BAO_Address::getValues( $params );
                $address = $address[1];
				        //adds all address lines to the report
				        $fullFormatedAddress = CRM_Utils_Address::format($address);;

                // Use addslashes function to avoid error of the fields have single quotes
                $displayName = addslashes($displayName); 
                $fullFormatedAddress = addslashes($fullFormatedAddress);
                $address['postal_code'] = addslashes($address['postal_code']);

                // FIXME: check if there is customTable method
                //$query = "INSERT INTO civicrm_value_direct_debit_details 
                //          (entity_id, name, address, post_code, amount) 
                //          VALUES ({$contributionID}, '{$displayName}', '{$fullFormatedAddress}', '{$address['postal_code']}', {$contribution['total_amount']})
                //          ON DUPLICATE KEY UPDATE name = '{$displayName}' , address = '{$fullFormatedAddress}' , post_code = '{$address['postal_code']}' , amount = '{$contribution['total_amount']}'";
                $query = "UPDATE civicrm_value_direct_debit_details SET added_to_direct_debit = 1 WHERE entity_id = '$contributionID'";
                CRM_Core_DAO::executeQuery( $query );

                $contributionsAdded[] = $contributionID;
            } else {
                $contributionsNotAdded[] = $contributionID;
			} 
		}
        
        if ( ! empty( $contributionsAdded ) ) {
            // if there is any extra work required to be done for contributions that are batched,
            // should be done via hook
            DirectDebit_Utils_Hook::batchContributions( $batchID, $contributionsAdded );
        }

        return array( count($contributionIDs), 
                      count($contributionsAdded), 
                      count($contributionsNotAdded) );
    }

	/*
     * this function check contribution is valid for adding to a direct debit batch or not:
     * 1 - if contribution_id already inserted in batch_contribution
     */
	static function _validateContributionToBatch( &$contributionIDs )  {
		  $contributionsAdded    	   = array( );
		  $contributionsAlreadyAdded = array( );
      $contributionsNotValid 	   = array( );
                
        require_once "DirectDebit/Utils/DirectDebit.php";
        require_once "CRM/Core/DAO/EntityBatch.php";
        require_once "CRM/Contribute/BAO/Contribution.php";
        if (!empty($contributionIDs)) {
            foreach ( $contributionIDs as $activity_id => $contributionID ) {
               	$batchContribution =& new CRM_Core_DAO_EntityBatch( );
                $batchContribution->entity_table = 'civicrm_contribution';
    			      $batchContribution->entity_id    = $contributionID;
                
    			       // check if the selected contribution id already in a batch
    			       // if not, increment $numContributionsAdded else keep the count of contributions that are already added
    			       if ( ! $batchContribution->find( true ) ) {
                    // get contact_id, & contribution receive date from Contribution using contribution id
                    $params = array( 'id' => $contributionID);
                    CRM_Contribute_BAO_Contribution::retrieve( $params, $defaults, $ids );
                    
                    // check if contribution is not valid for gift aid, increment $numContributionsNotValid
					
                    if ( DirectDebit_Utils_DirectDebit::isEligibleForGiftAid( $defaults['contact_id'], $defaults['receive_date'], $contributionID ) ) {
                        $contributionsAdded[$activity_id] = $contributionID;
                    } else {
                        $contributionsNotValid[$activity_id] = $contributionID;
                    }
    			       } else {
                    $contributionsAlreadyAdded[$activity_id] = $contributionID;
                  }
    		    }
        }      
        return array( count($contributionIDs), $contributionsAdded, $contributionsAlreadyAdded, $contributionsNotValid );
    }

	/*
     * this function returns the array of batchID & title
     */
	static function getBatchIdTitle( $orderBy = 'id' ){
        $query = "SELECT * FROM civicrm_batch ORDER BY " . $orderBy;
        $dao   =& CRM_Core_DAO::executeQuery( $query);
       
		$result	= array();
        while ( $dao->fetch( ) ) {
            $result[$dao->id] = $dao->id." - ".$dao->label;
        }
        return $result;
	}

    /*
     * this function returns the array of contribution
     * @param array  $contributionIDs an array of contribution ids
     * @return array $result an array of contributions
     */
    static function getContributionDetails( $contributionIds ) {
        
        if ( empty( $contributionIds ) ) {
            return;
        }
        $query = " SELECT contribution.id, contact.id contact_id, contact.display_name, contribution.total_amount, contribution_type.name,
                          contribution.source, contribution.receive_date, batch.label , dd_details.activity_id as activity_id FROM civicrm_contribution contribution
                   LEFT JOIN civicrm_contact contact ON ( contribution.contact_id = contact.id )
                   LEFT JOIN civicrm_contribution_type contribution_type ON ( contribution_type.id = contribution.contribution_type_id )
                   LEFT JOIN civicrm_entity_batch entity_batch ON ( entity_batch.entity_id = contribution.id ) 
                   LEFT JOIN civicrm_value_direct_debit_details dd_details ON ( dd_details.entity_id = contribution.id )
                   LEFT JOIN civicrm_batch batch ON ( batch.id = entity_batch.batch_id ) 
                   WHERE contribution.id IN (" . implode(',', $contributionIds ) . ")" ;
        
        $dao    = CRM_Core_DAO::executeQuery( $query );
        $result	= array( );
        while ( $dao->fetch( ) ) {
            $result[$dao->id]['contribution_id']   = $dao->id;
            $result[$dao->id]['contact_id']        = $dao->contact_id;
            $result[$dao->id]['display_name']      = $dao->display_name;
            $result[$dao->id]['total_amount']      = $dao->total_amount;
            $result[$dao->id]['contribution_type'] = $dao->name;
            $result[$dao->id]['source']            = $dao->source;
            $result[$dao->id]['receive_date']      = $dao->receive_date;
            $result[$dao->id]['batch']             = $dao->label;
            
            require_once 'api/v2/Activity.php';
            $params = array(
             'activity_id' => $dao->activity_id
            );
        
            $act = civicrm_activity_get($params);
            $activity_type_id = $act['result']['activity_type_id'];
            $result[$dao->id]['activity_type_id']  = $activity_type_id;
             
        } 
        
        return $result;
    }
    
        /*
     * this function returns the latest batch ID
     */
	static function getLatestBatchId( $orderBy = 'id' ){
        $query = "SELECT * FROM civicrm_batch ORDER BY " . $orderBy;
        $dao   =& CRM_Core_DAO::executeQuery( $query);
        
        $result = array();
        
        while ( $dao->fetch( ) ) {
            $result[] = $dao->id;
            //$result = array($dao->id);
            return $result;
            break;
        }       
	}
    
  static function buildForm( &$form, $childID ) {
    
  }  
    
}
