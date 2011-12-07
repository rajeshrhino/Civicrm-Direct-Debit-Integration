<?php

/* 
 * Membership  - Auto renewal
 * Author: rajesh@millertech.co.uk
 * Date: 04 Nov 2010
 */
 
require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';

$debug = false;

// Include the Direct Debit Settings file
require_once '../../civicrm_direct_debit/direct_debit_settings.php';

require_once 'CRM/Contribute/PseudoConstant.php';

function MembershipAutoRenewal( ) {
  global $debug;
    
  $config =& CRM_Core_Config::singleton();
        
  require_once 'CRM/Utils/System.php';
  require_once 'CRM/Utils/Hook.php';
  
  require_once 'api/v2/Contribution.php';
  require_once 'api/v2/MembershipContributionLink.php';
  
  // Select all membership types which are 'auto-renew' required (table field `auto_renew` = 2) 
  $membershipTypeArray = array();
  $membershipTypeFeeArray =  array();
  $query = "SELECT * FROM civicrm_membership_type WHERE auto_renew = '2'";
  $dao = CRM_Core_DAO::executeQuery( $query );
  while($dao->fetch()) {
       $membershipTypeArray[] = $dao->id;
       $membershipTypeFeeArray[$dao->id] = $dao->minimum_fee;
  }
  
  $membershipTypes = @implode(',' , $membershipTypeArray);
  //echo $membershipTypes;
  
  //$current_date = date("Y-m-d");
  //$temp_date = strtotime($current_date);
  
  $count = 0;
  
  $contactArray = array();
  
  if ($membershipTypes) {
      // Select all memberships which are auto-renew membership types
      // And status is grace (table field `status_id` = 3)
      //$dtFirstDay = date("YmdHis", mktime(0, 0, 0, date("m") , 1, date("Y")));
      //$dtLastDay = date('YmdHis',strtotime('-1 second',strtotime('+1 month',strtotime(date('m').'/01/'.date('Y').' 00:00:00'))));
      
      $dtFirstDay = '20111001000000';
      $dtLastDay = '20111031235959';
        
      $member_sql = "SELECT cm.id as membership_id , cm.contact_id as contact_id , cm.membership_type_id as membership_type_id ,cm.end_date as end_date ,
                      cmt.duration_interval as duration_interval , cmt.duration_unit as duration_unit     
                      FROM civicrm_membership cm 
                      JOIN civicrm_membership_type cmt ON cm.membership_type_id = cmt.id
                      LEFT JOIN civicrm_value_payment_method cpm ON cm.id = cpm.entity_id
                      WHERE cm.membership_type_id IN ($membershipTypes) AND cm.end_date >= '{$dtFirstDay}' AND cm.end_date <= '{$dtLastDay}'
                      AND cm.status_id IN (2,3,8) AND cpm.direct_debit = 1";
                      //AND cm.status_id = 3
      //echo $member_sql;exit;                                            
      $member_dao = CRM_Core_DAO::executeQuery( $member_sql );
      
      $count = 0;
      
      while($member_dao->fetch()) {
            //print_r ($member_dao);exit;
            $membership_id = $member_dao->membership_id;
            $contact_id = $member_dao->contact_id;
            
            $mem_end_date = $member_dao->end_date;
            $temp_date = strtotime($mem_end_date);
            
            //print_r ($member_dao);echo "<hr />";
            //$temp_sql = "SELECT cmp.contribution_id as contribution_id FROM civicrm_membership_payment cmp WHERE cmp.membership_id = '$membership_id' ORDER BY cmp.contribution_id DESC";
            //$temp_dao = CRM_Core_DAO::executeQuery( $temp_sql );
            //$temp_dao->fetch();
            //$source_contribution_id = $temp_dao->contribution_id; 
      
            //require_once 'CRM/Contribute/DAO/Contribution.php';
            //$cdao = new CRM_Contribute_DAO_Contribution();
            //$cdao->id = $source_contribution_id;
            //$cdao->find(true);
            
            //receive_date
            $params =  array();
            
            $total_amount = $membershipTypeFeeArray[$member_dao->membership_type_id];
            
            $params = array(
                'contact_id'             => $member_dao->contact_id,
                'receive_date'           => date('YmdHis'),
                'total_amount'           => $total_amount,
                'fee_amount'             => $total_amount,
                'source'                 => 'Auto-Renewal for Membership Id :'.$membership_id,
                'contribution_status_id' => 2,
                'note'                   => 'Auto-Renewal for Membership Id :'.$membership_id ,
                'contribution_type_id'   => 2
            );
            $contribution =& civicrm_contribution_add($params);
            //print_r( $contribution );
            $contribution_id = $contribution['id'];
            
            $duration_interval = $member_dao->duration_interval;
            $duration_unit = $member_dao->duration_unit;
            
            $next_collectionDate = strtotime ( "+$duration_interval $duration_unit" , $temp_date ) ;
            $next_collectionDate = date ( 'YmdHis' , $next_collectionDate );
            
            $update_sql = "UPDATE civicrm_membership SET end_date = '$next_collectionDate' , status_id = '2' WHERE id = '$membership_id'";
            CRM_Core_DAO::executeQuery( $update_sql );
            
            //Create membership Payment
            $params = array (
                 'contribution_id' => $contribution_id ,
                 'membership_id'   => $membership_id
                 );
            $membershipPayment = civicrm_membershipcontributionlink_create( $params );
            
            //$contribution_sql = "SELECT * FROM civicrm_value_direct_debit_details WHERE entity_id = '$source_contribution_id'";
            //$contribution_dao = CRM_Core_DAO::executeQuery( $contribution_sql );
            //$contribution_dao->fetch();
            //$mandate_id = $contribution_dao->mandate_id;
            //$contributionTypeName = CRM_Contribute_PseudoConstant::contributionType($cdao->contribution_type_id);
            
            //$mandate_sql = "SELECT * FROM civicrm_value_bank_details WHERE entity_id = '$contact_id' AND is_default = '1'";
            $mandate_sql = "SELECT * FROM civicrm_value_bank_details WHERE entity_id = '$contact_id'";
            $mandate_dao = CRM_Core_DAO::executeQuery( $mandate_sql );
            $mandate_dao->fetch();
            $mandate_id = $mandate_dao->id;
            
            //$mandate_sql = "SELECT * FROM civicrm_value_bank_details WHERE id = '$mandate_id'"; 
            //$mandate_dao = CRM_Core_DAO::executeQuery( $mandate_sql );
            //$mandate_dao->fetch();
            
            if ($mandate_dao->collection_day) 
                $collection_day = $mandate_dao->collection_day;
            else
                $collection_day = 20;
                         
            //$next_collectionDate = get_valid_next_collection_date($collection_day , date("m") , 'YmdHis' );
            $next_collectionDate = '20111020000000'; 
            
            require_once 'api/v2/Activity.php';
            $params = array(
             'activity_type_id' => CIVICRM_DIRECT_DEBIT_STANDARD_PAYMENT_ACTIVITY_ID ,
             'source_contact_id' => $contact_id,
             'target_contact_id' => $contact_id,
             'subject' => "Member Dues, Mandate Id - ".$mandate_id,
             'status_id' => 1,
             'activity_date_time' => $next_collectionDate 
            );
            $act = civicrm_activity_create($params);
            
            $activity_id = $act['id'];
            if ($mandate_id != null) {      
                $sql = "INSERT INTO civicrm_value_activity_bank_relationship SET entity_id = '$activity_id', bank_id = '$mandate_id'";
                $dao = CRM_Core_DAO::executeQuery( $sql );
                
                $sql = "INSERT INTO civicrm_value_direct_debit_details SET activity_id = '$activity_id' , entity_id = '$contribution_id' , mandate_id = '$mandate_id'";
                $dao = CRM_Core_DAO::executeQuery( $sql );
            }

            //CRM_Core_DAO::executeQuery( "UPDATE civicrm_value_bank_details SET bacs_code = '17' WHERE id = '$mandate_id'" );
            
            //$log_note = "Auto Renewal: Contact Id - $contact_id, Membership Id - $membership_id , Created Contribtion Id - $contribution_id"; 
            //$log_date = date("jS F Y - h:i:s A");
            //$log_query = "INSERT INTO civicrm_auto_renewal_log SET log_date = '$log_date' , log_summary = '$log_note'";
            //CRM_Core_DAO::executeQuery( $log_query );
            
            $url = CRM_Utils_System::url( 'civicrm/contact/view', "reset=1&cid={$contact_id}"  );
            $contactArray[] = " Contact ID: " . $contact_id . "  - <a href =\"$url\"> ". $contact_id . " </a> ";
            $count++;
      }
      //exit;    
  }
  echo "Membership renewed: ".$count."<br />";
  if (count($contactArray) > 0)
    echo $status = implode( '<br/>', $contactArray );
}


function get_valid_next_collection_date ($day , $month , $format) {
    
    $current_date = date("Y-m-d");
    $temp_date = strtotime($current_date);

    if(empty($month))
        $month = date("m");     
    
    $collection_date = date("Y-m-d" , mktime( 0 , 0 , 0 , date($month) , date($day) , date("Y")));
    $temp_collection_date = strtotime($collection_date);
        
    if ($temp_collection_date < $temp_date) {
        $month++;
        return get_valid_next_collection_date ($day , $month , $format );
    }
    
    $collection_date = date ( $format , $temp_collection_date );
    return $collection_date;
         
    /*$diff = abs($temp_collection_date - $temp_date);
    $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
    if ($days < 14) {
        $month++;
        return get_valid_next_collection_date ($day , $month , $format);
    } else {
        $collection_date = date ( $format , $temp_collection_date );
        return $collection_date;  
    }*/
}

// Run
MembershipAutoRenewal();

?>
<FORM><INPUT TYPE="BUTTON" VALUE="Back" ONCLICK="history.go(-1)"></FORM>