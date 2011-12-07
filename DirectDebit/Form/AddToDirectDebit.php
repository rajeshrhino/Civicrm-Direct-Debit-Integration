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

require_once 'CRM/Contribute/Form.php';

/**
 * This class provides the functionality to add contributions to 
 * a direct debit batch 
 */

class DirectDebit_Form_AddToDirectDebit extends CRM_Contribute_Form {

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
	function preProcess()
    {	
	  parent::preProcess( );
		
		    $this->_contributionIds = $this->getContributionsList();
		
		
		    $this->_rejectedcontributionIds = $this->getRejectedContributionsList();
		
        require_once 'DirectDebit/Utils/Contribution.php';
			//echo "total contribution ids=".count($this->_contributionIds);	
		    list( $total, $added, $alreadyAdded, $notValid ) =
            DirectDebit_Utils_Contribution::_validateContributionToBatch( $this->_contributionIds );
			//echo "<br>total=".$total;		
			//echo "<br>added=".count($added);
			//echo "<br>al added=".count($alreadyAdded);
			//echo "<br>not valid=".count($notValid);
			
		    $this->assign('selectedContributions', $total);
			$this->assign('totalAddedContributions', count($added));
		    $this->assign('alreadyAddedContributions', count($alreadyAdded));
		    $this->assign('notValidContributions', count($notValid));

        // get details of contribution that will be added to this batch.
        $contributionsAddedRows = array( );
        $contributionsAddedRows = DirectDebit_Utils_Contribution::getContributionDetails ( $added );
        //$this->assign('contributionsAddedRows', $contributionsAddedRows );
        
        while(list($key, $values) = @each($contributionsAddedRows)) {
            $contributionsAddedRowsByActivity[$values['activity_type_id']][] = $values;     
        }
        
        $this->assign('FirstTimeCollectionActivityId', CIVICRM_DIRECT_DEBIT_FIRST_COLLECTION_ACTIVITY_ID);
        $this->assign('StandardPaymentActivityId', CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID);
        $this->assign('FinalPaymentActivityId', CIVICRM_DIRECT_DEBIT_FINAL_PAYMENT_ACTIVITY_ID);
        
        //print_r ($contributionsAddedRowsByActivity);exit;
        
        $this->assign('contributionsAddedRowsByActivity', $contributionsAddedRowsByActivity );
        
        // get details of contribution thatare already added to this batch.
		//print_r($alreadyAdded);
        $contributionsAlreadyAddedRows = array( );
        $contributionsAlreadyAddedRows = DirectDebit_Utils_Contribution::getContributionDetails ( $alreadyAdded );
        $this->assign( 'contributionsAlreadyAddedRows', $contributionsAlreadyAddedRows );       
        
        if (count($this->_rejectedcontributionIds) > 0) {
            $this->assign( 'contributionsRejectionsRows', count($this->_rejectedcontributionIds) );
            $contributionsRejectionRows = array( );
            $contributionsRejectionRows = DirectDebit_Utils_Contribution::getContributionDetails ( $this->_rejectedcontributionIds );
            while(list($key, $values) = @each($contributionsRejectionRows)) {
                $contributionsRejectionRowsByActivity[$values['activity_type_id']][] = $values;     
            }
            
            $this->assign( 'contributionsRejectionRowsByActivity', $contributionsRejectionRowsByActivity );            
        }else{
            $this->assign( 'contributionsRejectionsRows', 0 );
        } 
	}
	
	function getContributionsList( ) {
	
	     $dtStartDay = date("YmdHis", strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
	     $dtEndDay = date("YmdHis", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));

       $dtStartDay = '20111001000000';
       $dtEndDay = '20111031235959';

       // FIX ME : Change to - activity_date_time <= %3"
	     $activity_types = CIVICRM_DIRECT_DEBIT_FIRST_COLLECTION_ACTIVITY_ID.",".CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID.",".CIVICRM_DIRECT_DEBIT_FINAL_PAYMENT_ACTIVITY_ID;
	     $activity_sql = "SELECT * FROM civicrm_activity ca WHERE ca.activity_type_id IN ($activity_types) AND ca.status_id = %1
                                    AND activity_date_time >={$dtStartDay} AND activity_date_time <={$dtEndDay}";
	                                                                                                       // AND activity_date_time <= %2  
	     $activity_params  = array( 
                                    1 => array( 1   , 'Integer' ) ,
                                    2 => array( date('Y-m-d H:i:s')   , 'String' ) 
                                    );
        								
       $activity_dao = CRM_Core_DAO::executeQuery( $activity_sql, $activity_params );
       $t = array();
       while($activity_dao->fetch()) {
	        
            //print_r ($activity_dao);
            $activity_id = $activity_dao->id;
            $t[] = $activity_id;
            $sql = "SELECT * FROM civicrm_value_activity_bank_relationship WHERE entity_id = '$activity_id'";
            $dao = CRM_Core_DAO::executeQuery( $sql );
            $dao->fetch();
            $mandate_id = $dao->bank_id;
            
            //$sql = "SELECT dd.entity_id as contribution_id FROM civicrm_value_direct_debit_details dd LEFT JOIN civicrm_contribution cc ON cc.id = dd.entity_id WHERE dd.mandate_id = '$mandate_id' AND dd.added_to_direct_debit IS NULL AND dd.activity_id = '$activity_id'";
            $sql = "SELECT dd.entity_id as contribution_id FROM civicrm_value_direct_debit_details dd LEFT JOIN civicrm_contribution cc ON cc.id = dd.entity_id WHERE dd.mandate_id = '$mandate_id' AND dd.added_to_direct_debit IS NULL AND dd.activity_id = '$activity_id'";
            $dao = CRM_Core_DAO::executeQuery($sql);
            if ($dao->fetch())
                $contributionIds[$activity_id] = $dao->contribution_id;
            $count++;    
                  
       }	   
       //print_r ($contributionIds);exit;
       return $contributionIds; 
   }     
    
   function getRejectedContributionsList( ) {
	
	     $dtStartDay = date("YmdHis", strtotime(date('m').'/01/'.date('Y').' 00:00:00'));
	     $dtEndDay = date("YmdHis", strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
	     
       $dtStartDay = '20111001000000';
       $dtEndDay = '20111031235959';
       
       // FIX ME : Change to - activity_date_time <= %3"
	     $activity_types = CIVICRM_DIRECT_DEBIT_FIRST_COLLECTION_ACTIVITY_ID.",".CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID.",".CIVICRM_DIRECT_DEBIT_FINAL_PAYMENT_ACTIVITY_ID;
	     $activity_sql = "SELECT * FROM civicrm_activity ca WHERE ca.activity_type_id IN ($activity_types) AND ca.status_id = %1";
                                    //AND activity_date_time >={$dtStartDay} AND activity_date_time <={$dtEndDay}
	                                                                                                       // AND activity_date_time <= %2  
	     $activity_params  = array( 
                                    1 => array( 1   , 'Integer' ) ,
                                    2 => array( date('Y-m-d H:i:s')   , 'String' ) 
                                    );
       $activity_dao = CRM_Core_DAO::executeQuery( $activity_sql, $activity_params );
       
       while($activity_dao->fetch()) {
            ///print_r ($activity_dao);echo "<hr />";exit;
            $activity_id = $activity_dao->id;
            
            $sql = "SELECT * FROM civicrm_value_activity_bank_relationship WHERE entity_id = '$activity_id'";
            $dao = CRM_Core_DAO::executeQuery( $sql );
            $dao->fetch();
            $mandate_id = $dao->bank_id;
            
            $sql = "SELECT dd.entity_id as contribution_id FROM civicrm_value_direct_debit_details dd LEFT JOIN civicrm_contribution cc ON cc.id = dd.entity_id WHERE dd.mandate_id = '$mandate_id' AND dd.process_as_rejection = 1 AND dd.added_to_direct_debit = 1 AND dd.activity_id = '$activity_id'";
            $dao = CRM_Core_DAO::executeQuery($sql);
            if ($dao->fetch())
                $contributionIds[$activity_id] = $dao->contribution_id;
                  
       }
       //print_r ($contributionIds);exit;
       return $contributionIds; 
    }  
        
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
		$attributes	= CRM_Core_DAO::getAttribute( 'CRM_Core_DAO_Batch' );

		$this->add( 'text', 'title', 
                    ts('Batch Label'),
                    $attributes['label'], true );

		$this->addRule( 'title', ts('Label already exists in Database.'),
						'objectExists', array( 'CRM_Core_DAO_Batch', $this->_id, 'label' ) );
		
		$this->add( 'textarea', 'description', ts('Description:') . ' ', 
                    $attributes['description'] );

        $defaults = array( 'label' =>	ts( 'Gift Aid Batch %1 (%2)'),
                           '%1' =>		date('d-m-Y'),
                           '%2' => 		date('H:i:s')
		);
        $this->setDefaults( $defaults );

    $config =& CRM_Core_Config::singleton( );
    $this->assign('userFrameworkBaseURL' ,$config->userFrameworkBaseURL);
                           
		$this->addDefaultButtons( ts('Add to batch') );
    }
	

   
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
		
		$params = $this->controller->exportValues( );
		
		$batchParams = array();
		$batchParams['label'      ] = $params['title'];
    $batchParams['name'       ] = CRM_Utils_String::titleToVar($params['title'], 63 );
		$batchParams['description'] = $params['description'];
		$batchParams['batch_type' ] = "Gift Aid";

    $session =& CRM_Core_Session::singleton( );
		$batchParams['created_id' ] = $session->get( 'userID' );
		$batchParams['created_date'] = date("YmdHis");

    require_once 'CRM/Core/Transaction.php';
    $transaction = new CRM_Core_Transaction( );

		require_once 'CRM/Core/BAO/Batch.php';
		$createdBatch   =& CRM_Core_BAO_Batch::create( $batchParams );
		$batchID        = $createdBatch->id;
		$batchLabel     = $batchParams['label'];
    
    if ($_POST['contributionRejections']) {
        $rejectionsCount = @count($_POST['contributionRejections']);
		    $contribution_rejections = @implode( ',' , $_POST['contributionRejections']);
        if (!empty($contribution_rejections)) {
            $sql = "UPDATE civicrm_entity_batch SET batch_id = {$batchID} WHERE entity_id IN ({$contribution_rejections})";
            $dao = CRM_Core_DAO::executeQuery($sql);
        }  
    }
    		
		$this->_contributionIds = $this->getContributionsList();
		
		require_once 'DirectDebit/Utils/Contribution.php';
		list( $total, $added, $notAdded ) =
            DirectDebit_Utils_Contribution::addContributionToBatch( $this->_contributionIds, $batchID );

    if ( $added <= 0 ) {
        // rollback since there were no contributions added, and we might not want to keep an empty batch
        $transaction->rollback( );
        $status = ts('Could not create batch "%1", as there were no valid contribution(s) to be added.', 
                     array(1 => $batchLabel));
    } else {
        $status = array( ts('Added Contribution(s) to %1'       , array(1 => $batchLabel)),
                         ts('Total Selected Contribution(s): %1', array(1 => $total)) );
        if ( $added ) {
            $status[] = ts('Total Contribution(s) added to batch: %1', array(1 => $added));
        }
        if ( $notAdded ) {
            $status[] = ts('Total Contribution(s) already in batch or not valid: %1', array(1 => $notAdded));
        }
        if ( $rejectionsCount ) {
            $status[] = ts('Total rejected Contribution(s) moved to batch: %1', array(1 => $rejectionsCount));
        }
        $status = implode( '<br/>', $status );
    }
    $transaction->commit( );
    CRM_Core_Session::setStatus( $status );
    
    $url_array = @explode( '&' , CIVICRM_DIRECT_DEBIT_BATCH_REPORT_URL);
    drupal_goto( $url_array[0] , $url_array[1] );
        
	}//end of function
}
