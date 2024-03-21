<?PHP
function GetOnlyNumeric($str){
	$removedText = preg_replace("/\D+/g","",$str);
	return $removedText;
}
function IsSqlStatement($str){
	if(is_string($str)==false)
		return false;
	$str=trim($str);
	$str=strToLower($str);
	if(strpos($str,"select ")>-1 && strpos($str,"from ")>-1)
		return true;
	else
		return false;
}
function FormatDate($date="") {
    $d;
	if(empty($date))
		return date("Y-m-d");
	else
		return date( "Y-m-d", strtotime($date));
}
function FillDropdownHTML($data,$value="",$commaSeperatedSqlValue=false,$addEmptyValue=false){
   $str = "";   
	if (IsSqlStatement($data)==true){		
		$data=str_replace("u00a0"," ",$data);		
		$db=$GLOBALS["db"];
		$res=$db->GetList1($data,false);
		if($commaSeperatedSqlValue==true){
			if(count($res)==1 && isset($res[0][0])){
				$data=$res[0][0];				
			}
		}else{
			//echo $data;
			//if(empty($res)) echo $data;
			$data=array();
			
			foreach($res as $r){
				if(count($r)>1){
					$r[0]=trim($r[0]);
					$r[1]=trim($r[1]);
					$data[$r[0]]=$r[1];
				}else{
					$r[0]=trim($r[0]);					
					$data[$r[0]]=$r[0];
				}
			}
		}
	}
	
	if(is_string($data)){
		$res=explode(",",$data);
		$data=array();
		foreach($res as $r){
			$r=explode(":",$r);
			if(count($r)>1){
				$r[0]=trim($r[0]);
				$r[1]=trim($r[1]);
				$data[$r[0]]=$r[1];
			}else{
				$r[0]=trim($r[0]);				
				$data[$r[0]]=$r[0];
			}
		}
	}	
		
	if(is_array($data)==false) return "";
	
	if($addEmptyValue){
		$str.="<option value=''></option>";
	}
	foreach($data as $k=>$v){
		if($k!=$value)
			$str.="<option value='$k'>$v</option>";
		else
			$str.="<option selected value='$k'>$v</option>";
	}
	
	//Supply Param Value
	if(strpos($str,"||")!==false){
		$ux=new EireneUx();
		$str=$ux->SupplyParamValue($str);
	}
	return $str;   
}

function SetUp($json,$value){
	/*create system tables*/
	$tbl=file_get_contents ("plugin/system.json");
	InstallPlugin("",$tbl,"",true);	
	$json->success=true;
}

function ArrangeFilesArrayForBulkUpload($file_post) {
	$file_ary = array();
	$file_count = count($file_post['name']);
	$file_keys = array_keys($file_post);

	for ($i=0; $i<$file_count; $i++) {
		foreach ($file_keys as $key) {
			$file_ary[$i][$key] = $file_post[$key][$i];
		}
	}
	return $file_ary;
}

function GetServerCommunication($result){
	//echo "<br>Global PHP<br>";print_r($GLOBALS["globalphp"]);
	//echo "<br>Global Result<br>";print_r($GLOBALS["result"]);	
	return json_encode($result,JSON_UNESCAPED_UNICODE);	
}
function GetServerCommunication1($result){
	//echo "<br>Global PHP<br>";print_r($GLOBALS["globalphp"]);
	//echo "<br>Global Result<br>";print_r($GLOBALS["result"]);
	$res=array();
	foreach($result as $k=>$r){		
		$res[$k]=$r;
	}	
}

function setOutput($defaultoutput,&$result,$value,$json,$append=false){
	/*initialize*/	
	if(empty($json)) $json=json_decode("{}");	
	if(!isset($json->output)) $json->output=$defaultoutput;
	if(!isset($json->outputto)) $json->outputto="html";	
	if(!isset($json->action)) $json->action="";	
	if(!isset($json->appendoutput)) $json->appendoutput=$append;
	if(is_object($json->outputto) || is_array($json->outputto)){		
		$json->outputto=GetStringFromJson($json,"outputto");
	}
	if(is_object($json->output) || is_array($json->output)){		
		$json->output=GetStringFromJson($json,"output");
	}	
	$json->output=trim($json->output);
	if($json->outputto=="none") return false;
	
	/*identify output and outputto*/
	if(strpos($json->output," ")!==false && !isset($json->output)){		
		$out=explode(" ",$json->output);		
		if(count($out)==2){
			$json->outputto=$out[0];
			$json->output=$out[1];
		}		
	}	
	
	if(strtolower($json->action)=="dom"){		
		setDomOutput($json);
	}else{		
		if(isset($json->append)){$json->append=GetStringFromJson($json,"append"," ");$value.=$json->append;}
		if(isset($json->prepend)){$json->prepend=GetStringFromJson($json,"prepend"," "); $value=$json->prepend.$value;}
		if($defaultoutput!="error")
			$target=empty($json->output)?$defaultoutput:$json->output;
		else{
			$target=$defaultoutput;
			$json->outputto=="html";
		}
		if($json->outputto=="html"){
			setHtmlAndPhpOutput($json,$value,$target,$GLOBALS["result"]);
		}else if($json->outputto=="php"){
			setHtmlAndPhpOutput($json,$value,$target,$GLOBALS["globalphp"]);					
		}		
	}
}
function setHtmlAndPhpOutput($json,$value,$target,&$globalvar){
	//if($json->outputto=="html") $globalvar=$GLOBALS["result"];
	//else if($json->outputto=="php") $globalvar=$GLOBALS["globalphp"];
	if($json->appendoutput==false){		
		$globalvar[$target]=$value;
		//echo $target. " = ";print_r($GLOBALS["result"][$target]);echo "<br>";
	}else{
		if(empty($globalvar[$target])){
			$globalvar[$target]=$value;			
		}else{
			if(is_string($globalvar[$target])){				
				$globalvar[$target].=" ".$value;						
			}else if(is_array($result[$target]))
				$globalvar[$target][]=$value;			
		}			
	}	
}
function setDomOutput($json){
	if(empty($json->fun)) return false;	
	if(!isset($GLOBALS["result"]["dom"])){
		$GLOBALS["result"]["dom"]=[];			
	}else{
		$GLOBALS["result"]["dom"]=json_decode($GLOBALS["result"]["dom"]);			
	}
	if(isset($json->action)) if(strtolower($json->action)=="dom") $json->action="";
	unset($json->action);
	unset($json->cmd);
	unset($json->output);
	unset($json->outputto);
	unset($json->appendoutput);
	if(empty($json->elem)) {$json->elem="";unset($json->elem);}	
	if(empty($json->val)) {$json->val="";unset($json->val);}
	//$json->val=str_replace(array("&comma&","&dash&","&scomma&"),array(",","-",";"),$json->val);
	
	$str=json_encode($json,JSON_UNESCAPED_UNICODE);
	
	//$result["dom"][]='{"fun":"'.$json->fun.'"'.$other.'}';
	$GLOBALS["result"]["dom"][]=$str;
	$GLOBALS["result"]["dom"]=json_encode($GLOBALS["result"]["dom"],JSON_UNESCAPED_UNICODE);
}

function GetSignature($orderid,$amount,$fullname,$phoneno,$email,$returnurl="",$notifyurl="",$ordernote=""){
	if(empty($returnurl)) $returnurl="https://onlinecourse.niea.in/index.php";
	if(empty($notifyurl)) $notifyurl="https://onlinecourse.niea.in/index.php";
	if(empty($ordernote)) $ordernote="Course Subscription";
	  $postData = array( 
	  "appId" => CashFree_AppID, 
	  "orderId" => $orderid, 
	  "orderAmount" => $amount, 
	  "orderCurrency" => "INR", 
	  "orderNote" => $ordernote, 
	  "customerName" => $fullname, 
	  "customerPhone" => $phoneno, 
	  "customerEmail" => $email,
	  "paymentModes" => "",
	  "returnUrl" => $returnurl, 
	  "notifyUrl" => $notifyurl
	);
	 // get secret key from your config
	 ksort($postData);
	 
	 $signatureData = "";
	 foreach ($postData as $key => $value){
		  $signatureData .= $key.$value;
	 }
	 $signature = hash_hmac('sha256', $signatureData, CashFree_SecretKey,true);
	 $signature = base64_encode($signature);
	 return $signature;
}

function SendEmail($to,$subject,$content,$header){
	$to_email = $to;
   $body = $content;
   $headers = "From: $header";
 
   if (mail($to_email, $subject, $body, $headers)) {
      echo("Email successfully sent to $to_email...");
   } else {
      echo("Email sending failed...");
   }
}

function ConvertTemplateToHTML($json,$rowArray){
	//$json is the json definition which also includes template
	//$list is the one dimensiona array (key and value) which are extracted from mysql db
	$api=new EireneApi();
	$temp="";
	foreach($json->template as $k=>$t){
		if(is_string($t)){
			$temp.=$t;
			foreach($rowArray as $k=>$v){
				if(strpos($temp,"||$k||")!==false){										
					$temp=str_replace("||$k||",$v,$temp);
				}						
			}
		}else{
			if(isset($t->if)){
				$temp1=$t;							
				$tmp="{}";
				$tmp=json_decode($tmp);
				foreach($temp1 as $k1=>$t1){
					$tmp->$k1=$t1;
					foreach($rowArray as $k2=>$l){
						if(strpos($t1,"||$k2||")!==false){										
							$tmp->$k1=str_replace("||$k2||",$l,$tmp->$k1);
						}						
					}
				}
				
				/*condition check*/
				$chk=$api->CheckIfCondition($tmp,$GLOBALS["value"],$GLOBALS["result"]);							
				
				/*assign previous values*/
				foreach($rowArray as $k2=>$l){					
					$GLOBALS["value"]["previous_".$k2]=$l;
				}
				
				if($chk==true){
					if(!empty($temp1->then)){ $temp.=$temp1->then;}							
				}else{
					if(!empty($temp1->else)) $temp.=$temp1->else;
				}
			}else if(isset($t->casevariable)){											
				$tmp="{}";
				$tmp=json_decode($tmp);								
				foreach($t as $k1=>$t1){
					$tmp->$k1=$t1;
				}
				foreach($rowArray as $k2=>$l){
					if(strpos($tmp->casevariable,"||$k2||")!==false){										
						$tmp->casevariable=str_replace("||$k2||",$l,$tmp->casevariable);
					}
				}
				$temp.=$api->GetCaseVariableValue($tmp,$GLOBALS["value"],$GLOBALS["result"]);
			}
		}
	}
	return $temp;
}

function conditionCheck($if,$op,$val){
	$res=false;
	if($val=="BLANK") $val="";
	//echo $if.$op.$val."<br>";
	//echo strlen($if)." = ".strlen($val)."<br>";
	//print_r($GLOBALS["value"]);echo "<br>";
	$if=ParseIfValue($if);
	$val=ParseIfValue($val);
	if($op=="="){
		if($if==$val) $res=true;
	}else if($op=="!="){
		if($if!=$val) $res=true;
	}else if($op==">"){
		if(floatval($if)>floatval($val)) $res=true;
	}else if($op==">="){
		if(floatval($if)>=floatval($val)) $res=true;
	}else if($op=="<"){
		if(floatval($if)<floatval($val)) $res=true;
	}else if($op=="<="){
		if(floatval($if)<=floatval($val)) $res=true;
	}
	if($res) 
		$res=1;
	else
		$res=0;
	//echo $res."<br>";
	return $res;
}
function ParseIfValue($value){
	if(strpos($value,"(") && strpos($value,")") && strpos($value," ")===false){
		$value=trim($value);
		$pos1=strpos($value,"(");
		$fun=trim(substr($value,0,$pos1));
		$param=trim(substr($value,$pos1+1,-1));
		//echo $fun."(".$param.")";
		if(function_exists($fun))
			return $fun($param);
		else
			return $fun."(".$param.")";
	}else
		return $value;
}

function conditionCheckOr($if,$op,$val,$orif,$orop,$orval){
	$res=false;
	$res1=false;
	$res2=false;
	$res1=conditionCheck($if,$op,$val);
	$res2=conditionCheck($orif,$orop,$orval);
	if($res1==true || $res2==true) $res=true;
	return $res;
}
function conditionCheckAnd($if,$op,$val,$orif,$orop,$orval){
	$res=false;
	$res1=false;
	$res2=false;
	$res1=conditionCheck($if,$op,$val);
	$res2=conditionCheck($orif,$orop,$orval);	
	//echo (int)$res1." = ".$if.$op.$va."; ".$res2." = ".$orif.$orop.$orval."<br>";
	if($res1==true && $res2==true) $res=true;
	return $res;
}

function imageCreateFromAny($filepath) {
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
    $allowedTypes = array(
        1,  // [] gif
        2,  // [] jpg
        3,  // [] png
        6   // [] bmp
    );
    if (!in_array($type, $allowedTypes)) {
        return false;
    }
    switch ($type) {
        case 1 :
            $im = imageCreateFromGif($filepath);
        break;
        case 2 :
            $im = imageCreateFromJpeg($filepath);
        break;
        case 3 :
            $im = imageCreateFromPng($filepath);
        break;
        case 6 :
            $im = imageCreateFromBmp($filepath);
        break;
    }   
    return $im; 
}
function setOutPutToDebug($str){
	setOutput("debug",$GLOBALS["result"],$str."<br><br>",json_decode("{}"),true);
}
function myErrorHandler($errno, $errstr, $errfile, $errline){
	if(strpos($errstr,"getaddress")!==false) return;
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }
	/*backtrack*/
	//debug_print_backtrace();
	
	setOutPutToDebug($errstr.' ['.$errfile.']['.$errline.'] ');
	$errfile=explode("\\",$errfile);
	$errfile=$errfile[count($errfile)-1];
    switch ($errno) {
	case E_USER_ERROR:
		if(empty($GLOBALS["result"]["dom"])){
			$GLOBALS["result"]["dom"]=[];			
		}else{
			$GLOBALS["result"]["dom"]=json_decode($GLOBALS["result"]["dom"]);			
		}
		$val1="";
		$j=json_decode("{}");$j->elem="";$j->fun="shownotification";$j->mode="alert";$j->val="Fatal error on line [".$errfile."][".$errline."][".$GLOBALS["currentstmt"]."][".$errstr."]";
		//$GLOBALS["result"]["dom"][]='{"elem":"","fun":"shownotification","mode":"alert","val":"Fatal error on line '.$errline.' in file '.$errfile.' id:'.$GLOBALS['currentstmt'].'}'";
		$GLOBALS["result"]["dom"][]=json_encode($j,JSON_UNESCAPED_UNICODE);
		$GLOBALS["result"]["dom"]=json_encode($result["dom"],JSON_UNESCAPED_UNICODE);		
		//exit(1);
		break; 
    case E_USER_WARNING:
		if(empty($GLOBALS["result"]["dom"])){
			$GLOBALS["result"]["dom"]=[];			
		}else{
			$GLOBALS["result"]["dom"]=json_decode($GLOBALS["result"]["dom"]);			
		}
		$val1="";
		$j=json_decode("{}");$j->elem="";$j->fun="shownotification";$j->mode="warning";$j->val="Warning error on line [".$errfile."][".$errline."][".$GLOBALS["currentstmt"]."][".$errstr."]";
		//$GLOBALS["result"]["dom"][]='{"elem":"","fun":"shownotification","mode":"warning","val":"'.'['.$errno.']['.$errfile.']['.$errline.']'.$errstr.' '.$GLOBALS['currentstmt'].'"}';
		$GLOBALS["result"]["dom"][]=json_encode($j,JSON_UNESCAPED_UNICODE);
		$GLOBALS["result"]["dom"]=json_encode($result["dom"],JSON_UNESCAPED_UNICODE);		
		//exit(1);
		break; 
    case E_USER_NOTICE:
		if(empty($GLOBALS["result"]["dom"])){
			$GLOBALS["result"]["dom"]=[];			
		}else{
			$GLOBALS["result"]["dom"]=json_decode($GLOBALS["result"]["dom"]);			
		}
		$val1="";
		$j=json_decode("{}");$j->elem="";$j->fun="shownotification";$j->mode="info";$j->val="Notice error on line [".$errfile."][".$errline."][".$GLOBALS["currentstmt"]."][".$errstr."]";		
		//$GLOBALS["result"]["dom"][]='{"elem":"","fun":"shownotification","mode":"info","val":"['.$errno.']['.$errfile.']['.$errline.']'.$errstr.' '.$GLOBALS['currentstmt'].'"}';
		$GLOBALS["result"]["dom"][]=json_encode($j,JSON_UNESCAPED_UNICODE);
		$GLOBALS["result"]["dom"]=json_encode($result["dom"],JSON_UNESCAPED_UNICODE);		
		//exit(1);
		break; 
    default:
		if(empty($GLOBALS["result"]["dom"])){
			$GLOBALS["result"]["dom"]=[];			
		}else{
			$GLOBALS["result"]["dom"]=json_decode($GLOBALS["result"]["dom"]);			
		}
		$val1="";
		$j=json_decode("{}");$j->elem="";$j->fun="shownotification";$j->mode="alert";$j->val="Error on line [".$errfile."][".$errline."][".$GLOBALS["currentstmt"]."][".$errstr."]";				
		//$GLOBALS["result"]["dom"][]='{"elem":"","fun":"shownotification","mode":"info","val":"Unknown error type: ['.$errno.']['.$errfile.']['.$errline.']'.$errstr.' '.$GLOBALS['currentstmt'].'"}';
		$GLOBALS["result"]["dom"][]=json_encode($j,JSON_UNESCAPED_UNICODE);
		$GLOBALS["result"]["dom"]=json_encode($GLOBALS["result"]["dom"],JSON_UNESCAPED_UNICODE);
		//exit(1);
		break;
    }
 
    /* Don't execute PHP internal error handler */
    return true;
}

function encrypt($str){
  	/* Store the cipher method */
	$ciphering = "AES-128-CTR";	  
	/* Use OpenSSl Encryption method */
	$iv_length = openssl_cipher_iv_length($ciphering); 
	$options = 0; 	  
	/* Non-NULL Initialization Vector for encryption */
	$encryption_iv = '1234567891011121';	  
	/* Store the encryption key */
	$encryption_key = "1234Abcd"; 
	  
	/* Use openssl_encrypt() function to encrypt the data */
	$encryption = openssl_encrypt($str,$ciphering,$encryption_key, $options, $encryption_iv); 
	return $encryption;
}
function decrypt($str){	
	/* Store the cipher method */
	$ciphering = "AES-128-CTR";
	/* Use OpenSSl Encryption method */
	$iv_length = openssl_cipher_iv_length($ciphering); 
	// Non-NULL Initialization Vector for decryption 
	$decryption_iv = '1234567891011121'; 

	// Store the decryption key 
	$decryption_key = "1234Abcd"; 
	$options = 0;
	// Use openssl_decrypt() function to decrypt the data 
	$decryption=openssl_decrypt ($str, $ciphering,  
		$decryption_key, $options, $decryption_iv); 

	return $decryption; 
}

function ConvertIntoMatrix($input,&$rowdisplay,&$coldisplay,&$rows,&$cols,&$rowindex,&$id,$sqlJson){
	$output=array();
	/*merge seperator*/
	$seperator="<br>";
	if(isset($sqlJson->contentseperator)) $seperator=$sqlJson->contentseperator;
	
	
	foreach($input as $k=>$v){
		$row=$sqlJson->row;
		$col=$sqlJson->col;
		$val=empty($sqlJson->val)?"":$sqlJson->val;
		if(empty($val)){
			$val=empty($sqlJson->value)?"":$sqlJson->value;
		};
		
		$mergecontent=false;
		if(isset($sqlJson->keepallcontent)){
			if($sqlJson->keepallcontent==1 || $sqlJson->keepallcontent==true){
				if(isset($output[$v[$row]."_".$v[$col]])) $mergecontent=true;
			}
		}
		
		if(!$mergecontent)
			$output[$v[$row]."_".$v[$col]]=$v[$val];
		else{
			
			$output[$v[$row]."_".$v[$col]].=$seperator.$v[$val];
		}
		
		$rowindex[$v[$row]."_".$v[$col]]=$k;
		if(isset($v["id"])) $id[$v[$row]."_".$v[$col]]=$v["id"];
		foreach($v as $k=>$r){
			if($k==$row){
				if(in_array($r,$rows)==false){
					$rows[]=$r;
					$rowdisplay[$r]=isset($sqlJson->rowdisplay)?$v[$sqlJson->rowdisplay]:$r;
						
				}
			}else if($k==$col){
				if(in_array($r,$cols)==false && !empty($r)){
					$cols[]=$r;
					$coldisplay[$r]=isset($sqlJson->coldisplay)?$v[$sqlJson->coldisplay]:$r;
				}
			}					
		}		
	}
	return $output;
}

function GetStringFromJson(&$sqlJson,$nodename,$seperator="",$convertSpecialCharToHTMLCode=false,$supplyValueToVariable=true){
	$processobjclass=new EireneProcessObject();
	$str=$processobjclass->ProcessObject($sqlJson,$nodename,$seperator,$convertSpecialCharToHTMLCode,$supplyValueToVariable);
	return $str;
}

function ConvertNumberToWords(float $number){
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');
    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }
    $Rupees = implode('', array_reverse($str));
    $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
    return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
}
function GetPermissionSettingsForMenu($pluginid){
		$str="";
		/*step 1 - Get menu/button names*/
		$db=$GLOBALS["db"];
		$sql="SELECT formbuttons FROM eirene_plugin WHERE id='$pluginid'";
		$formbuttons = $db->GetValue($sql);
		$formbuttons=json_decode($formbuttons);
		if(empty($formbuttons)) return "";
		$buttons=array();
		foreach($formbuttons as $b){
			$buttons[]=$b->name;
		}
		
		/*step 2 - Get userid and names*/
		$sql="SELECT a.id,a.username FROM eirene_users a WHERE a.profileid IN (SELECT profileid FROM eirene_permission WHERE pluginid1='$pluginid')";
		$users=$db->GetList1($sql);
		
		/*step 3 - Get existing permission*/
		$sql="SELECT userid,permission,view_rights FROM eirene_permission WHERE pluginid1='$pluginid' AND userid IS NOT NULL";
		$permission=$db->GetList1($sql);
		$perm=array();
		foreach($permission as $p){
			if(!empty($p[1]))
				$perm[$p[0]."_".$p[2]]=$p[1];
		}
		$permission=null;
		
		/*step 4 - Create a table*/
		$str="<table class='eirene-table dataTable'><thead><tr><th></th>";
		foreach($buttons as $k=>$b){
			if($k>0)
				$str.="<th>".$b."</th>";
			else
				$str.="<th style='position:sticky; left:0px;z-index:9;'>".$b."</th>";
		}
		$str.="</tr><tbody>";
		
		foreach($users as $u){
			$str.="<tr><td style='position:sticky; left:0px;z-index:9;'>".$u[1]."</td>";
			foreach($buttons as $b){
				if(empty($perm[$u[0]."_".$b])) $perm[$u[0]."_".$b]="";
				$str.="<td><select class='select0' values='0:No Permission,1:Permission' onchange='Eirene.runStmt(&apos;4mnusv&apos;,{user:&apos;".$u[0]."&apos;,plugin:&apos;".$pluginid."&apos;,val:$(this).val(),button:&apos;".$b."&apos;})' value='".$perm[$u[0]."_".$b]."'></select></td>";				
			}
			$str.="</tr>";
		}		
		$str.="</tbody></table><script>datatable('#workareaTablebox');</script>";		
		setOutput("#workareaTablebox",$GLOBALS["result"],$str,"");
	}
	function GetMenu($superadmin){		
		if(!isset($GLOBALS["value"]["pluginid"])) return false;
		if(!isset($GLOBALS["userinfo"]["id"])) return false;
		$pluginid=$GLOBALS["value"]["pluginid"];
		$userid=$GLOBALS["userinfo"]["id"];
		$db=$GLOBALS["db"];
		
		/*get permission*/
		$sql="SELECT permission,view_rights FROM eirene_permission WHERE pluginid1='$pluginid' AND userid ='$userid'";
		$list=$db->GetList1($sql);
		
		$list1=array();
		foreach($list as $l){
			$list1[$l[1]]=$l[0];
		}
		$list=$list1;
		$list1=null;	
		
		/*get button list*/
		$sql="SELECT formbuttons FROM eirene_plugin WHERE id='$pluginid'";
		$buttons=$db->GetValue($sql);
		$buttons=json_decode($buttons);
				
		$menu="";
		if($buttons){
			$menu=array();
			foreach($buttons as $b){
				if(!isset($b->onclick)) $b->onclick="";
				$b->onclick=str_replace("'","&apos;",$b->onclick);
				if($superadmin==1){
					if(!isset($b->caption)) $b->caption="";
					$menu[]='<li><a href="#" onclick="'.$b->onclick.'"><span class="'.$b->icon.' mr-2"></span><span class="caption">'.$b->caption.'</span></a></li>';
				}else{
					if(!empty($list[$b->name])){						
						$menu[]='<li><a href="#" onclick="'.$b->onclick.'"><span class="'.$b->icon.' mr-2"></span><span class="caption">'.$b->caption.'</span></a></li>';
					}
				}
			}
			$menu="<ul>".implode("",$menu)."</ul>";
			
		}		
		setOutput("menu",$GLOBALS["result"],$menu,"");
	}

function setGlobalValue($variable,$value=""){
	if($value==""){
		$v=explode(";",$variable);
		if(isset($v[0])) $variable=$v[0];
		if(isset($v[1])) $value=$v[1];
	}
	$GLOBALS[$variable]=$value;
}
function GetAnswerToUserQueryBiblios($query){
	$ai=new BibliosAI();
	$ai->processAlgorithm($query,"queryresponse");
	return true;
		
	/*initialize*/
	$db=$GLOBALS["db"];
	
	/*get list of nouns from user query*/
	$postag=new POSTag();
	$postag->POSTagging($query,"","");
	//echo $postag->TaggedSentence;
	$nounlist=array();
	$adjlist=array();
	foreach($postag->WordType as $k=>$v){
		if($v=='$n' || $v=='$n2'|| $v=='$np'){
			$nounlist[]=$postag->WordList[$k];
		}else if($v=='$adj' || $v=='$adj2'|| $v=='$adj3'){
			$adjlist[]=$postag->WordList[$k];
		}
	}
	
	
	if(empty($nounlist)){		
		setOutput("queryresponse",$GLOBALS["result"],"Empty Query/No result available","",false);		
		return false;
	}
		
	
	/*2. insert query - only unique queries are inserted*/	
	$sql="SELECT COUNT(*) from biblios_userqueries where query='$query'";
	$res=$db->GetValue($sql);
	if(empty($res)){
		$sql="INSERT INTO biblios_userqueries (id,query) VALUES (UUID(),'$query')";			
		$db->execute($sql);
	}		
	//setOutput("queryresponse",$GLOBALS["result"],"Empty Keyword ".$query,"",false);		
	//return false;
		
	/*search with keyword*/
	$sql="SELECT replace(journal,'<br>','\n') as journal,tags from biblios_journal where ";
	foreach($nounlist as $k=>$n){
		if($k>0) $sql.="and ";
		$sql.="CONCAT(tags,',') like'%$n,%'";
	}	
	$res=$db->GetList1($sql,true);	
	if(empty($res)){
		/*if no result then search for keyword in journal entry*/
		/*build query consisting of adjective and nous*/
		$qry=array();
		foreach($nounlist as $n){
			$qry[]=" journal like '%$n%'";
		}
		foreach($adjlist as $a){
			$qry[]=" journal like '%$a%'";
		}
		$qry=implode(" and ",$qry);
		$sql="SELECT replace(journal,'<br>','\n') as journal,tags from biblios_journal where ".$qry;
		$res=$db->GetList1($sql,true);
		
		if(empty($res)){
			setOutput("queryresponse",$GLOBALS["result"],"No result found","",false);
			/*enter new subject keyword found if any in a new table db*/
			return false;
		}else{
			$resstring="";
			foreach($res as $k=>$r){
				$resstring=ExtractLineWithMostAppropriateAnswer($nounlist,$adjlist,$r["journal"]);
				if(empty($resstring)){
					$res[$k]["journal"]="";
				}else{
					$res[$k]["journal"]	=$resstring;
				}
			}
		}
	}
	
	/*results will have four parts intro,body,evaluation,conclusion*/
	$output=array();
	$output["introduction"]=array();
	$output["body"]=array();
	$output["evaluation"]=array();
	$output["conclusion"]=array();
	
	foreach($res as $r){		
		$temptag=explode(",",$r["tags"]);
		$tagfound="";		
		foreach($temptag as $tk=>$tv){			
			if(strtolower($tv)==strtolower($nounlist[0])){				
				$tagfound=$tk;				
				break;
			}
		}
		
		//foreach($temptag as $tk=>$tv){
			//$output["introduction"][]="<b>".$tv."</b><br>";
			//if(in_array(strtolower(trim($tv)),$nounlist)){
			if(!$tagfound==""){
				if(isset($temptag[$tagfound+1])){					
					if(strtolower(trim($temptag[$tagfound+1]))=="evaluation" || strtolower(trim($temptag[$tk+1]))=="assessment")
						$output["evaluation"][]=$r["journal"];
					else if(strtolower(trim($temptag[$tagfound+1]))=="conclusion" || strtolower(trim($temptag[$tk+1]))=="summary")
						$output["conclusion"][]=$r["journal"];
					else if(strtolower(trim($temptag[$tagfound+1]))=="definition" || strtolower(trim($temptag[$tk+1]))=="origin")
						$output["introduction"][]=$r["journal"];
					else{						
						if(empty($output["body"][ucwords($temptag[$tagfound+1])]))
							$output["body"][ucwords($temptag[$tagfound+1])]=$r["journal"];
						else{							
							$output["body"][ucwords($temptag[$tagfound+1])].="<br>".$r["journal"];
						}
					}
				}else
					$output["introduction"][]=$r["journal"];
			}else{
				$output["introduction"][]=$r["journal"];
			}
		//}
		
	}
	
	$outputstring="";
	if(!empty($output["introduction"])) $outputstring.=implode("<br>",$output["introduction"]);
	if(!empty($output["body"])){
		foreach($output["body"] as $k=>$v){
			$outputstring.="<br><br>".$k."<br>".$v;
		}
	}
	if(!empty($output["evaluation"])) $outputstring.=implode("<br>",$output["evaluation"]);
	if(!empty($output["conclusion"])) $outputstring.=implode("<br>",$output["conclusion"]);
	
	$outputstring=str_replace("\n","<br>",$outputstring);
	setOutput("queryresponse",$GLOBALS["result"],$outputstring,"",false);
	return $outputstring;
}

function ExtractLineWithMostAppropriateAnswer($nounlist,$adjlist,$paragraph){
	$paragraph=explode("\n",$paragraph);
	foreach ($paragraph as $p){
		$allnounsfound=true;
		$alladjfound=true;
		foreach($nounlist as $n){
			if(!stripos($p,$n)) {
				$allnounsfound=false;
				break;
			}
		}
		foreach($adjlist as $n){
			if(!stripos($p,$n)) {
				$alladjfound=false;
				break;
			}
		}
		if($allnounsfound && $alladjfound){
			return $p;
		}		
	}
	return "";
}


function in_string($haystack,$needle){
	/*$haystack needs to be string seperated by comma*/
	/*$needle needs to be a string that will be searched in $haystack*/
	/*The function searches haystack and if any one of the haystack item matches with needle, it returns true*/
	/*this function is case sensitive*/
	if(!is_string($haystack) || !is_string($needle)) return false;
	if(empty($needle)) return false;
	if(empty($haystack)) return false;
	$haystack=explode(",",$haystack);	
	$found=false;
	foreach($haystack as $n){
		$n=trim($n);		
		if($n==$needle){
			$found=true;
			break;
		}
	}
	return $found;	
}

function ArraySearch_Any($needle,$haystack){
	/*$needle needs to be an array*/
	/*$haystack needs to be an array*/
	/*The function finds out if any item of needle is found in haystack*/
	if(!is_array($needle) || !is_array($haystack)) return false;
	$found=false;
	foreach($needle as $n){
		if(in_array($n,$haystack)){
			$found=true;
			return $found;
		}
	}
	return $found;	
}

function ArraySearch_All($needle,$haystack){
	/*$needle needs to be an array*/
	/*$haystack needs to be an array*/
	/*The function searches all arrayitem of needle and returns true if all arrayitems of needle are found in the haystack*/
	if(!is_array($needle) || !is_array($haystack)) return false;
	$found=true;
	foreach($needle as $n){
		if(!in_array($n,$haystack)){
			$found=false;
			return $found;
		}
	}
	return $found;	
}

function extractTextWithinParentheses($inputString) {
    $pattern = '/\((.*?)\)/'; // Regular expression to match text within parentheses
    preg_match_all($pattern, $inputString, $matches); // Match all occurrences
    
    // Extracting text from matches
    $texts = array();
    foreach ($matches[1] as $match) {
        $texts[] = $match;
    }
    
    return $texts;
}

function GetAllVariables($inputString) {
	$inputString=" ".$inputString." ";
    $pattern = '/\|\|(.*?)\|\|/'; // Regular expression to match text within double bars
    preg_match_all($pattern, $inputString, $matches); // Match all occurrences
    
    // Extracting text from matches
    $texts = array();
    foreach ($matches[1] as $match) {
        $texts[] = $match;
    }
    
    return $texts;
}

?>