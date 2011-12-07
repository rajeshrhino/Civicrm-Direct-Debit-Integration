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

require_once 'CRM/Core/Form.php';

require_once 'CRM/Core/Session.php';

require_once 'CRM/Contribute/DAO/ContributionRecur.php';

/**
 * This class provides the functionality to list the recurring contributions
 * for a contact under 'Contribution' tab 
 */

class DirectDebit_Form_RecurringContribution extends CRM_Core_Form {

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
	function preProcess()
  {	
        parent::preProcess( );
		    
	}
	
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
		
		$attributes = CRM_Core_DAO::getAttribute( 'CRM_Contribute_DAO_ContributionRecur' );
		$action = CRM_Utils_Array::value('action', $_REQUEST, '');
    $cid = CRM_Utils_Request::retrieve( 'cid', 'Integer', $this );
    $id = CRM_Utils_Request::retrieve( 'id', 'Integer', $this );
    
    require_once 'api/v2/Contact.php';
    $params = array('contact_id' => $cid);
    $contact_details = & civicrm_contact_get($params);
    
    CRM_Utils_System::setTitle( 'Setup Recurring Payment - '.$contact_details[$cid]['display_name'] );
    
    //$mandatIdArray = array(''=>'--select--');
    $sql = "SELECT * FROM civicrm_value_bank_details WHERE entity_id = '$cid'";
    //AND authorization_date IS NOT NULL AND authorization_file IS NOT NULL 
    $dao = CRM_Core_DAO::executeQuery( $sql );
    while($dao->fetch()) {
        $mandatIdArray[$dao->id] = $dao->id;  
    }
    //$form->add( 'text', 'mandate_id', ts('Mandate ID'), array( 'maxlength' => 10 ) , false );
    
    //echo $action;exit;
    if ($action == 'update') {
		    $sql = "SELECT * FROM civicrm_contribution_recur WHERE id = '$id'";
		    $dao = CRM_Core_DAO::executeQuery($sql);
        if($dao->fetch()) {
            $defaults = array(
                              'amount'=>$dao->amount ,
                              'frequency_interval'=>$dao->frequency_interval ,
                              'frequency_unit'=>$dao->frequency_unit ,
                              'start_date'=>$dao->start_date ,
                              'processor_id'=>$dao->processor_id ,
                              'next_sched_contribution'=>$dao->next_sched_contribution 
                              //'standard_price'=>$dao->standard_price ,
                              //'vat_rate'=>$dao->vat_rate 
                              );
                              
           if ( CRM_Utils_Array::value( 'start_date' , $defaults )  && !empty($dao->start_date) && $dao->start_date != '0000-00-00') {
                list( $defaults['start_date'], 
                      $defaults['start_date_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['start_date'], 'activityDate' );    
           } else {
              $defaults['start_date'] = "";     
           }                       
           if ( CRM_Utils_Array::value( 'next_sched_contribution' , $defaults ) && !empty($dao->next_sched_contribution) && $dao->next_sched_contribution != '0000-00-00') {
                list( $defaults['next_sched_contribution'], 
                      $defaults['next_sched_contribution_time'] ) = CRM_Utils_Date::setDateDefaults( $defaults['next_sched_contribution'], 'activityDate' );    
           } else {
              $defaults['next_sched_contribution'] = "";     
           }
        }
        $this->addElement('hidden', 'id', $id );
    }
    
    if(count($mandatIdArray) == 0) {
        require_once 'CRM/Utils/Type.php';
        $customGroupName = CRM_Utils_Type::escape( 'Direct_Debit_Mandate', 'String');
        $DDMandategroupId = CRM_Core_DAO::getFieldValue( "CRM_Core_DAO_CustomGroup", $customGroupName, 'id', 'name' );
        $this->assign('no_mandate_for_contact' , 1 );
        $this->assign('groupId' , $DDMandategroupId );
    }    
    $this->add( 'select', 'processor_id', ts('Mandate ID'), $mandatIdArray , true );
     
		$this->add( 'text', 'amount', 
                    ts('Amount'),
                    $attributes['label'], true );
    
		$this->add( 'text', 'frequency_interval', ts( 'Every' ),  
                        array( 'maxlength' => 2 , 'size' => 2 )  , true );
    //$form->addRule( 'frequency_interval', 
    //                        ts( 'Frequency must be a whole number (EXAMPLE: Every 3 months).' ), 'integer' );
                    
    $frUnits = implode( CRM_Core_DAO::VALUE_SEPARATOR,
                                CRM_Core_OptionGroup::values(  'recur_frequency_units' ) );                    
    $units    = array( );
    $unitVals = explode( CRM_Core_DAO::VALUE_SEPARATOR, $frUnits );
    $frequencyUnits = CRM_Core_OptionGroup::values( 'recur_frequency_units' );
    foreach ( $unitVals as $key => $val ) {
        if ( array_key_exists( $val, $frequencyUnits ) ) {
            $units[$val] = $frequencyUnits[$val];
            if ( CRM_Utils_Array::value( 'is_recur_interval', $form->_values ) ||
                 $className == 'CRM_Contribute_Form_Contribution' ) {
                 $units[$val] = "{$frequencyUnits[$val]}(s)";
            }
        }
    }

    $frequencyUnit =& $this->add( 'select', 'frequency_unit', null, $units , true );
    
    // FIXME: Ideally we should freeze select box if there is only
    // one option but looks there is some problem /w QF freeze.
    //if ( count( $units ) == 1 ) {
    //$frequencyUnit->freeze( );
    //}
    
    //$this->add( 'text', 'installments', ts( 'installments' ), $attributes['installments'] );
                                        
    $this->addDate( 'start_date', ts('Start Date'), true, array( 'formatType' => 'activityDate' ) );
    $this->addDate( 'next_sched_contribution', ts('Next Scheduled Date'), true, array( 'formatType' => 'activityDate' ) );
    $this->addDate( 'end_date', ts('End Date'), false, array( 'formatType' => 'activityDate' ) );
                    
    $this->setDefaults( $defaults );
    
    $this->addElement('hidden', 'action', $action );
    $this->addElement('hidden', 'cid', $cid );
    
    $this->assign( 'cid', $cid );
    
    //$this->addFormRule( array( 'CRM_Package_Form_Package', 'formRule' ) );
                           
		$this->addButtons(array( 
                                    array ( 'type'      => 'next', 
                                            'name'      => ts('Save'), 
                                            'spacing'   => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', 
                                            'isDefault' => true   ), 
                                    ) 
                              );
    }
    	
    /**
     * global validation rules for the form
     *
     * @param array $fields posted values of the form
     *
     * @return array list of errors to be posted back to the form
     * @static
     * @access public
     */
    static function formRule( $values ) 
    {
        $errors = array( );

        if (!empty($values['start_date']) && !empty($values['end_date']) ) {
            $start = CRM_Utils_Date::processDate( $values['start_date'] );
            $end   = CRM_Utils_Date::processDate( $values['end_date'] );
            if ( ($end < $start) && ($end != 0) ) {
                $errors['end_date'] = ts( 'End date should be after Start date' );
            }
        }  
        return $errors;
    }	
   
    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
    public function postProcess() {
        $config =& CRM_Core_Config::singleton( );
    		$params = $this->controller->exportValues( );
    		
    		if(!empty($params['start_date']))
    		    $start_date       = CRM_Utils_Date::processDate($params['start_date']);
    		if(!empty($params['next_sched_contribution']))    
    		    $next_sched_contribution         = CRM_Utils_Date::processDate($params['next_sched_contribution']);
    		
        if ($params['action'] == 'add') {
            //$recur->create_date   = date( 'YmdHis' );
            $sql = "INSERT INTO civicrm_contribution_recur SET contact_id = %1 , amount = %2 , frequency_interval = %3 , frequency_unit = %4 , invoice_id = %5 ,
                    trxn_id = %6 ,  currency = %7 , create_date = %8 , start_date = %9 , next_sched_contribution = %10 , processor_id = %11";
            $invoice_id  = md5(uniqid(rand(), true));
            $recur_params = array(
                          1 =>  array( $params['cid']                , 'Integer' ) ,  
                          2 =>  array( $params['amount']             , 'String' ) ,
                          3 =>  array( $params['frequency_interval'] , 'String'  ) ,
                          4 =>  array( $params['frequency_unit']     , 'String' ) ,
                          5 =>  array( $invoice_id                   , 'String'  ) ,
                          6 =>  array( $invoice_id                   , 'String'  ) ,
                          7 =>  array( $config->defaultCurrency      , 'String'  ) ,
                          8 =>  array( date('YmdHis')                , 'String'  ) ,
                          9 =>  array( $start_date                   , 'String'  ) ,
                          10 => array( $next_sched_contribution      , 'String'  ) ,
                          11 => array( $params['processor_id']       , 'String'  )
                            
                      );
            $status = ts('Recurring Contribution setup successfully');        
        }
        elseif ($params['action'] == 'update') {
            $sql = "UPDATE civicrm_contribution_recur SET amount = %1 , frequency_interval = %2 , frequency_unit = %3 ,
                    start_date = %4 , next_sched_contribution = %5 , modified_date = %6  , processor_id = %8 WHERE id = %7";
            $recur_params = array(
                          1 =>  array( $params['amount']             , 'String' ) ,
                          2 =>  array( $params['frequency_interval'] , 'String'  ) ,
                          3 =>  array( $params['frequency_unit']     , 'String' ) ,
                          4 =>  array( $start_date                   , 'String'  ) ,
                          5 =>  array( $next_sched_contribution      , 'String'  ) ,
                          6 =>  array( date('YmdHis')                , 'String'  ) ,
                          7 =>  array( $params['id']                 , 'Integer') ,
                          8 =>  array( $params['processor_id']       , 'String'  )
                      );
                         
            $status = ts('Recurring Contribution updated');        
        }
        
        CRM_Core_DAO::executeQuery($sql , $recur_params);
        
        $session = CRM_Core_Session::singleton( );
        CRM_Core_Session::setStatus( $status );
        
        CRM_Utils_System::redirect( '?q=civicrm/contact/view&reset=1&cid='.$params['cid'] );
	  }//end of function
}
