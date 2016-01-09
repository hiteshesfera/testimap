<?php 

ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once("includes/config.php");
require_once("Classes/imap_mailbox.php");



  $mysql = "SELECT * FROM `jobs_email_detail`";
  $count_Q=$dbConnection->query($mysql);
  $total_rows = $count_Q->rowCount();


  while ($row=$count_Q->fetch(PDO::FETCH_ASSOC)) {
    $myarray[] = $row;
  }
    echo "<pre>";
    print_r($myarray);
    echo "</pre>";
    //$datevar = "11/3/2014 12:00:00 PM";
//  echo "this is current date {$datevar}";
//  echo "<br>";
//  $payment_date=date('Y-m-d h:i:s',strtotime($datevar));
// echo "this this date after applying strtotimes {$payment_date}";





     echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
    

 echo "<pre>";
// print_r($field_data);
     
      foreach ($myarray as $key => $allcompanyval) {

     $company_id = $allcompanyval["company_id"];
     $varfs = "";
     $obj = new Mailbox($allcompanyval["hspemail"] , getPassword($allcompanyval["hsppassword"]));
     $jobarray = $obj->initClass();

     if ($jobarray) {
        $var = $obj->checkimapp();
        $result =  $obj->insertJobsFromMail($jobarray ,  $company_id);

     if ($result) {
       echo "Yes Inserted <br>";

     } else {
      echo "Not Inserted";
     }

       
     }
    


    
     //echo $varfs;
     $obj->disconnect();
       }

  

   

   





  // $hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
  // $username = "hitesh_ranaut@esferasoft.com"; # e.g somebody@gmail.com
  // $password = "H(esfera_234)#%";


/* connect to gmail with your credentials */

// $hostname = "{imap.gmail.com:993/imap/ssl}INBOX";
// $username = "hitesh_ranaut@esferasoft.com"; # e.g somebody@gmail.com
// $password = "H(esfera_234)#%";

/**
*@cfastapp.com Test Email Id
*
*/ 
// $hostname = "{pop.secureserver.net:110/pop3}";
// $username = "testjob@cfastapp.com"; # e.g somebody@gmail.com
// $password = "Location1";

/**
*
*@cfastdata.com 
*
*/
// $hostname = "{pop.secureserver.net:110/pop3}";
// $username = "hspdaily@cfastdata.com"; # e.g somebody@gmail.com
// $password = "Location1";



/* try to connect */
// $obj = new Mailbox($hostname , $username , $password);
// $emails = $obj->searchMailbox('UNSEEN');
// if(!$emails) {
//     die('Mailbox is empty');
// }

// $max_emails = 100;
//    $count = 1;
//    rsort($emails);
//     foreach($emails as $email_number) 
//     {
//         $overview = $obj->getEmailDetail($email_number);
//         echo $overview;
//         $live_file_array =  $obj->getAttachmentList($email_number);
//         $mark_seen = $obj->markMailAsRead($email_number);
//         echo "<pre>";
//         foreach($live_file_array as $key=>$val){
//                     $abc2 = explode("\t", $val);
//                     print_r($abc2);
//                     $r++;
//                 }
//                    if (!$r) { continue; }
//                 echo "{$overview} THis is Value Before Inserting Into Database ".$r; 
//                     if($count++ >= $max_emails) break;
//                 }
