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
 
require_once 'CRM/Report/Form.php';
require_once 'DirectDebit/Utils/Contribution.php';
require_once 'CRM/Contribute/PseudoConstant.php';

class DirectDebit_Report_Form_Contribute_DirectDebit extends CRM_Report_Form {
    protected $_addressField = false;
    protected $_customGroupExtends = array( 'Contribution' );

    function __construct( ) {
        $this->_columns = 
            array( 'civicrm_entity_batch'      =>
                   array( 'dao'     => 'CRM_Core_DAO_EntityBatch',
                          'filters' =>             
                          array(
                                'batch_id' => 
                                array( 'title' => 'Batch',
                                       'default'      => DirectDebit_Utils_Contribution::getLatestBatchId( 'id desc' ), 
                                       'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                       'options'      => DirectDebit_Utils_Contribution::getBatchIdTitle( 'id desc' ), 
                                       ), ), ),
                                       
                   'civicrm_contribution' =>
                   array( 'dao'     => 'CRM_Contribute_DAO_Contribution',
                          'fields'  =>
                          array(   
                                 'contribution_id' => 
                                 array( 
                                       'name'       => 'id',
                                       'no_display' => true,
                                       'required'   => true,
                                        ),
                                 'contact_name' => 
                                 array( 
                                       'name'       => 'contact_id',
                                       'title'       => 'Contributor Name',
                                       'display' => true,
                                       'required'   => true,
                                        ),       
                                 'contact_id' => 
                                 array( 
                                       'name'       => 'contact_id',
                                       'title'       => 'Donor ID',
                                       'display' => true,
                                       'required'   => true,
                                        ),        
                                 'payment_instrument_id' => 
                                 array( 
                                       'name'       => 'payment_instrument_id',
                                       'title'       => 'Payment Method',
                                       'display' => true,
                                       'required'   => true,
                                        ),
                                        
                                'receive_date' => 
                                 array( 
                                       'name'       => 'receive_date',
                                       'title'       => 'Transaction Date',
                                       'display' => true,
                                       'required'   => true,
                                        ),
                                'contribution_type_id' => 
                                 array( 
                                       'name'       => 'contribution_type_id',
                                       'title'       => 'Contribution Type',
                                       'display' => true,
                                       'required'   => true,
                                        ),        
                                'trxn_id' => 
                                 array( 
                                       'name'       => 'trxn_id',
                                       'title'       => 'Transaction ID',
                                       'display' => true,
                                       'required'   => true,
                                        ),                
                                'total_amount' => 
                                 array( 
                                       'name'       => 'total_amount',
                                       'title'       => 'Amount',
                                       'display' => true,
                                       'required'   => true,
                                        ),        
                              ),
                        ),
                   );

        parent::__construct( );
        //Set defaults
        /*if ( is_array( $this->_columns['civicrm_value_direct_debit_details'] ) ) {
            foreach ( $this->_columns['civicrm_value_direct_debit_details']['fields'] as $field => $values ) {
                    $this->_columns['civicrm_value_direct_debit_details']['fields'][$field]['default'] = true;
                    $this->_columns['civicrm_value_direct_debit_details']['fields'][$field]['required'] = true;
            }       
        }*/
    }

    function select( ) {
        $select = array( );

        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('fields', $table) ) {
                foreach ( $table['fields'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( 'required', $field ) ||
                         CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                        if ( $tableName == 'civicrm_address' ) {
                            $this->_addressField = true;
                        } else if ( $tableName == 'civicrm_email' ) {
                            $this->_emailField = true;
                        }
                        
                        // only include statistics columns if set
                        if ( CRM_Utils_Array::value('statistics', $field) ) {
                            foreach ( $field['statistics'] as $stat => $label ) {
                                switch (strtolower($stat)) {
                                case 'sum':
                                    $select[] = "SUM({$field['dbAlias']}) as {$tableName}_{$fieldName}_{$stat}";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type']  = 
                                        $field['type'];
                                    $this->_statFields[] = "{$tableName}_{$fieldName}_{$stat}";
                                    break;
                                case 'count':
                                    $select[] = "COUNT({$field['dbAlias']}) as {$tableName}_{$fieldName}_{$stat}";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_statFields[] = "{$tableName}_{$fieldName}_{$stat}";
                                    break;
                                case 'avg':
                                    $select[] = "ROUND(AVG({$field['dbAlias']}),2) as {$tableName}_{$fieldName}_{$stat}";
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['type']  =  
                                        $field['type'];
                                    $this->_columnHeaders["{$tableName}_{$fieldName}_{$stat}"]['title'] = $label;
                                    $this->_statFields[] = "{$tableName}_{$fieldName}_{$stat}";
                                    break;
                                }
                            }   
                            
                        } else {
                            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = CRM_Utils_Array::value( 'type', $field );
                        }
                    }
                }
            }
        }

        $this->_select = "SELECT " . implode( ', ', $select ) . " ";
    }

    function from( ) {
        $this->_from = "
FROM civicrm_entity_batch {$this->_aliases['civicrm_entity_batch']} 
INNER JOIN civicrm_contribution {$this->_aliases['civicrm_contribution']} 
        ON {$this->_aliases['civicrm_entity_batch']}.entity_table = 'civicrm_contribution' AND 
           {$this->_aliases['civicrm_entity_batch']}.entity_id = {$this->_aliases['civicrm_contribution']}.id";
    }

    function where( ) {
        parent::where( );

        if ( empty($this->_where) ) {
            //$this->_where = "WHERE (value_direct_debit_details_civireport.amount IS NOT NULL)";
        } else {
            //$this->_where .= " AND (value_direct_debit_details_civireport.amount IS NOT NULL)";
        }
    }

	function statistics( &$rows ) {
        $statistics = parent::statistics( $rows );

        $select = "
                SELECT *
               ";
                      
        $sql = "{$select} {$this->_from} {$this->_where}";
        $dao = CRM_Core_DAO::executeQuery( $sql );

        /*if ( $dao->fetch( ) ) {
            $statistics['counts']['amount']    = array( 'value' => $dao->amount,
                                                        'title' => 'Total Amount',
                                                        'type'  => CRM_Utils_Type::T_MONEY );
        }*/
        
        // Added by rajesh@millertech.co.uk on 1st Dec 2010
        $batch_array = CRM_Utils_Array::value('batch_id_value', $_POST, '');
        //print_r ($batch_array);exit;  
        $block_produce = FALSE;
        if (! empty ($batch_array) ) {
            $statistics['batch_array'] = array_reverse ( $batch_array );
            $all_batch_ids = implode(',' , $batch_array);
            $sql = "SELECT * FROM civicrm_entity_batch eb WHERE eb.batch_id IN ($all_batch_ids) AND eb.entity_id IN (SELECT gf.entity_id FROM civicrm_entity_file gf)";
            $dao = CRM_Core_DAO::executeQuery( $sql );  
            $dao->fetch( );
            if (! empty($dao->entity_id) ) {
                $block_produce = TRUE;     
            }
        }
        else {
            $batch_sql = "SELECT MAX(id) as default_batch_id FROM civicrm_batch";
            $batch_dao = CRM_Core_DAO::executeQuery( $batch_sql );  
            $batch_dao->fetch( );
            $default_batch_id = $batch_dao->default_batch_id;  
            
            if (!empty($default_batch_id) ) {
                $batch_array = array ( 0 => $default_batch_id ); 
                $statistics['batch_array'] = $batch_array;
                
                $all_batch_ids = implode(',' , $batch_array);
                $sql = "SELECT * FROM civicrm_entity_batch eb WHERE eb.batch_id IN ($all_batch_ids) AND eb.entity_id IN (SELECT gf.entity_id FROM civicrm_entity_file gf)";
                $dao = CRM_Core_DAO::executeQuery( $sql );  
                $dao->fetch( );
            }
            if (! empty($dao->entity_id) ) {
                $block_produce = TRUE;     
            }
        }
        
        if ($block_produce) {
            $statistics['block_produce'] = 1;     
        }
        else {
            $statistics['block_produce'] = 0;    
        }
        
        if (!empty($batch_array)) {
            $batch_id = $batch_array[0];
            
            $batch_sql = "SELECT * FROM civicrm_batch b WHERE b.id = %1";
            $batch_params  = array( 1 => array( $batch_id   , 'Integer' ));         
            $batch_dao = CRM_Core_DAO::executeQuery( $batch_sql, $batch_params );
			
            $batch_dao->fetch( );
            
            $batch_file_name = "Batch_".$batch_id."_".date('ymd', strtotime ($batch_dao->created_date));
            
            $csv_path = "sites/default/files/civicrm/custom";
            $filePathName   = "{$csv_path}/{$batch_file_name}.csv";
            
            $statistics['csvFileName'] = $filePathName;
        }
        
        return $statistics;
    }
	
	  function alterDisplay( &$rows ) {
	       $paymentInstruments = CRM_Contribute_PseudoConstant::paymentInstrument();
	       
         require_once 'api/v2/Contact.php';
         require_once 'CRM/Contribute/DAO/ContributionType.php';
	       foreach ( $rows as $rowNum => $row ) {
	           if ( array_key_exists('civicrm_contribution_contact_id', $row) ) {
	               $contact_id = $row['civicrm_contribution_contact_id'];
                 $params = array('contact_id' => $contact_id , 'return.external_identifier' => 1 );
                 $contactArray = civicrm_contact_get($params);
                 if(!empty($contactArray[$contact_id]['external_identifier'])) 
                    $rows[$rowNum]['civicrm_contribution_contact_id'] = $contactArray[$contact_id]['external_identifier'];          
             }
             if ( array_key_exists('civicrm_contribution_payment_instrument_id', $row) ) {
	               $payment_intrument_id = $row['civicrm_contribution_payment_instrument_id'];
                 $payment_intrument_name = $paymentInstruments[$payment_intrument_id];
                 $rows[$rowNum]['civicrm_contribution_payment_instrument_id'] = $payment_intrument_name;          
             }
             if ( array_key_exists('civicrm_contribution_contribution_type_id', $row) ) {
	               $contribution_type_id = $row['civicrm_contribution_contribution_type_id'];
	               $dao =& new CRM_Contribute_DAO_ContributionType();
	               $dao->id = $contribution_type_id;
                 $dao->find(true);
                 $contribution_type_name = $dao->name; 
                 $rows[$rowNum]['civicrm_contribution_contribution_type_id'] = $contribution_type_name;          
             }
             if ( array_key_exists('civicrm_contribution_contact_name', $row) ) {
	                $contact_id = $row['civicrm_contribution_contact_name'];
	                
	                $params = array('contact_id' => $contact_id , 'return.display_name' => 1 );
                  $contactArray = civicrm_contact_get($params);
                  if(!empty($contactArray[$contact_id]['display_name'])) { 
                    $rows[$rowNum]['civicrm_contribution_contact_name'] = $contactArray[$contact_id]['display_name'];
                    $url = 'civicrm/contact/view';
                    $urlParams = "reset=1&cid={$contact_id}";
                    $contact_url = CRM_Utils_System::url( $url, $urlParams );
                    $rows[$rowNum]['civicrm_contribution_contact_name_link'] = $contact_url;
                  }  
                    
             }
	       }
	       //print_r ($rows);exit;
	  }     
	
    function postProcess( ) {

        parent::postProcess( );

    }
}
