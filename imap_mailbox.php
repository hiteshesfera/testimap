<?php

/**
*
* @Class => Mailbox
*
* THis Class Will Create Connection with Gmail Server.  
* Will Return Mail box mails. Mails Return will depend On the argument passed in searchMailbox() Function.
* 
* 
*
**/

Class Mailbox {

 // This is Path to mail Server
	protected $imapPath = "{imap.gmail.com:993/imap/ssl}INBOX";
 // email id for mail account
	protected $login;
 // Password Used For mail account
	protected $password;
  //Imap stream this contain Imap stream to fetch all data
  protected $imapStream;

  protected $field_data = array( 'company_id' , 'Last_Updated' , 'job_status', 'job_status_reason' , 'Service_Region' , 'Tech_Team' , 'Activity' , 'Account', 'Owner' , 'Due' , 'Statusfromfile' , 'SR_Sub_Area', 'Reason', 'Type', 'notitle', 'Tech_Instructions', 'Planned_Duration', 'Order', 'Driving_Directions', 'Company', 'SR', 'VIP', 'Repeat_Service_Flag', 'Service_Within_7', 'Dwelling_Type', 'Cancelled', 'Cust_Selected_PP', 'Related_SR' , 'Create_QA', 'QA_Generated', 'OMS_Order_Id', 'Modified_Activity', 'Source_QA_Activity', 'MAS_Programming', 'Order_Class', 'Property_id', 'Alert_Urgency', '40Ft_Ladder_Flag', 'Property', 'Jeopardy_Flag', 'Jeopardy_Reason', 'Travel_Time', 'User_Login', 'Site', 'Region', 'Planned_Start', 'Planned_Completion' , 'Case_Mgmt_Flag' );
	
	

/**
*
*@_construct()
*@param String $imapPath , $login , $password
*
**/
	public function __construct($login , $password  ) {
		$this->login    = $login;
		$this->password = $password;
    // This will assign the input stream to the imapStream property
    $this->imapStream = $this->getConnection();
	}




    /**
     *
     *@function
     * 
     */

     public function initClass() {
       $emailss = $this->searchMailbox('UNSEEN');
       //$emailss = $this->searchMailbox();
      if(!$emailss) {  return false;  }
         $max_emails = 100;
         $count = 1;
         rsort($emailss);
         $mainarray = array();
         foreach($emailss as $email_number) 
          {    // this contain email received date.
              $overview = $this->getEmailDetail($email_number);
              $live_file_array = $this->getAttachmentList($email_number);
              $mark_seen = $this->markMailAsRead($email_number);
              foreach($live_file_array as $key=>$val){
                          $abc2 = explode("\t", $val);
                          array_push($mainarray , $abc2);
                          //print_r($abc2);
                    
                      }
                     
                     // echo "{$overview} THis is Value Before Inserting Into Database ".$r; 
                       if($count++ >= $max_emails) break;
         }
         return $mainarray;
     }



    

    /**
    *
    *@insertJobsFromMail();
    *
    *@param $jobarray Array
    *
    *@return bool
    *
    *
    */

   public function insertJobsFromMail($jobarray  , $company_id) {
    global $dbConnection;
     $varfs="";
    if (!empty($jobarray) && is_array($jobarray)) {
       //outer loop total array count
      $outer_loop_array_count = count($jobarray);
      $inc_outer = 1;
      foreach ($jobarray as $key => $value) {
      $inc_val = 1;
      $array_count = count($value);
      $updatedtime = date("Y-m-d H:i:s", time());
      $varfs   .= "( '{$company_id}' , '{$updatedtime}' , '0' , '0' ,";
       foreach ($value as $mykey => $myvalue) {
         $val1 = str_replace('"', '', $myvalue);
         //$val2 = str_replace(NULL, '', $val1);
 $val2 = iconv(mb_detect_encoding($val1, mb_detect_order(), true), "UTF-8", $val1);
         $varfs .= "'".  str_replace("'", "''", $val2 ) ."'";
        if ($inc_val != $array_count) {
           $varfs .= ", ";
        }
       ++$inc_val;
       }
       $varfs .= ")";
         if ($inc_outer != $outer_loop_array_count) {
           $varfs .= ",  ";
         }
        ++$inc_outer;
     }
      $sql = "INSERT INTO `HSP_jobs` ".$this->fieldsForSQL()." VALUES ".$varfs;
      $result =  $dbConnection->query($sql);
      $delDuplicate = $dbConnection->query("DELETE dup FROM `HSP_jobs` dup, `HSP_jobs` org WHERE dup.`hsp_id` > org.`hsp_id` AND dup.`Service_Region` = org.`Service_Region` AND dup.`Tech_Team` = org.`Tech_Team` AND dup.`Activity` = org.`Activity` AND  dup.`Account` = org.`Account` AND  dup.`SR_Sub_Area` = org.`SR_Sub_Area` AND  dup.`Reason` = org.`Reason` AND  dup.`Type` = org.`Type`  AND  dup.`Planned_Duration` = org.`Planned_Duration` AND  dup.`Order`= org.`Order` AND  dup.`Site`= org.`Site` AND  dup.`Region`= org.`Region`");
     
      return $result;
   }


   }



    /**
    *
    *@getConnection()
    *
    *
    *@return Connection Stream with mail server.
    *
    */
   public function getConnection() {
          $imapStream = $this->initImapStream();
                 return $imapStream;
   }


    /**
    *
    *@method  initImapStream()
    *
    *@return Connection with mail server.
    *
    * Protected Method 
    */

	protected function initImapStream() {
		$imapStream = @imap_open($this->imapPath, $this->login, $this->password);
		if(!$imapStream) {
			throw new ImapMailboxException('Connection error: ' . imap_last_error());
		}
		return $imapStream;
	}

   
   /**
    *@method checkimapp
    *
    *@return array
    *
   */
  public function checkimapp() {
   return imap_check($this->getConnection());
  }


    /**
    *
    *@method  searchMailbox()
    *
    *@param String $criteria
    *
    *@return List of mails in inbox 
    */

    public function searchMailbox( $criteria = 'UNSEEN') {
		$mailsIds = imap_search($this->imapStream , $criteria);
		return $mailsIds ? $mailsIds : array();
	}

    /**
    *
    *@method Protected searchMailbox()
    *
    * This will disconnect the main connection
    *
    */

	public function disconnect() {
		
			return imap_close($this->imapStream, CL_EXPUNGE);
	
	}






   

   /* get information specific to this email */
   /**
   *@method getEmailDetail()
   *
   *@param Int $email_number
   *
   *@return Date Format Used (Y-m-d H:i:s)
   *
   */
    public function getEmailDetail($email_number) {
       
        $overview = imap_fetch_overview($this->imapStream ,$email_number,0);
        $date = $overview[0]->date;
        return date('Y-m-d H:i:s' , strtotime($date));

    }  

   /**
   *@method getImapMailbody()
   *
   *@param Int $email_number
   *
   *@return $Mail message
   *
   */

    public function getImapMailbody($email_number) {
        /* get mail message */
          $message = imap_fetchbody($this->getConnection() ,$email_number,2);
          return $message;

    }



    public function getPOPUnseenMails() {


      $count = imap_num_msg($this->getConnection());
 for($msgno = 1; $msgno <= $count; $msgno++) {
  
     $headers = imap_headerinfo($connection, $msgno);
    if($headers->Unseen == 'U') {
       
       return $msgno;

        }
     }
 }


 public function setFlagForMail($msgno) {
  imap_setflag_full($this->getConnection() , $msgno , 'SEEN');
  return true;

 }
      



     
  /* if any attachments found... */
   /**
   *@method getAttachmentList()
   *
   *@param Int $email_number
   *
   *@return Array Of All attachment
   *
   */
	public function getAttachmentList($email_number) {

       $structure = imap_fetchstructure($this->imapStream , $email_number);
        $attachments = array();
 
       
        if(isset($structure->parts) && count($structure->parts)) 
        {
            for($i = 0; $i < count($structure->parts); $i++) 
            {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );
 
                if($structure->parts[$i]->ifdparameters) 
                {
                    foreach($structure->parts[$i]->dparameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'filename') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }
 
                if($structure->parts[$i]->ifparameters) 
                {
                    foreach($structure->parts[$i]->parameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'name') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }
 
                if($attachments[$i]['is_attachment']) 
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($this->getConnection(), $email_number, $i+1);
 
                    /* 4 = QUOTED-PRINTABLE encoding */
                    if($structure->parts[$i]->encoding == 3) 
                    {
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    /* 3 = BASE64 encoding */
                    elseif($structure->parts[$i]->encoding == 4) 
                    {
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }

        return $this->getCSVFileFromAttachment($attachments);
	}

  /**
   *
   *@getCSVFileFromAttachment()
   *
   *@param array $attachments Contains All attachments
   *
   *@return Function will return only Data of CSV File.
   *
   *
   */

  public function getCSVFileFromAttachment($attachments) {
             
              foreach($attachments as $attachment)
            { 
            if($attachment['is_attachment'] == 1)
             {   
        
                $filename = $attachment['name']; 
                if(empty($filename)) $filename = $attachment['filename'];
                if(empty($filename)) $filename = time() . ".dat";
 
                /**
                 * prefix the email number to the filename in case two emails.
                 * have the attachment with the same file name.
                 */

                $live_file =    $attachment["attachment"];
              //  $abc       =    file_get_contents($email_number . "-" . $filename);
                
               $array = explode("." , $filename);
               $extension = strtolower(array_pop($array));
                 if ($extension == "csv") {
                 $live_file_array = explode("\n", $live_file);
                 array_shift($live_file_array);
                 array_pop($live_file_array);
                 $r=0;
                return $live_file_array;
                 } else {
                    continue;
                 }
            }
        }
  }









// This Will mark the given mail as seen mail
  /**
  *
  *@method markMailAsRead()
  *
  *@return bool
  *
  *
  *
  */

  public function markMailAsRead($mailId) {
    return $this->setFlag(array($mailId), '\\Seen');
  }


  /**
   * Causes a store to add the specified flag to the flags set for the mails in the specified sequence.
   *
   * @param array $mailsIds
   * @param $flag Flags which you can set are \Seen, \Answered, \Flagged, \Deleted, and \Draft as defined by RFC2060.
   * @return bool
   */
  public function setFlag(array $mailsIds, $flag) {
    return imap_setflag_full($this->getConnection(), implode(',', $mailsIds), $flag, ST_UID);
  }


    /**
    *
    *@method getHeaderInfo()
    *
    *@param INT $msgno
    *
    *@return array with message detail
    */


  public function getHeaderInfo($msgno) {
    return imap_headerinfo($this->getConnection() , $msgno);
  }


    /**
    *
    *@method fieldsForSQL()
    *
    *@return String
    */
  public function fieldsForSQL() {
           $fields = "";
          $total_fields_count = count($this->field_data);
          $inc_field_i = 1;

          $fields = "(";
          foreach ($this->field_data as $key => $value) {
           $fields .= "`{$value}` ";
          if ($inc_field_i != $total_fields_count) {
             $fields .= ","; 
          }
          ++$inc_field_i;
          }

          $fields .= ")";
           return $fields;
        
  }



}


class ImapMailboxException extends Exception {

}