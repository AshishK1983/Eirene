<?PHP
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;	
	require_once 'resource/phplib/PHPMailer/Exception.php';
	require_once 'resource/phplib/PHPMailer/PHPMailer.php';
	require_once 'resource/phplib/PHPMailer/SMTP.php';
	
class EireneMail{	
	public $error="";
	
	function sendEmail($to,$cc,$subject,$content,$sendername="",$replyto=""){
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Mailer = "smtp";
		$mail->SMTPDebug  = 0;  
		$mail->SMTPAuth   = TRUE;
		$mail->SMTPSecure = "tls";
		$mail->Port       = 587;
		$mail->Host       = "smtp.gmail.com";
		$mail->Username   = "nbbi.office@nieamission.org";
		$mail->Password   = "Nbbi@office";
		$mail->IsHTML(true);
		
		$to1=$to;$cc1=$cc;
		$to=explode(",",$to);
		foreach($to as $t){
			$tt=explode(":",$t);
			if(count($tt)>1)
				$mail->AddAddress($tt[0], $tt[1]);
			else
				$mail->AddAddress($tt[0], $tt[0]);
		}
		if(!empty($cc)){
			$cc=explode(",",$cc);
			foreach($cc as $t){
				$tt=explode(":",$t);
				if(count($tt)>1)
					$mail->AddCC($tt[0], $tt[1]);
				else
					$mail->AddCC($tt[0], $tt[0]);
			}
		}
		if(empty($sendername)) $sendername="NIEA Online Course";
		$mail->SetFrom("nbbi.office@nieamission.org", $sendername);
		if(!empty($replyto))
			$mail->AddReplyTo($replyto,$sendername);
		else
			$mail->AddReplyTo("nbbi.office@nieamission.org", $sendername);		
		$mail->Subject = $subject;
		$mail->MsgHTML($content);
		
		if(!$mail->Send()) {
		  //$this->error="Error while sending Email.";
		  //return false;
		  $this->sendMailFromPhp($to1,$cc1,$subject,$content);
		} else {
		  return true;
		}
		
	}	
	
	function sendMailFromPhp($to1,$cc,$subject,$content){
		$to="";
		$to1=explode(",",$to1);
		foreach($to1 as $t){
			$tt=explode(":",$t);
			if(empty($to))
				$to=$tt[0];
			else
				$to.=";".$tt[0];
			//else
				//$mail->AddAddress($tt[0], $tt[0]);
		}
         
         $content;
         
         $header = "From:onlinecourse@niea.in \r\n";
         //$header .= "Cc:afgh@somedomain.com \r\n";
         $header .= "MIME-Version: 1.0\r\n";
         $header .= "Content-type: text/html\r\n";
         
         $retval = mail($to,$subject,$content,$header);
         
         if( $retval == true ) {
            return true;
         }else {
           $this->error="Error while sending Email.";
		   return false;
         }
	}
}
?>