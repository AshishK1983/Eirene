<?PHP
class EireneUx{	
	private $permission="";
	private $viewrights="";
	private $formname="";	
	private $isValidUser=false;
	private $stmts=array();
	function __construct() {
		$this->stmts['GetForm']='{"action":"GetForm","outputto":"html","output":"form","formname":"||formname||","onsuccess":"cmd:dom,fun:function;showForm1;||formname||;||form||;||title||;||getid||;||saveid||"}';
		$this->stmts['upload']='{"action":"Upload File","outputto":"html","output":"upload","directory":"","maxsize":"20mb","filetype":"mp3,mpeg,mpeg-3,ogg,wav,pdf,doc,docx,xlx,xlxs,ppt,pptx,mp4,jpg,jpeg,png,tiff,bmp,svg","path":"resource/media/","onsuccess":"cmd:dom,fun:showtoast;success;Upload successful-cmd:dom,fun:attr;input[name=||elemname||];value;||GET_uploadedfilename||"}';
		$this->stmts['remf']='{"action":"Remove File","validate":"eirene_users status>0,online_course_users status>0 and isfaculty=1","outputto":"php","output":"res","path":"||path||"}';
		$this->stmts['editrecord']='{"action":"Get Row","outputto":"html","output":"edit","param":"FIELD,TBLNM,ID","paramseperator":"chr(1)","stmt":"SELECT ||FIELD|| FROM ||TBLNM|| WHERE id='||ID||'"}';
		$this->stmts['initialize']='{"action":"IF","name":"Check Maintanence","outputto":"php","output":"chk","tbl":"eirene_plugin","fld":"maintanence","whr":"id=\'||pluginid||\'","value":"1","then":"MaintanenceMsg","else":"initializeCHK"}';
		$this->stmts['MaintanenceMsg']='{"action":"Return","name":"Maintanence Message","outputto":"html","output":"#workareaTablebox","value":"<table class=\'m-5\'><tr ><td class=\'p-10\'><span class=\'mif-tools mif-5x fg-blue\'></span></td><td class=\'p-10\'><h1 class=\'mb-5\'>Maintenance Mode!</h1>This site is currently under maintenance. We apologize for the inconvenience caused to you.  We\'re doing our best to get things back to working. In case of emergency, please feel free to contact The Team.</td></tr></table>"}';
		$this->stmts['initializeCHK']='{"action":"IF","name":"Initialize Check","outputto":"php","output":"getmenuchk","fld":"status","tbl":"eirene_users","whr":"id=\'||USERID||\'","value":"2","operator":"=","then":"initializeS","else":"initializeN"}';
		$this->stmts['initializeN']='{"action":"Initialize","name":"Initialize-Normal","outputto":"html","output":"formdef","fld":"a.form_html,b.permission,b.view_rights","tbl":"eirene_plugin a","join":"INNER JOIN eirene_permission b ON a.id=b.pluginid1","whr":"a.id=\'||pluginid||\' and b.profileid=\'||PROFILEID||\'","onsuccess":"initializePlugin1()","phpfunction":{"fun":"GetMenu","param1":"0"}}';
		$this->stmts['initializeS']='{"action":"Initialize","name":"Initialize-Super User","outputto":"html","output":"formdef","param":"pluginid","fld":"a.form_html,\'3\' as permission,\'\' as view_rights","tbl":"eirene_plugin a","whr":"a.id=\'||pluginid||\'","onsuccess":"initializePlugin1()","phpfunction":{"fun":"GetMenu","param1":"1"}}';
		$this->stmts['setup']='{"action":"Install","name":"Install Eirene","validate":"none","outputto":"php","output":"install","onsuccess":"cmd:dom,fun:showtoast;success;Install Successful-cmd:dom,fun:showmessage;#wiz;Install Success;success;The Install is successful.<br><br>Go ahead and explore the Eirene framework. <br><br>With best wishes<br>Eirene Team","onfailure":"cmd:dom,fun:showtoast;alert;Some error encountered during installation."}';
		$this->stmts['loginchk']='{"action":"IF","validate":"none","outputto":"php","output":"usercount","value":1,"operator":"=","then":"login1","else":"cmd:dom,fun:showtoast;alert;Authentication Failed1-cmd:dom,fun:animate;#loginform;horizontal;1000","tbl":"eirene_users","fld":"count(*)","whr":"((username=\'||USER||\' and pass=\'||PASS||\' and status>=1 and recordstatus=1) or (id=\'||COOKIE_USERID||\'))"}';
		$this->stmts['login1']='{"action":"Get Row","validate":"none","outputto":"html","output":"userinfo","outputcontent":"row","fld":"a.id as userid,a.username,a.fullname,a.profileid,a.status,a.fullname,a.defaultpluginid","tbl":"eirene_users a","whr":"((a.username=\'||USER||\' and a.pass=\'||PASS||\' and a.status>=1 and a.recordstatus=1) or (id=\'||COOKIE_USERID||\'))","onsuccess":"fetchmenu","jsfunction":"loginsuccess","phpfunction": [{"runonlyonsuccess": 1,"outputto": "php","output": "rand","fun": "setcookie","param1": "userid","param2": "||GET_userinfo_userid||","param3":"||TIME+1year||","param4":"/","param5":"","param6":0},{"fun":"session_start"}]}';
		$this->stmts['fetchmenu']='{"action":"IF","validate":"none","outputto":"html","output":"menuchk","fld":"count(*)","tbl":"eirene_users","whr":"id=\'||GET_userinfo_userid||\' and status=2","value":"1","operator":"=","then":"fetchmenu2","else":"fetchmenu1"}';
		$this->stmts['fetchmenu1']='{"action":"Get HTML","validate":"none","outputto":"html","output":"#side-menu","fld":"b.id,b.pluginname,b.icon,a.permission","tbl":"eirene_permission a","join":"INNER JOIN eirene_plugin b ON a.pluginid1=b.id","whr":"b.status=1 and a.profileid=\'||GET_userinfo_profileid||\'","parent":"","template":"<li id=\"mnu_||id||\" caption=\"||pluginname||\"  pluginid=\"||id||\"><a href=\"#\" onclick=\"Eirene.activePluginId=&apos;||id||&apos;;initializePlugin($(this).parent())\"><span class=\"icon\"><span class=\"||icon|| icon\"></span></span><span class=\"caption\">||pluginname||</span></a></li>"}';
		$this->stmts['fetchmenu2']='{"action":"Get HTML","validate":"none","outputto":"html","output":"#side-menu","fld":"b.id,b.pluginname,b.icon,\'3\' as permission","tbl":"eirene_plugin b","whr":"b.status>=1 and b.recordstatus=1","parent":"","template":"<li id=\"mnu_||id||\" caption=\"||pluginname||\" pluginid=\"||id||\"><a href=\"#\" onclick=\"Eirene.activePluginId=&apos;||id||&apos;;initializePlugin($(this).parent())\"><span class=\"icon\"><span class=\"||icon|| icon \"></span></span><span class=\"caption\">||pluginname||</span></a></li>"}';
		$this->stmts['installplugin']='{"action":"Install Plugin","validate":"eirene_users","onsuccess":"cmd:dom,fun:showdialog;Install Report;info;||installreport||"}';
		$this->stmts['exportplugin']='{"action":"Export Plugin","validate":"eirene_users","onsuccess":"cmd:dom,fun:showdialog;Export Successful;info;||exportreport||"}';
		$this->stmts['chngps']='{"action":"Save Row","validate":"eirene_users","tbl":"eirene_users","command":"update","fld":"pass","fldtype":"s","value":"||pass||","whr":"id=\'||userid||\'","onsuccess":"cmd:dom,fun:showtoast;success;Saving Successful","onfailure":"cmd:dom,fun:showtoast;alert;Saving Failed"}';
		
	}
	function FetchSql(&$master,$customid){
		$master='{}';
		$master=json_decode($master);
		$db=$GLOBALS['db'];
		$str="";$sql="";				
		
		if(isset($customid)==false){$master->error="Query ID Needed";return false;}		
				
		$jsonfilterval="";
		if(empty($GLOBALS["value"]["filter"])==false) $jsonfilterval=$GLOBALS["value"]["filter"];
				
		$api=new EireneApi();				
		if(empty($this->stmts[$customid])){				
			$sql="SELECT sql_statement,filter_exists,filter_fields,filter_fields_operator,filter_fields_caption,filter_fields_type,filter_fields_inputtype,filter_fields_options,edit_record,delete_record,restore_record,change_status,function1,function2,function3,function4,header FROM eirene_sqlstatements WHERE pluginid='".$GLOBALS["plugin"]["id"]."' and customid='$customid'";
			$res=$db->FetchRecord($sql);
			//echo $sql;
			//update that custom id is accessed.
			$sql="UPDATE eirene_sqlstatements SET usecount=usecount+1  WHERE pluginid='".$GLOBALS["plugin"]["id"]."' and customid='$customid'";
			$db->Execute($sql);
		}else{				
			$res=array();		
			$res["sql_statement"]=$this->stmts[$customid];
			//$res["filter_exists"]="";$res["filter_fields"]="";$res["filter_fields_operator"]="";$res["filter_fields_caption"]="";$res["filter_fields_type"]="";$res["filter_fields_inputtype"]="";$res["filter_fields_options"]="";$res["edit_record"]="";$res["delete_record"]="";$res["restore_record"]="";$res["change_status"]="";$res["function1"]="";$res["function2"]="";$res["function3"]="";$res["function4"]="";$res["header"]="";
		}
		
		if(empty($res)){			
			if(empty($GLOBALS["troubleshoot"]["showsql"]))
				$str='{"error":"No Query Found1."}';
			else
				$str='{"error":"No Query Found. '.$sql.'}';
			$master=json_decode($str);			
			return false;
		}		
		
		if(empty($res["sql_statement"])){			
			$str='{"error":"Empty Query Found."}';			
			$master=json_decode($str);
			return false;
		}
		
		try{
			/*decode JSON ->sql_statement*/			
			$res=json_encode($res);
			$res=json_decode($res);
			$master=json_decode($res->sql_statement);		
			$master->sqlid=$customid;/*sqlid is the six digit id representing the command. This id is unique per plugin*/
			$this->AddUserSuppliedVariable($master);	
		}catch(Exception $ex){				
			$str='{"error":"Sql Statment JSON Error"}';			
			$master=json_decode($str);
			return false;
		}
		
		/*validate sql query request*/
		$validate=$this->ValidateUser($master);
		if($validate==false){		
			$master='{}';
			$master=json_decode($master);				
			$master->error="Command Validation Failed3";				
			return $master;
		}		
											
		$this->GetPermission($master);		
		if(!isset($master->validate)) $master->validate="";
		if($this->permission==0 && $GLOBALS["plugin"]["iswebsite"]==0 && $master->validate!="none"){									
			$master->error="Permission Conflict_1";
			return false;					
		}					
		return $str;		
	}	
	 
	function SupplyParamValue($string,&$json="",$convertSpecialCharToHTMLCode=false){				
		if(strpos($string,"||")===false) return $string;
		if(trim($string)=="") return "";
		/*assign value from $value to any variable*/
		$varlist=GetAllVariables($string);//print_r($varlist); echo $string."<br>";
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["value"],$string,"",$convertSpecialCharToHTMLCode);
		
		if(strpos($string,"||")===false) return $string;
		
		/*Incase if $GLOBALS["value"][variable] exists & json variable also exists, the $GLOBALS["value"] will take precidence*/
		if(!empty($json)){			
			if(is_object($json)){
				$this->ReplaceVariableWithValue($varlist,$json,$string,"",$convertSpecialCharToHTMLCode);							
			}
		}
		if(strpos($string,"||")===false) return $string;
		
		/*addedvariable*/
		if(isset($json->addedvariables)){
			if(is_string($string))
				$this->ReplaceVariableWithValue($varlist,$json->addedvariables,$string,"",$convertSpecialCharToHTMLCode);			
		}		
		
		/*result array*/
		if(strpos($string,"||")===false) return $string;
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["result"],$string,"",$convertSpecialCharToHTMLCode);
		/*result GET_array*/
		if(strpos($string,"||")===false) return $string;
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["result"],$string,"GET",$convertSpecialCharToHTMLCode);
		
		/*globalphp array*/
		if(strpos($string,"||")===false) return $string;
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["globalphp"],$string,"",$convertSpecialCharToHTMLCode);
		/*globalphp GET_array*/
		if(strpos($string,"||")===false) return $string;
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["globalphp"],$string,"GET",$convertSpecialCharToHTMLCode);
		
		/*userinfo array*/
		if(strpos($string,"||")===false) return $string;
		$this->ReplaceVariableWithValue($varlist,$GLOBALS["userinfo"],$string,"USER",$convertSpecialCharToHTMLCode);
		
		/*plugin array*/
		if(strpos($string,"||")===false) return $string;
		if(isset($GLOBALS["plugin"])) $this->ReplaceVariableWithValue($varlist,$GLOBALS["plugin"],$string,"PLUGIN",$convertSpecialCharToHTMLCode);
				
		/*Further TreatString*/
		$string=$this->TreatWithPHPValues($string,$convertSpecialCharToHTMLCode);	
		
		
		/*Non available value to be blank*/
		$varlist=GetAllVariables($string);
		foreach($varlist as $v){
			$string=str_ireplace("||$v||","",$string);	
		}
		return $string;
	}
	
	function ReplaceVariableWithValue($variablelist,$source,&$string,$prefix,$convertSpecialCharToHTMLCode){
		if(!isset($source)) return false;
		if(!empty($prefix)){$prefix.="_";}		
		foreach($variablelist as $v){
			$varvalue=null;	
			$vcaps=strtoupper($v);
			$vsmall=strtolower($v);
			$varvalue=$this->ReplaceVariableWithValue1($v,$source);
			if(is_null($varvalue)){$varvalue=$this->ReplaceVariableWithValue1($vcaps,$source);}
			if(is_null($varvalue)){$varvalue=$this->ReplaceVariableWithValue1($vsmall,$source);}
						
			if(is_null($varvalue)==false && (is_string($varvalue) || is_numeric($varvalue))){
				if($convertSpecialCharToHTMLCode) $varvalue=str_ireplace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$varvalue);
				if($varvalue=="null") $varvalue="";
				$string=str_ireplace("||".$prefix.$v."||",$varvalue,$string);	
			}
		}
		return true;
	}
	
	function ReplaceVariableWithValue1($variable,$source){
		$varvalue=null;
		$variable=str_ireplace(array("GET_","get_","USER_","user_","PLUGIN_","plugin_"),"",$variable);			
		if(is_array($source) && isset($source[$variable]) && (is_string($source[$variable]) || is_numeric($source[$variable]))){
			$varvalue=$source[$variable];
		}else if(is_object($source) && isset($source->$variable) && (is_string($source->$variable) || is_numeric($source->$variable))){
			$varvalue=$source->$variable;
		}else if(is_string($source) && substr($source,0,1)==chr(123)){
			$source=json_decode($source);
			if(isset($source->$variable)){
				$varvalue=$source->$variable;
			}
		}else{					
			$varvalue=$this->ReplaceVariableWithValue2($variable,$source);
		}
		return $varvalue;
	}
	
	function ReplaceVariableWithValue2($variables,$source){
		$vv=explode("_",$variables);
		if(isset($vv[1])) $tempnm=$vv[1];
		$varvalue=null;
		if($source)
		
		if(count($vv)==2 && is_array($source) && isset($source[$vv[0]])){
			if(is_array($source[$vv[0]])){
				if(isset($source[$vv[0]][$vv[1]]) && (is_string($source[$vv[0]][$vv[1]]) || is_numeric($source[$vv[0]][$vv[1]]) )){
					$varvalue=$source[$vv[0]][$vv[1]];
				}
			}else if(is_string($source[$vv[0]]) && substr($source[$vv[0]],0,1)==chr(123)){
				$varvalue=$this->ReplaceVariableWithValue1($vv[1],$source[$vv[0]]);
			}
		}else if(count($vv)==2 && is_object($source) && isset($source->$tempnm)){			
			if(isset($source[$vv[0]]->$tempnm) && (is_string($source[$vv[0]]->$tempnm) || is_numeric($source[$vv[0]]->$tempnm) )){
				$varvalue=$source[$vv[0]]->$tempnm;
			}			
		}
		return $varvalue;
	}
	
	function AddUserSuppliedVariable(&$master){
		/*user supplied variables related to only this sql command will be inserted below*/
		$master->addedvariables=array();
		foreach($GLOBALS["value"] as $k=>$v){
			if(strpos($k,"var".$master->sqlid."_")!==false){
				if(is_string($v)) $v=trim($v);
				if($v=="undefined") continue;
				$tempvar=str_replace("var".$master->sqlid."_","",$k);
				$master->addedvariables[$tempvar]=$v;
				if($tempvar=="savemethod"){
					if(isset($master->savemethod)){
						if(isset($master->savemethod[$v]))
							$master->whr=$master->savemethod[$v];
					}else
						$master->$tempvar=$v;
				}else{
					$master->$tempvar=$v;
				}
			}
		}
	}
	
	function ValidateUser($sqlJson){
		$table="";
		if(isset($sqlJson->validate)) $table=$sqlJson->validate;
		if(empty($table)) $table="eirene_users status>0 and recordstatus>0";
		
		if($table=="none")return true;
		if($GLOBALS["plugin"]["validationneeded"]==0) return true;
		
		$table=explode(",",$table);
				
		$db=$GLOBALS["db"];
		$res=false;
		//print_r($sqlJson);
		$userid="";
		if(!empty($GLOBALS["userinfo"]["id"]))
			$userid=$GLOBALS["userinfo"]["id"];
		else if(!empty($GLOBALS["value"]["userid"])){
			$userid=$GLOBALS["value"]["userid"];
			$GLOBALS["userinfo"]["id"]=$userid;
		}else if(!empty($_COOKIE["userid"])){
			$userid=$_COOKIE["userid"];
			$GLOBALS["userinfo"]["id"]=$userid;
		}
		
		
		foreach($table as $t){			
			$t=trim($t);			
			if(!isset($GLOBALS["globalvalidate"][str_replace(" ","_",$t)])){				
				if(strpos($t," ")===false){
					/*the below codes will only check the table if user exists, if where criteria is to be checked then the else clause will take care of it*/
					$sql="SELECT count(id) FROM $t WHERE id='".$userid."'";
				}else{					
					/* The below query is for any additional where criteria along with table name*/
					$tt=explode(" ",$t);
					$t=$tt[0];
					$tt[0]="";
					$tt=implode(" ",$tt);
					$tt=trim($tt);
					if(!empty($tt)) $tt=" and $tt";
					$sql = "SELECT count(id) FROM $t WHERE id='".$userid."' ".$tt;					
				}				
				$res=$db->GetValue($sql);
				//echo $sql."<br>";
				//print_r($_COOKIE);
				//print_r($res);echo "<br>";
				//print_r($GLOBALS["userinfo"]);
				//print_r($GLOBALS["value"]);
			}else{
				if($GLOBALS["globalvalidate"][str_replace(" ","_",$t)]==true) $res=true;
			}
			if($res==1){
				$res= true;
				$GLOBALS["globalvalidate"][str_replace(" ","_",$t)]=true;
				break;
			}else{
				$GLOBALS["globalvalidate"][str_replace(" ","_",$t)]=false;
				//echo $sql;
			}
		}
		return $res;
	}
	
	function GetFilterHTML($res,&$whr,$jsonval){
		if(isset($res->filter_fields)==false)
			return false;
		$filter_fields=explode(",",$res->filter_fields);
		$filter_fields_operator=explode(",",$res->filter_fields_operator);
		$filter_fields_caption=explode(",",$res->filter_fields_caption);
		$filter_fields_type=explode(",",$res->filter_fields_type);
		$filter_fields_inputtype=explode(",",$res->filter_fields_inputtype);		
		$res_s=json_decode($res->sql_statement);
		$filter_append=empty($res_s->filter_append)?"":$res_s->filter_append;		
		$filter_append=explode(",",$filter_append);
		if(count($filter_append)!=count($filter_fields)){
			$filter_append=array();
			$len=count($filter_fields);
			for($i=0;$i<$len;$i++){
				array_push($filter_append,"");
			}
		}
		
		if(empty($jsonval)==false)
			$jsonval=json_decode($jsonval);
		if(isset($res->filter_fields_options)==false)
			$filter_fields_options="";
		else
			$filter_fields_options=$res->filter_fields_options;
		$str="";
		if (count($filter_fields)!= count($filter_fields_type) || count($filter_fields)!=count($filter_fields_inputtype)){
			return "Filter cannot be displayed. Mismatch in fields (".count($filter_fields).") and fieldstypes (".count($filter_fields_type).") and inputtypes(".count($filter_fields_inputtype).").";
		}else{
			$len=count($filter_fields);
			$db=$GLOBALS["db"];
			for($i=0;$i<$len;$i++){
				$fieldname=$filter_fields[$i];				
				if(empty($jsonval)==false){
					foreach($jsonval as $k=>$v){
						if($k==$fieldname){
							$ff=explode(" ",$fieldname);
							if(empty(trim($v))==false){								
								if($filter_fields_operator[$i]=="like")
									$v="'%".$v.$filter_append[$i]."%'";
								else{
									if($filter_fields_type[$i]!="n")
										$v="'".$v.$filter_append[$i]."'";
									else
										$v=$v.$filter_append[$i];
								}
								if(empty($whr)==false)
									$whr.=" and ".$ff[0]." ".$filter_fields_operator[$i]." ".$v;
								else
									$whr.=$ff[0]." ".$filter_fields_operator[$i]." ".$v;
							}
						}
					}
				}				
				        
				if(strtolower($filter_fields_inputtype[$i])=="text"){
					$str.="<input style='min-width:150px' type='text' param='true'  name='". $filter_fields[$i] ."' data-role='input' placeholder='". $filter_fields_caption[$i] ."'>";
				}else if(strtolower($filter_fields_inputtype[$i])=="datepicker"){
					$str.="<input type='date' param='true'  name='". $filter_fields[$i] ."'  placeholder='". $filter_fields_caption[$i] ."'>";
				}else if(strtolower($filter_fields_inputtype[$i])=="checkbox"){
					$str.="<input type='checkbox' param='true'  name='". $filter_fields[$i] ."' data-caption='". $filter_fields_caption[$i] ."'  data-role='checkbox' placeholder='". $filter_fields_caption[$i] ."'>";
				}else if(strtolower($filter_fields_inputtype[$i])=="select"){						
					$found=false;
					$opt="";
					if(gettype($filter_fields_options)=="string"){
						if(empty($filter_fields_options)==false)
							$filter_fields_options=json_decode($filter_fields_options);
					}				   
					foreach($filter_fields_options as $j=>$x){
						if($x->fieldname==$filter_fields[$i]){
							$found=true;
							$opt=$x->option;
							break;
						}   
					};				   
					if ($found==true){
						$opt=str_replace("u00a0"," ",$opt);						
						//echo $opt;
						$opt=FillDropdownHTML($opt,"");					  				  
						$str.="<select type='select' class='select1' name='". $filter_fields[$i] ."' style='width:200px;' param='true' placeholder='".$filter_fields_caption[$i]."'><option value=' '>[All]</option>".$opt."</select>";					  
					}
				}
				if(empty($str)==false)
					$str="<div style='margin-left:10px;' class='d-flex'>".$str."</div>";							
			}			
		}
		
		return $str;
	}
	function GetPermission(&$json){
		/* Permission Definition: 0- No permission, 1 - View, 2- Delete, 3 - Edit */		
			
		//echo "permission status = ".$status."<br>";
		if($GLOBALS["plugin"]["iswebsite"]==1){
			$this->permission=1;
			$this->viewrights="";
			$json->permission=1;
			$json->viewrights="";
			return true;
		}else if($GLOBALS["userinfo"]["status"]!=2){
			if(!empty($GLOBALS["plugin"]["id"]) && !empty($GLOBALS["userinfo"]["profileid"]))
				$sql="SELECT permission,view_rights FROM eirene_permission WHERE profileid='".$GLOBALS["userinfo"]["profileid"]."' and pluginid1='".$GLOBALS["plugin"]["id"]."'";
			else if(empty($GLOBALS["plugin"]["id"]) && !empty($GLOBALS["userinfo"]["profileid"]))
				$sql="SELECT permission,view_rights FROM eirene_permission WHERE profileid='".$GLOBALS["userinfo"]["profileid"]."'";
			else{
				$this->permission=0;
				$this->viewrights="";
				$json->permission=0;
				$json->viewrights="";
				return false;
			}
			
			$db=$GLOBALS["db"];
			$res=$db->FetchRecord($sql);
			
			if(count($res)==0){
				$this->permission=0;
				$this->viewrights="";
				$json->permission=0;
				$json->viewrights="";
				return false;
			}
			if($res["permission"]==0){
				$this->permission=0;
				$this->viewrights="";
				$json->permission=0;
				$json->viewrights="";
				return false;
			}else{				
				$this->permission=$res["permission"];
				$this->viewrights=$res["view_rights"];
				$json->permission=$res["permission"];
				$json->viewrights=$res["view_rights"];
			}			
		}else{
			$res='{"permission":"3","view_rights":""}';
			$this->permission=3;
			$this->viewrights="";
			$json->permission=3;
			$json->viewrights="";
			
		}
		return $res;
	}
		
	function TreatWithPHPValues($string,$convertSpecialCharToHTMLCode=false){
		if(stripos($string,"||IP||")!==false){
			$ip=$_SERVER['REMOTE_ADDR'];
			$string=str_ireplace("||IP||",$ip,$string);
			$geo="";
			try{
				//$geo=unserialize(file_get_contents("http://www.geoplugin.net/php.gp?ip=".$ip));
			}catch(Exception $ex){
				
			}
			if(!empty($geo)){
				try{					
					$string=(!empty($geo["geoplugin_city"]))?str_ireplace("||CITY||",$geo["geoplugin_city"],$string):$string;
					$string=(!empty($geo["geoplugin_region"]))?str_ireplace("||REGION||",$geo["geoplugin_region"],$string):$string;
					$string=(!empty($geo["geoplugin_countryName"]))?str_ireplace("||COUNTRY||",$geo["geoplugin_countryName"],$string):$string;
				}catch(Exception $ex){
					$string=str_ireplace("||CITY||","",$string);
					$string=str_ireplace("||REGION||","",$string);
					$string=str_ireplace("||COUNTRY||","",$string);
				}
			}
			$useragent=str_ireplace(",","",$_SERVER["HTTP_USER_AGENT"]);
			$string=str_ireplace("||USERAGENT||",$useragent,$string);
		}
		
		if(stripos($string,"||LASTINSERTID||")!==false){
			$db=$GLOBALS["db"];
			if(isset($db->lastInsertId))
				$lastinsertid=$db->lastInsertId;
			else
				$lastinsertid="";
			$string=str_ireplace("||LASTINSERTID||",$lastinsertid,$string);
		}
		if(stripos($string,"||USERID||")!==false){
			if(!empty($GLOBALS["userinfo"]["id"])){
				$r=$GLOBALS["userinfo"]["id"];
				if($convertSpecialCharToHTMLCode) $r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
				$string=str_ireplace("||USERID||",$r,$string);				
			}			
		}
		if(stripos($string,"||COOKIE")!==false){			
			foreach($_COOKIE as $ck=>$cv){				
				if(stripos($string,"||COOKIE_$ck||")!==false){				
					$string=str_ireplace("||COOKIE_$ck||",$cv,$string);				
				}
			}			
		}
		if(strpos($string,"||PROFILEID||")){
			$r=$GLOBALS["userinfo"]["profileid"];
			if($convertSpecialCharToHTMLCode) $r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
			$string=str_ireplace("||PROFILEID||",$r,$string);			
		}
		if((stripos($string,"||PLUGINID||")!==false)){
			$r=$GLOBALS["plugin"]["id"];
			if($convertSpecialCharToHTMLCode) $r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
			$string=str_ireplace("||PLUGINID||",$r,$string);			
		}
			
		if(stripos($string,"||PHPUNIQID||")!==false) {
			$r=uniqid();$GLOBALS["globalphp"]["phpuniqid"]=$r;
			if($convertSpecialCharToHTMLCode) $r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
			$string=str_ireplace("||PHPUNIQID||",$r,$string);			
		}
		if(stripos($string,"||PREVIOUS_PHPUNIQID||")!==false) {
			if(isset($GLOBALS["globalphp"]["phpuniqid"])) 
				$string=str_ireplace("||PREVIOUS_PHPUNIQID||",$GLOBALS["globalphp"]["phpuniqid"],$string);
		}
		if(stripos($string,"||CURDATE||")!==false) {			
			$string=str_ireplace("||CURDATE||",date("Y-m-d"),$string);
		}
		if(stripos($string,"||CURDATE_D_M_Y||")!==false) {			
			$string=str_ireplace("||CURDATE_D_M_Y||",date("d-m-Y"),$string);
		}
		if(stripos($string,"||CURDATE_Y_M_D||")!==false) {			
			$string=str_ireplace("||CURDATE_Y_M_D||",date("Y-m-d"),$string);
		}
		if(stripos($string,"||CURDATE_Y_M||")!==false) {			
			$string=str_ireplace("||CURDATE_Y_M||",date("Y-m"),$string);
		}
		if(stripos($string,"||CURDATETIME||")!==false) {			
			$string=str_ireplace("||CURDATETIME||",date("Y-m-d H:m:s"),$string);
		}
		if(stripos($string,"||CURDATETIME_D_M_Y||")!==false) {			
			$string=str_ireplace("||CURDATETIME_D_M_Y||",date("d-m-Y H:m:s"),$string);
		}
		
		/*TIME*/
		if(stripos($string,"||TIME")!==false){
			if(stripos($string,"||TIME+1year||")!==false) {
				$string=str_ireplace("||TIME+1year||",time()+31556926,$string);
			}else if(stripos($string,"||TIME+1month||")!==false) {
				$string=str_ireplace("||TIME+1month||",time()+2592000,$string);
			}else if(stripos($string,"||TIME+1week||")!==false) {
				$string=str_ireplace("||TIME+1week||",time()+604800,$string);
			}else if(stripos($string,"||TIME+1day||")!==false) {
				$string=str_ireplace("||TIME+1day||",time()+86400,$string);
			}
		}
		
		/* supply value from php array*/
		//$string=$this->ReplaceGETVariableWithValues($GLOBALS["result"],$string,"GET",$convertSpecialCharToHTMLCode);
		//$string=$this->ReplaceGETVariableWithValues($GLOBALS["globalphp"],$string,"GET",$convertSpecialCharToHTMLCode);
		//$string=$this->ReplaceGETVariableWithValues($GLOBALS["userinfo"],$string,"USER",$convertSpecialCharToHTMLCode);
		//if(isset($GLOBALS["plugin"])) $string=$this->ReplaceGETVariableWithValues($GLOBALS["plugin"],$string,"PLUGIN",$convertSpecialCharToHTMLCode);
		
		
		return $string;
	}
	
	
	function ReplaceGETVariableWithValues($sourcearray,$targetstring,$prefix,$convertSpecialCharToHTMLCode){
		if(is_string($targetstring)==false) return "";
		//if(strpos($targetstring,"||GET_")===false) return $targetstring;
		if(is_array($sourcearray)==false) return $targetstring;
		
		/*source array is the array which has key-value pair of values. This will be used to supply values to variables*/
		foreach($sourcearray as $k=>$r){			
			if(is_string($r) || is_numeric($r)){
				if(trim($r)=="") continue;
			}else if(is_array($r)){
				if(count($r)==0) continue;
			}
			
			if(stripos($targetstring,"_".$k."_")!==false){
				/*data needs to be obtained from json value */
				if(is_string($r) && substr($r,0,1)==chr(123)){
					$js=json_decode($r);
					foreach($js as $k1=>$j){
						if(substr($j,0,1)!=chr(123) && substr($j,0,1)!="["){
							if($convertSpecialCharToHTMLCode)
								$j=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$j);
							$targetstring=str_ireplace("||".$prefix."_".$k."_".$k1."||",$j,$targetstring);
						}
					}
				}else if(is_string($r) && substr($r,0,1)!="["){
					if($convertSpecialCharToHTMLCode)
						$r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
					$targetstring=str_ireplace("||".$prefix."_".$k."||",$r,$targetstring);
				}
				
			}else{
				//if(is_array($r)==true) {print_r($r);echo "<br>";}
				if(stripos($targetstring,"_".$k)!==false){
					if($convertSpecialCharToHTMLCode)
						$r=str_replace(array("-","'",'"'),array("&dash&","&apos&","&quot&"),$r);
					
					$targetstring=str_ireplace("||".$prefix."_".$k."||",$r,$targetstring);
				}
			}
		}		
		return $targetstring;
	}
	function MergeSqlCommand(&$json){
		/*merge sql commands from sqlids*/		
		if(!isset($json->merge)) return "";		
		if(is_string($json->merge)){
			$merge=explode(",",$json->merge);
			foreach($merge as $m){											
				$this->MergeSqlCommandHelper($json,$m);
			}
		}else if(is_object($json->merge)){
			/*format of $json->merge as an object is: merge:{"sqlid":"1a","node":"elem"} - node is optional*/
			if(!isset($json->merge->sqlid)) return "";
			if(!isset($json->merge->node)) $json->merge->node="";
			$this->MergeSqlCommandHelper($json,$json->merge->sqlid,$json->merge->node);			
		}else if(is_array($json->merge)){
			foreach($json->merge as $m){
				if(is_string($m)){
					$merge=explode(",",$m);
					foreach($merge as $mm){											
						$this->MergeSqlCommandHelper($json,$mm);
					}
				}else if(is_object($m)){
					if(!isset($m->if)){
						if(!isset($m->sqlid)) continue;
						if(!isset($m->node)) $m->node="";
						$this->MergeSqlCommandHelper($json,$m->sqlid,$m->node);
					}else{
						$api=new EireneApi();
						$chk=$api->CheckIfCondition($m,$GLOBALS["value"],$GLOBALS["result"]);
						if(!isset($m->sqlid)) continue;
						if(!isset($m->node)) $m->node="";
						if($chk) $this->MergeSqlCommandHelper($json,$m->sqlid,$m->node);
					}
				}					
			}
		}
		
	}
	function MergeSqlCommandHelper(&$json,$sqlid,$node=""){
		$sql="SELECT sql_statement FROM eirene_sqlstatements WHERE pluginid='".$GLOBALS["plugin"]["id"]."' and customid='".$sqlid."'";
		$res=$GLOBALS["db"]->FetchRecord($sql);
		if(empty($res)) return "";
		if(is_string($res)) return "";
		$res=json_encode($res);
		$res=json_decode($res);							
		$res=json_decode($res->sql_statement);
		//print_r($res1->sql_statement);
		foreach($res as $k=>$v){
			if($k=="action" || $k=="outputto"  || $k=="output" || $k=="validate" ) continue;
			if(isset($json->$k)) continue;
			if($node=="")
				$json->$k=$v;
			else if($node==$k)
				$json->$k=$v;
		}
	}
	
	private function GetFormDefinition($formname,$removefieldlist=false){
		/*This will return an object*/
		/*formdefinition contains field list. removefield will not copy fieldlist in the definition*/
		$db=$GLOBALS["db"];
		$sql="SELECT form FROM eirene_plugin WHERE id='".$GLOBALS["plugin"]["id"]."'";		
		$res=$db->GetValue($sql);
		$formdef="";
		if(!empty($res)){
			if(substr($res,0,1)==chr(123)) $res="[".$res."]";
			$res=json_decode($res);				
			foreach($res as $r){
				if(empty($r->name)) continue;								
				if($r->name!=$formname)
					continue;
				else{
					$formdef=$r;
					break;
				}					
			}
		}
		if(!empty($formdef)){
			if($removefieldlist==true) $formdef->field="";
		}
		return $formdef;
	}
	public function GetFormDefinitionFromTable($formname,$removefieldlist=true){
		/*This will return an object*/
		/*formdefinition contains field list. removefieldlist will not copy fieldlist in the definition*/
		$db=$GLOBALS["db"];
		$api=new EireneApi();
		$sql="SELECT table_def FROM eirene_plugin WHERE id='".$GLOBALS["plugin"]["id"]."'";		
		//$sql="SELECT tabledef FROM eirene_tables WHERE tablename='".$formname."'";	
		$res=$db->GetValue($sql);
		$formdef="";
		
		if(!empty($res)){
			if(substr($res,0,1)==chr(123)) $res="[".$res."]";
			$res=json_decode($res);				
			foreach($res as $r){
				if(empty($r->name)) continue;								
				if($r->name!=$formname)
					continue;
				else{					
					$formdef=$r;
					$formdef->tablename=$r->name;
					break;
				}					
			}
		}
		
		if(!empty($formdef) && $removefieldlist==true){
			$formdef->fields="";
		}else{
			if(!empty($formdef->fields) && is_array($formdef->fields)){
				foreach($formdef->fields as &$f){					
					if(strpos($f->name," ")){
						CreateTable_GetFieldNameTypeAndOther($f);						
					}
				}
				$tj=json_decode("{}");
				$tj->name="createdon";$tj->type="datetime";$tj->other="";$tj->label="Created On";
				$formdef->fields[]=$tj;
				$tj1=json_decode("{}");
				$tj1->name="modifiedon";$tj1->type="datetime";$tj1->other="";$tj1->label="Modified On";
				$formdef->fields[]=$tj1;
			}
		}		
		return $formdef;
	}
	public function GetFieldDefinitionFromTable($tablename,&$meta="",&$joindef=""){
		if(strpos($tablename," ")){
			$tablename=explode(" ",$tablename)[0];
		}
		/*This will return an object*/
		/*formdefinition contains field list. removefieldlist will not copy fieldlist in the definition*/
		$db=$GLOBALS["db"];
		$api=new EireneApi();		
		$sql="SELECT tabledef,meta,joindef FROM eirene_tables WHERE tablename='".$tablename."' and pluginid='||pluginid||'";	
		$sql=$this->SupplyParamValue($sql);
		$fielddef=$db->GetList1($sql);
		if(empty($fielddef)) return "";
		//echo $sql. " = ".$fielddef."<br>";
		$meta=json_decode($fielddef[0][1]);
		if(!empty($fielddef[0][2])) $joindef=json_decode($fielddef[0][2]);
		$fielddef1=json_decode($fielddef[0][0]);		
		if(!empty($fielddef1)){			
			$tj=json_decode("{}");
			$tj->name="createdon";$tj->alias="con";$tj->type="datetime";$tj->other="";$tj->label="Created On";
			$fielddef1[]=$tj;
			$tj1=json_decode("{}");
			$tj1->name="modifiedon";$tj1->alias="mon";$tj1->type="datetime";$tj1->other="";$tj1->label="Modified On";			
			$fielddef1[]=$tj1;
		}
		$str=json_decode("{}");
		$str->fields=$fielddef1;
		return $str;
	}
	
	function GetSql(&$master,$res="",$convertString=false){		
		$flddef="";
		
		/*if formname is indicated then fetch other attributes from the form or table_def*/
		if((isset($master->formname) || isset($master->getform))&& $master->action!="Get Form"&& $master->action!="Print Form"){			
			if($master->action=="Save Row"||$master->action=="Save"||$master->action=="Save Table"){				
				if(!isset($master->onsuccess)) $master->onsuccess="";
				if(!isset($master->onfailure)) $master->onfailure="";
				if(isset($master->onsavesuccess)) $master->onsuccess="cmd:dom,fun:showtoast;success;Saving Successful-".$master->onsavesuccess;
				if(isset($master->onsavefailure)) $master->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed-".$master->onsavefailure;
				if(stripos($master->onsuccess,"sav")===false) $master->onsuccess="cmd:dom,fun:showtoast;success;Saving Successful";
				if(stripos($master->onfailure,"sav")===false) $master->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed";					
				if(empty($master->command)) $master->command="insertorupdate";
				
				if(!empty($GLOBALS["value"]["var".$master->sqlid."_savemethodno"]) && isset($master->savemethod)){												
					$tempnum=$GLOBALS["value"]["var".$master->sqlid."_savemethodno"];
					$tempval=isset($master->savemethod[$tempnum])?$master->savemethod[$tempnum]:"";
					if(!empty($tempval))
						$master->whr=$tempval;
					else
						$master->whr="id='||ID||'";
				}else{
					if(empty($master->whr)) $master->whr="id='||ID||'";
				}
			}else if($master->action=="Delete Row"||$master->action=="Delete Row Permanently"){
				if(!isset($master->onsuccess)) $master->onsuccess="";
				if(!isset($master->onfailure)) $master->onfailure="";
				if(isset($master->ondeletesuccess)) $master->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-".$master->ondeletesuccess;
				if(isset($master->ondeletefailure)) $master->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed-".$master->ondeletefailure;
				if(stripos($master->onsuccess,"delet")===false) $master->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-cmd:dom,fun:hide;current";											
				if(stripos($master->onfailure,"delet")===false) $master->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed";
				if(empty($master->whr)) $master->whr="id='||ID||'";
			}else if($master->action=="Edit"){
				$master->outputto="html";
				$master->output="edit";
				$GLOBALS["value"]["formname"]=$master->formname;
				$frmdef=$this->GetFormDefinitionFromTable($master->formname,false);
				$newfld=array();
				$newfld[]="id";
				foreach($frmdef->fields as $f){
					if(in_string($master->fld,$f->name)){
						if(isset($f->alias))
							$newfld[]=$f->name." as ".$f->alias;
						else
							$newfld[]=$f->name;
					}
				}
				//$master->fld="id,".$master->fld;
				$master->fld=implode(",",$newfld);
				
				//$master->jsfunction="assignVariable Eirene.data.formname;".$master->formname.",showWindowInDialog ".$master->formname.",edit1";//
				$master->jsfunction="edit1";
				if(empty($master->whr)) $master->whr="id='||ID||'";				
			}else if($master->action=="Get Table"){
				$flddef=$this->GetFieldDefinitionFromTable($master->tbl);
				if(!isset($master->tableid)) $master->tableid="Table".rand(1,9999);
				/*primary tablenamealias - the function of primarytablenamealis is to help editable field know which is the primary table and the edited field will be saved to this table*/
				if(isset($master->primarytable)) $master->primarytablenamealias=$master->primarytable;
				if(!isset($master->primarytablenamealias) && strpos(trim($master->tbl)," ")){						
					$master->primarytablenamealias=trim(str_replace(" as "," ",$master->tbl));
					if(strpos($master->primarytablenamealias," "))
						$master->primarytablenamealias=explode(" ",$master->primarytablenamealias)[1];						
				}else if(isset($master->primarytablenamealias)){
					$master->primarytablenamealias=trim($master->primarytablenamealias);
					if(strpos($master->primarytablenamealias," "))
						$master->primarytablenamealias=explode(" ",$master->primarytablenamealias)[1];						
				}
				
				/*table header*/
				$master->header=$this->GetTableHeader($master);
				
				/*processing editablefields*/
				if(isset($master->editablefields)){
					if(!is_string($master->editablefields)) goto OutsideEditableFieldsProcessing;
					/*editablefields must be a string - else the below function fails*/
					$master->editablefields=explode(",",$master->editablefields);
					/*convert fields into array of strings*/
					
					if(is_string($master->fld)){
						$master->fld=explode(",",$master->fld);							
					}else if(is_array($master->fld)){
						$tempfld=array();
						foreach($master->fld as $k=>$v){
							if(is_string($v)){
								$tempv=explode(",",$v);
								foreach($tempv as $vv)
									$tempfld[]=$vv;
							}else if(is_object($v))
								$tempfld[]=$v;
						}
						$master->fld=$tempfld;
					}
					
					/*convert editablefields as objects*/
					foreach($master->fld as $k=>$v){
						if(is_object($v) || is_array($v)) continue;	
						//echo $v."=".implode(",",$master->editablefields)."<br>";							
						if(in_array($v,$master->editablefields)){								
							$master->fld[$k]=json_decode("{}");
							$master->fld[$k]->field=json_decode("{}");
							$master->fld[$k]->field->name=$v;
							$master->fld[$k]->field->type="editable";							
							$master->fld[$k]->field->value='<IF('.$v.' is null,"",'.$v.')>';								
						}
					}										
				}
			}
		}
		
		OutsideEditableFieldsProcessing:
		/*Supply values to variables*/
		$mfld="";$mtbl="";$mjoin="";$munion="";$mwhr="";$mhaving="";$mgrp="";$msrt="";$mlimit="";$moffset="";
		
		if(!empty($master->output)){$master->output=GetStringFromJson($master,"output");}
		if(!empty($master->outputto)){$master->outputto=GetStringFromJson($master,"outputto");}
		if(!empty($master->fld)){			
			if(isset($master->includeeditdelete)){
				//if(!isset($master->sqlid)) {print_r($master);echo '<br>';echo '<br>';}
				if(isset($GLOBALS["value"]["includeeditdelete".$master->sqlid])) $master->includeeditdelete=true;
				if($master->includeeditdelete==true || $master->includeeditdelete=="true"){					
					if(is_array($master->fld)){
						//print_r($master->fld);echo "<br><br>";
						$newfield=json_decode("{}");
						$newfield->field=json_decode("{}");
						$newfield->field->type="editdelete";
						$master->fld[]=$newfield;
						//print_r($master->fld);echo "<br><br>";
					}else if(is_string($master->fld)){						
						$fld=array();
						$fld[]=$master->fld;
						$newfield=json_decode("{}");
						$newfield->field=json_decode("{}");
						$newfield->field->type="editdelete";
						$fld[]=$newfield;						
						$master->fld=$fld;
					}
				}
				
			}
			//print_r($master->fld);echo "<br>";
			$master->fld=GetStringFromJson($master,"fld",",");$mfld=$master->fld;
			//print_r($master->fld);echo "<br><br>";
		}
		
		if(!empty($master->tbl)){$master->tbl=GetStringFromJson($master,"tbl",",");$mtbl=$master->tbl;}		
		if(!empty($master->join)){$master->join=GetStringFromJson($master,"join"," ");$mjoin=$master->join;}
		if(!empty($master->union)){$master->union=GetStringFromJson($master,"union"," ");$munion=$master->union;}
		//print_r($GLOBALS["value"]);echo "<br>";
		
		//if($master->sqlid=="solt1") {print_r($master->whr);echo "<br><br>";print_r($master);echo "<br><br>";}
		
		/*srt*/
		if(!empty($master->srt)){
			$master->srt=GetStringFromJson($master,"srt"," ");
			//print_r($master);
			if(!empty($flddef->fields)){				
				foreach($flddef->fields as $f){
					if(empty($f->alias)) $f->alias="";
					if(!empty($master->srt)){
						if(strpos($master->srt,$f->alias)!==false){							
							$master->srt=str_replace($f->alias,$f->name,$master->srt);							
						}
					}
				}
			}			
		}
		
		/*whr*/
		if(!empty($master->whr)){
			//if($master->sqlid=="solt1") print_r($GLOBALS["value"]);
			$master->whr=GetStringFromJson($master,"whr"," ");			
		}else if(empty($master->whr) && $master->action=="Get Table"){						
			$master->tbl=str_replace("  "," ",str_ireplace(" as "," ",$master->tbl));
			$temptbl=explode(" ",$master->tbl);
			$tempalias="";
			if(isset($temptbl[1])) $tempalias=trim($temptbl[1]).".";
						
			$tempwhr=array();
			if(!empty($master->defaultwhr)){
				$tempwhr[]=$master->defaultwhr;
			}else
				$tempwhr[]=$tempalias."recordstatus=1";	//print_r($GLOBALS["value"]);echo "<br>";print_r($_POST);			
			
			if(!empty($flddef->fields)){				
				foreach($flddef->fields as $f){
					if(empty($f->alias)) $f->alias="";					
					$tempfldnm=empty($f->alias)?$f->name:$f->alias;					
					if(!empty($GLOBALS["value"][$tempfldnm]) || (isset($GLOBALS["value"][$tempfldnm]) && $GLOBALS["value"][$tempfldnm]!="")){						
						if(strpos($f->type,"int")!==false){
							if(!isset($f->filterop))
								$tempwhr[]=$tempalias.$f->name."=".$GLOBALS["value"][$tempfldnm];
							else
								$tempwhr[]=$tempalias.$f->name." ".$f->filterop." ".$GLOBALS["value"][$tempfldnm];
						}else{							
							if(!isset($f->filterop))
								$tempwhr[]=$tempalias.$f->name."='".$GLOBALS["value"][$tempfldnm]."'";
							else{
								if($f->filterop=="like")
									$tempwhr[]=$tempalias.$f->name." like '%".$GLOBALS["value"][$tempfldnm]."%'";
								else
									$tempwhr[]=$tempalias.$f->name." ".$f->filterop." '".$GLOBALS["value"][$tempfldnm]."'";
							}
						}
					}else if(!empty($GLOBALS["value"][$tempfldnm."fromdate"]) && $f->type=="date"){
						$tempwhr[]=$tempalias.$f->name." >='".$GLOBALS["value"][$tempfldnm."fromdate"]."'";
						if(!empty($GLOBALS["value"][$tempfldnm."todate"]))
							$tempwhr[]=$tempalias.$f->name." <='".$GLOBALS["value"][$tempfldnm."todate"]."'";
					}else if(!empty($GLOBALS["value"][$tempfldnm."fromdate"]) && $f->type=="datetime"){
						$tempwhr[]="DATE(".$tempalias.$f->name.") >='".$GLOBALS["value"][$tempfldnm."fromdate"]."'";
						if(!empty($GLOBALS["value"][$tempfldnm."todate"]))
							$tempwhr[]="DATE(".$tempalias.$f->name.") <='".$GLOBALS["value"][$tempfldnm."todate"]."'";
					}
				}
			}
			

			/*add whr from viewrights*/
			if(empty($master->viewrights))
				$master->viewrights="{}";
			$rights=json_decode($master->viewrights);			
			$TableNamesAndAlias=$this->GetAllTableNamesAndAlias($master);			
			if(!empty($rights)){				
				foreach($rights as $r){					
					if(isset($r->tbl)){
						foreach($TableNamesAndAlias as $tn){
							/*$tn may contain two elements 0=tablename 1=alias. Some table names may not have alias*/							
							if($tn[0]==$r->tbl){
								if(!empty($tn[1])){
									$tempwhr[]=str_replace("TBLNM",$tn[1],$r->views);
								}else{
									$tempwhr[]=str_replace("TBLNM",$tn[0],$r->views);
								}
							}
						}						
					}
				}
			}
			if(empty($master->whr))
				$master->whr=implode(" and ",$tempwhr);
			else
				$master->whr.=" and ".implode(" and ",$tempwhr);			
			
			if(count($tempwhr)<=1){				
				if(!isset($master->limit)) $master->limit=50;					
			}
			if(!isset($master->srt)) $master->srt=$tempalias."createdon desc";
		}
		
		//if($master->sqlid=="solt1") {print_r($master->whr);echo "<br>";}
		if(!empty($master->having)){$master->having=GetStringFromJson($master,"having"," ");$mhaving=$master->having;}
		if(!empty($master->grp)){$master->grp=GetStringFromJson($master,"grp"," ");$mgrp=$master->grp;}
		if(!empty($master->srt)){$msrt=$master->srt;}
		if(!empty($master->limit)){$master->limit=GetStringFromJson($master,"limit"," ");$mlimit=$master->limit;}
		if(!empty($master->offset)){$master->offset=GetStringFromJson($master,"offset"," ");$moffset=$master->offset;}
	
		
		if(empty($res)){
			$res="{}";
			$res=json_decode($res);			
		}		
		
		if(!isset($master->stmt)){
			/*Fields*/	
			$fld="SELECT ";
			/*Function Fields to be added to fld above as prepend*/
			if(isset($master->functionfield)){
				$fld1=$this->GetFunctionField($master,false);
				if(!empty($fld1))
					$fld.=$fld1;				
			}
			
			/*main fields*/
			if(isset($master->fld)){
				if($fld=="SELECT ")
					$fld.=$master->fld;
				else
					$fld.=",".$master->fld;				
			}
			
			/*Function Fields to be added to fld above as append*/
			if(isset($master->functionfield)){
				$fld1=$this->GetFunctionField($master,true);
				if(!empty($fld1)){
					if($fld=="SELECT ")
						$fld.=$fld1;
					else
						$fld=$fld.",".$fld1;
				}				
			}
			
			/*Table*/
			$tbl="";
			if(isset($master->tbl)){				
				$tbl=(!empty($master->tbl))?"FROM ".$master->tbl:"";
			}else
				$master->tbl="";
			
						
			/*Where criteria*/
			
			$whr="";
			if(!empty($master->whr)){
				$master->whr=str_replace("_apos_","'",$master->whr);
				$whr="WHERE ".$master->whr;
			}
			
			//echo $master->whr;
			/*Group BY*/
			$grp="";
			if(isset($master->grp)){				
				$grp=(!empty($master->grp))?"GROUP BY ".$mgrp:"";
			}		

			/*having criteria*/
			$having="";
			if(isset($master->having)){				
				$having=(!empty($master->having))?"HAVING ".$mhaving:"";
			}				
		
			$srt=!empty($master->srt)?"ORDER BY ".$master->srt:"";
			$limit=!empty($master->limit)?"LIMIT ".$master->limit:"";
			$offset=!empty($master->offset)?"OFFSET ".$moffset:"";				
			if(isset($master->datefield)){
				$datefield=explode(",",$master->datefield);
				foreach($datefield as $dd){
					if(strpos($dd,".")){
						$d=explode(".",$dd);
						$d=$d[1];
					}else
						$d=$dd;
						
					$fld=str_replace($dd,"DATE_FORMAT($dd,'%d-%m-%Y')as $d",$fld);
				}
			}
			
			if(!isset($master->union))
				$str="$fld $tbl $mjoin $whr $grp $having $srt $limit $offset";
			else{
				if(stripos($munion,"UNION")!==false)
					$str="($fld $tbl $mjoin $whr $grp $having $srt $limit $offset)  ".$munion;
				else
					$str="($fld $tbl $mjoin $whr $grp $having $srt $limit $offset) UNION ALL ".$munion;
			}
		}else{
			$stmt=GetStringFromJson($master,"stmt"," ");
			$str=$stmt;
		}
		$str=$this->SupplyParamValue($str);
		
		return $str;
	}
	
	function GetAllTableNamesAndAlias($json){
		/*this function is will only support for Get Table*/
		if($json->action!="Get Table") return "";
		if(!isset($json->tbl)) return "";		
		$str=array();
		$tableNamesAndAlias=array();
		
		/*process join*/
		/*attempt to identify tablename and alias*/
		if(isset($json->tbl)){
			$str=GetStringFromJson($json,"tbl"," ");
			$str=explode(" ",$str);
			$str=array_filter($str);
			$tempstr=array();
			foreach($str as $k=>$v){				
				if(strtolower($v)=="as") continue;
				$tempstr[]=$v;
			}
			if(count($tempstr)==1) $tempstr[]="";
			$tableNamesAndAlias[]=$tempstr;
		}
		if(!empty($json->join)){			
			$str=GetStringFromJson($json,"join"," ");
			if(stripos($str,"join")){
				$str=trim(str_ireplace(array("inner ","left ","right ","cross ","  "," as ","join "),array("","","",""," "," ","JOIN "),$str));			
				$str=str_replace("  "," ",$str);
				$str=explode("JOIN ",$str);				
				$str=array_filter($str);			
				foreach($str as $ss){					
					$tempstr=array();
					$ss=explode(" ",$ss);
					$ss=array_filter($ss);
					foreach($ss as $s){					
						if(strtolower($s)=="on") break;
						$tempstr[]=$s;
					}
					if(count($tempstr)==1)$tempstr[]="";
					$tableNamesAndAlias[]=$tempstr;
				}
			}
		}
		return $tableNamesAndAlias;
		
	}	
	
	function GetTableNameFromTableAlias($json,$alias){		
		$tablename="";
		$tbl=isset($json->tbl)?$json->tbl:"";
		$join=isset($json->join)?$json->join:"";
		/*remove as */
		if(!empty($tbl)) $tbl=str_ireplace(" as "," ",$tbl);
		if(!empty($join)) $join=str_ireplace(" as "," ",$join);
		
		/*1. check first in tbl*/
		if(strpos($tbl," ")){
			$temptbl=explode(" ",$tbl);
			if(count($temptbl)==2 && $temptbl[1]==$alias) 
				$tablename=$temptbl[0];
		}
		
		/*2. if tablename is not found then search in join statement*/
		if(empty($tablename) && !empty($join)){			
			$tempjoin=str_ireplace(array("inner","left","right"),"",$join);
			$tempjoin=str_replace("  "," ",$tempjoin);
			$tempjoin=str_ireplace(" as "," ",$tempjoin);
			$tempjoin=str_replace(" join "," JOIN ",$tempjoin);
			$tempjoin=explode("JOIN",$tempjoin);			
			foreach($tempjoin as $jj){
				$jj=trim($jj);
				if(!empty($jj)){					
					if(strpos($jj," ")){						
						$jj=explode(" ",$jj);
						if(count($jj)>=2){
							if($jj[1]==$alias){
								$tablename=$jj[0];								
								break;
							}
						}
					}
				}
			}
		}
		return $tablename;
	}
	function GetTableHeader($sqlJson){
		if(!isset($sqlJson->header)) return "";
		if(is_array($sqlJson->header)){
			$newh=array();
			foreach($sqlJson->header as $h){
				if(is_string($h)){
					if(strpos($h,"<")!==false){
						$newh[]=$h;
					}else{
						$hh=explode(",",$h);
						foreach($hh as $hhh){
							$newh[]=$hhh;
						}
					}						
				}else if(is_array($h)){
					foreach($h as $hh){
						$newh[]=$hh;
					}
				}else if(is_object($h)){
					$tempjson=json_decode("{}");
					$tempjson->tempstr=$h;
					$tempstr=GetStringFromJson($tempjson,"tempstr",",");
					if(!empty($tempstr)) $newh[]=$tempstr;
				}
			}				
			$sqlJson->header=$newh;
		}else if(is_string($sqlJson->header)){
			$sqlJson->header=explode(",",$sqlJson->header);
		}		
		return $sqlJson->header;
	}
}
class EireneSql{
	private $TableName;
	private $TableAlias;
	public $TableNameAndAlias;
	private $FieldArray;
	private $Where;
	private $Having;
	private $OrderBy;
	private $GroupBy;
	private $Command;/*Insert,Update,Delete*/
	private $Info;/*JSON Object containing important info on the table*/
	private $ux;	
	function __construct(){
		$this->TableName="";
		$this->TableAlias="";
		$this->TableNameAndAlias=array();
		$this->FieldArray=array();
		$this->Where="";
		$this->WhereCriteriaCount=0;
		$this->Having="";
		$this->OrderBy="";
		$this->GroupBy="";
		$this->Command="";
		$this->Info=json_decode("{}");
		$this->ux=new EireneUx;		
	}
	function ProcessSqlJson(&$sqlJson){
		/*This function will process noted related to forming sql query*/
		
		/*0. STMT*/
		if(isset($sqlJson->stmt)){
			$sqlJson->stmt=GetStringFromJson($sqlJson,"stmt"," ");			
			return $sqlJson->stmt;
		}
		
		/*1. Table Names and Alias including joins and fielddef*/
		$this->GetTableNameAndAlias($sqlJson);
		
		/*1a. Gather Table Info*/		
		$this->GatherInfo($sqlJson);
		
		/*2. Table Name*/
		if(isset($sqlJson->tbl)) $sqlJson->tbl=trim(GetStringFromJson($sqlJson,"tbl"));					
		
		
		/*2. Fields*/
		if(isset($sqlJson->fld)) $sqlJson->fld=$this->GetField($sqlJson);					
			
		/*3 Join*/
		/*This is handled in GetTableNameAndAlias*/
		//if(isset($sqlJson->join)) 
		//	$sqlJson->join=GetStringFromJson($sqlJson,"join"," ");
		
		
		/*Where*/
		$sqlJson->whr=$this->GetWhere($sqlJson);
		
		/*GroupBy*/
		if(isset($sqlJson->grp)) $sqlJson->grp=GetStringFromJson($sqlJson,"grp"," ");
		
		/*Having*/
		if(isset($sqlJson->having)) $sqlJson->having=GetStringFromJson($sqlJson,"having"," ");
		
		/*OrderBy*/
		$sqlJson->srt=$this->GetSort($sqlJson);
		
		/*Limit*/
		if($this->WhereCriteriaCount<=1 && !isset($sqlJson->limit)){
			$sqlJson->limit="50";
		}else
			$sqlJson->limit=GetStringFromJson($sqlJson,"limit"," ");
		
		/*Offset*/
		if(isset($sqlJson->offset)) $sqlJson->offset=GetStringFromJson("$sqlJson","offset"," ");		
		
		/*Union*/
		if(isset($sqlJson->union)) $sqlJson->union=trim(GetStringFromJson($sqlJson,"union"," "));
		
		/*Table Header - for use in Get Table*/
		if(isset($sqlJson->header)) $sqlJson->header=$this->GetTableHeader($sqlJson);
		
		/*form sql query*/
		$fld=!empty($sqlJson->fld)?$sqlJson->fld:"";
		$tbl=!empty($sqlJson->tbl)?"FROM ".$sqlJson->tbl:"";
		$mjoin=!empty($sqlJson->join)?$sqlJson->join:"";
		$munion=!empty($sqlJson->union)?$sqlJson->union:"";
		$whr=!empty($sqlJson->whr)?"WHERE ".$sqlJson->whr:"";
		$grp=!empty($sqlJson->grp)?"GROUP BY ".$sqlJson->grp:"";
		$having=!empty($sqlJson->having)?"HAVING ".$sqlJson->having:"";
		$srt=!empty($sqlJson->srt)?"ORDER BY ".$sqlJson->srt:"";
		$limit=!empty($sqlJson->limit)?"LIMIT ".$sqlJson->limit:"";
		$offset=!empty($sqlJson->offset)?"OFFSET ".$sqlJson->offset:"";		
		if(empty($munion))
			$str="SELECT $fld $tbl $mjoin $whr $grp $having $srt $limit $offset";
		else{
			if(stripos($munion,"UNION")!==false)
				$str="(SELECT $fld $tbl $mjoin $whr $grp $having $srt $limit $offset)  ".$munion;
			else
				$str="(SELECT $fld $tbl $mjoin $whr $grp $having $srt $limit $offset) UNION ALL ".$munion;
		}
		return $str;
	}
	function GatherInfo(&$sqlJson){			
		/*Form Name*/
		$this->Info->formname="";
		if(isset($sqlJson->getform)){
			$this->Info->formname=$sqlJson->getform;						
		}else if(isset($sqlJson->formname)){
			$this->Info->formname=$sqlJson->formname;				
		}
		/*Tableid*/
		if($sqlJson->action=="Get Table")			
			if(!isset($sqlJson->tableid)) $sqlJson->tableid="Table".rand(1,9999);		
		$this->Info->tableid=isset($sqlJson->tableid)?$sqlJson->tableid:"";
		/*saveid*/
		$this->Info->saveid=isset($sqlJson->saveid)?$sqlJson->saveid:"";
		if(is_object($this->Info->saveid)){
			if(isset($this->Info->saveid->id)) $this->Info->saveid=$this->Info->saveid->id;
		}else if(is_array($this->Info->saveid)){
			if(isset($this->Info->saveid[0]->id)) $this->Info->saveid=$this->Info->saveid[0]->id;
		}
		$this->Info->saveid=explode("-",$this->Info->saveid)[0];
		/*getid*/
		$this->Info->getid=isset($sqlJson->getid)?$sqlJson->getid:"";
		/*delid*/
		$this->Info->delid="";
		if(isset($sqlJson->delid))
			$this->Info->delid=$sqlJson->delid;
		else if(isset($sqlJson->delpermanentid))
			$this->Info->delid=$sqlJson->delpermanentid;
		/*FieldDef,TableName,TableAlias*/		
		//$this->Info->FieldDef,TableName,TableAlias will be processed in GetTableNameAndAlias
	}
	function GetSql(&$master,$res="",$convertString=false){		
		$flddef="";
		
		/*if formname is indicated then fetch other attributes from the form or table_def*/
		if((isset($master->formname) || isset($master->getform))&& $master->action!="Get Form"&& $master->action!="Print Form"){			
			if($master->action=="Save Row"||$master->action=="Save"||$master->action=="Save Table"){				
				if(!isset($master->onsuccess)) $master->onsuccess="";
				if(!isset($master->onfailure)) $master->onfailure="";
				if(isset($master->onsavesuccess)) $master->onsuccess="cmd:dom,fun:showtoast;success;Saving Successful-".$master->onsavesuccess;
				if(isset($master->onsavefailure)) $master->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed-".$master->onsavefailure;
				if(stripos($master->onsuccess,"sav")===false) $master->onsuccess="cmd:dom,fun:showtoast;success;Saving Successful";
				if(stripos($master->onfailure,"sav")===false) $master->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed";					
				if(empty($master->command)) $master->command="insertorupdate";
				
				if(!empty($GLOBALS["value"]["var".$master->sqlid."_savemethodno"]) && isset($master->savemethod)){												
					$tempnum=$GLOBALS["value"]["var".$master->sqlid."_savemethodno"];
					$tempval=isset($master->savemethod[$tempnum])?$master->savemethod[$tempnum]:"";
					if(!empty($tempval))
						$master->whr=$tempval;
					else
						$master->whr="id='||ID||'";
				}else{
					if(empty($master->whr)) $master->whr="id='||ID||'";
				}
			}else if($master->action=="Delete Row"||$master->action=="Delete Row Permanently"){
				if(!isset($master->onsuccess)) $master->onsuccess="";
				if(!isset($master->onfailure)) $master->onfailure="";
				if(isset($master->ondeletesuccess)) $master->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-".$master->ondeletesuccess;
				if(isset($master->ondeletefailure)) $master->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed-".$master->ondeletefailure;
				if(stripos($master->onsuccess,"delet")===false) $master->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-cmd:dom,fun:hide;current";											
				if(stripos($master->onfailure,"delet")===false) $master->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed";
				if(empty($master->whr)) $master->whr="id='||ID||'";
			}else if($master->action=="Edit"){
				$master->outputto="html";
				$master->output="edit";
				$GLOBALS["value"]["formname"]=$master->formname;
				$frmdef=$this->GetFormDefinitionFromTable($master->formname,false);
				$newfld=array();
				$newfld[]="id";
				foreach($frmdef->fields as $f){
					if(in_string($master->fld,$f->name)){
						if(isset($f->alias))
							$newfld[]=$f->name." as ".$f->alias;
						else
							$newfld[]=$f->name;
					}
				}
				//$master->fld="id,".$master->fld;
				$master->fld=implode(",",$newfld);
				
				//$master->jsfunction="assignVariable Eirene.data.formname;".$master->formname.",showWindowInDialog ".$master->formname.",edit1";//
				$master->jsfunction="edit1";
				if(empty($master->whr)) $master->whr="id='||ID||'";				
			}else if($master->action=="Get Table" || $master->action=="Get Chart"){
				$flddef=$this->GetFieldDefinitionFromTable($master->tbl);
				if(!isset($master->tableid)) $master->tableid="Table".rand(1,9999);
				/*primary tablenamealias - the function of primarytablenamealis is to help editable field know which is the primary table and the edited field will be saved to this table*/
				if(isset($master->primarytable)) $master->primarytablenamealias=$master->primarytable;
				if(!isset($master->primarytablenamealias) && strpos(trim($master->tbl)," ")){						
					$master->primarytablenamealias=trim(str_replace(" as "," ",$master->tbl));
					if(strpos($master->primarytablenamealias," "))
						$master->primarytablenamealias=explode(" ",$master->primarytablenamealias)[1];						
				}else if(isset($master->primarytablenamealias)){
					$master->primarytablenamealias=trim($master->primarytablenamealias);
					if(strpos($master->primarytablenamealias," "))
						$master->primarytablenamealias=explode(" ",$master->primarytablenamealias)[1];						
				}
				
				/*table header*/
				$master->header=$this->GetTableHeader($master);
				
				/*processing editablefields*/
				if(isset($master->editablefields)){
					if(!is_string($master->editablefields)) goto OutsideEditableFieldsProcessing;
					/*editablefields must be a string - else the below function fails*/
					$master->editablefields=explode(",",$master->editablefields);
					/*convert fields into array of strings*/
					
					if(is_string($master->fld)){
						$master->fld=explode(",",$master->fld);							
					}else if(is_array($master->fld)){
						$tempfld=array();
						foreach($master->fld as $k=>$v){
							if(is_string($v)){
								$tempv=explode(",",$v);
								foreach($tempv as $vv)
									$tempfld[]=$vv;
							}else if(is_object($v))
								$tempfld[]=$v;
						}
						$master->fld=$tempfld;
					}
					
					/*convert editablefields as objects*/
					foreach($master->fld as $k=>$v){
						if(is_object($v) || is_array($v)) continue;	
						//echo $v."=".implode(",",$master->editablefields)."<br>";							
						if(in_array($v,$master->editablefields)){								
							$master->fld[$k]=json_decode("{}");
							$master->fld[$k]->field=json_decode("{}");
							$master->fld[$k]->field->name=$v;
							$master->fld[$k]->field->type="editable";							
							$master->fld[$k]->field->value='<IF('.$v.' is null,"",'.$v.')>';								
						}
					}										
				}
			}
		}
		
		OutsideEditableFieldsProcessing:
		/*Supply values to variables*/
		$mfld="";$mtbl="";$mjoin="";$munion="";$mwhr="";$mhaving="";$mgrp="";$msrt="";$mlimit="";$moffset="";
		
		if(!empty($master->output)){$master->output=GetStringFromJson($master,"output");}
		if(!empty($master->outputto)){$master->outputto=GetStringFromJson($master,"outputto");}
		if(!empty($master->fld)){			
			if(isset($master->includeeditdelete)){
				//if(!isset($master->sqlid)) {print_r($master);echo '<br>';echo '<br>';}
				if(isset($GLOBALS["value"]["includeeditdelete".$master->sqlid])) $master->includeeditdelete=true;
				if($master->includeeditdelete==true || $master->includeeditdelete=="true"){					
					if(is_array($master->fld)){
						//print_r($master->fld);echo "<br><br>";
						$newfield=json_decode("{}");
						$newfield->field=json_decode("{}");
						$newfield->field->type="editdelete";
						$master->fld[]=$newfield;
						//print_r($master->fld);echo "<br><br>";
					}else if(is_string($master->fld)){						
						$fld=array();
						$fld[]=$master->fld;
						$newfield=json_decode("{}");
						$newfield->field=json_decode("{}");
						$newfield->field->type="editdelete";
						$fld[]=$newfield;						
						$master->fld=$fld;
					}
				}				
			}
			//print_r($master->fld);echo "<br>";
			$master->fld=GetStringFromJson($master,"fld",",");$mfld=$master->fld;
			//print_r($master->fld);echo "<br><br>";
		}
		
		if(!empty($master->tbl)){$master->tbl=GetStringFromJson($master,"tbl",",");$mtbl=$master->tbl;}		
		if(!empty($master->join)){$master->join=GetStringFromJson($master,"join"," ");$mjoin=$master->join;}
		if(!empty($master->union)){$master->union=GetStringFromJson($master,"union"," ");$munion=$master->union;}
		//print_r($GLOBALS["value"]);echo "<br>";
		
		//if($master->sqlid=="solt1") {print_r($master->whr);echo "<br><br>";print_r($master);echo "<br><br>";}
		
		/*srt*/
		if(!empty($master->srt)){
			$master->srt=GetStringFromJson($master,"srt"," ");
			//print_r($master);
			if(!empty($flddef->fields)){				
				foreach($flddef->fields as $f){
					if(empty($f->alias)) $f->alias="";
					if(!empty($master->srt)){
						if(strpos($master->srt,$f->alias)!==false){							
							$master->srt=str_replace($f->alias,$f->name,$master->srt);							
						}
					}
				}
			}			
		}
		
		/*whr*/
		if(!empty($master->whr)){
			//if($master->sqlid=="solt1") print_r($GLOBALS["value"]);
			$master->whr=GetStringFromJson($master,"whr"," ");			
		}else if(empty($master->whr) && ($master->action=="Get Table" || $master->action=="Get Chart")){						
			$master->tbl=str_replace("  "," ",str_ireplace(" as "," ",$master->tbl));
			$temptbl=explode(" ",$master->tbl);
			$tempalias="";
			if(isset($temptbl[1])) $tempalias=trim($temptbl[1]).".";
						
			$tempwhr=array();
			if(!empty($master->defaultwhr)){
				$tempwhr[]=$master->defaultwhr;
			}else
				$tempwhr[]=$tempalias."recordstatus=1";	//print_r($GLOBALS["value"]);echo "<br>";print_r($_POST);			
			
			if(!empty($flddef->fields)){				
				foreach($flddef->fields as $f){
					if(empty($f->alias)) $f->alias="";					
					$tempfldnm=empty($f->alias)?$f->name:$f->alias;					
					if(!empty($GLOBALS["value"][$tempfldnm]) || (isset($GLOBALS["value"][$tempfldnm]) && $GLOBALS["value"][$tempfldnm]!="")){						
						if(strpos($f->type,"int")!==false){
							if(!isset($f->filterop))
								$tempwhr[]=$tempalias.$f->name."=".$GLOBALS["value"][$tempfldnm];
							else
								$tempwhr[]=$tempalias.$f->name." ".$f->filterop." ".$GLOBALS["value"][$tempfldnm];
						}else{							
							if(!isset($f->filterop))
								$tempwhr[]=$tempalias.$f->name."='".$GLOBALS["value"][$tempfldnm]."'";
							else{
								if($f->filterop=="like")
									$tempwhr[]=$tempalias.$f->name." like '%".$GLOBALS["value"][$tempfldnm]."%'";
								else
									$tempwhr[]=$tempalias.$f->name." ".$f->filterop." '".$GLOBALS["value"][$tempfldnm]."'";
							}
						}
					}else if(!empty($GLOBALS["value"][$tempfldnm."fromdate"]) && $f->type=="date"){
						$tempwhr[]=$tempalias.$f->name." >='".$GLOBALS["value"][$tempfldnm."fromdate"]."'";
						if(!empty($GLOBALS["value"][$tempfldnm."todate"]))
							$tempwhr[]=$tempalias.$f->name." <='".$GLOBALS["value"][$tempfldnm."todate"]."'";
					}else if(!empty($GLOBALS["value"][$tempfldnm."fromdate"]) && $f->type=="datetime"){
						$tempwhr[]="DATE(".$tempalias.$f->name.") >='".$GLOBALS["value"][$tempfldnm."fromdate"]."'";
						if(!empty($GLOBALS["value"][$tempfldnm."todate"]))
							$tempwhr[]="DATE(".$tempalias.$f->name.") <='".$GLOBALS["value"][$tempfldnm."todate"]."'";
					}
				}
			}
			

			/*add whr from viewrights*/
			if(empty($master->viewrights))
				$master->viewrights="{}";
			$rights=json_decode($master->viewrights);			
			$TableNamesAndAlias=$this->GetAllTableNamesAndAlias($master);			
			if(!empty($rights)){				
				foreach($rights as $r){					
					if(isset($r->tbl)){
						foreach($TableNamesAndAlias as $tn){
							/*$tn may contain two elements 0=tablename 1=alias. Some table names may not have alias*/							
							if($tn[0]==$r->tbl){
								if(!empty($tn[1])){
									$tempwhr[]=str_replace("TBLNM",$tn[1],$r->views);
								}else{
									$tempwhr[]=str_replace("TBLNM",$tn[0],$r->views);
								}
							}
						}						
					}
				}
			}
			if(empty($master->whr))
				$master->whr=implode(" and ",$tempwhr);
			else
				$master->whr.=" and ".implode(" and ",$tempwhr);			
			
			if(count($tempwhr)<=1){				
				if(!isset($master->limit)) $master->limit=50;					
			}
			if(!isset($master->srt)) $master->srt=$tempalias."createdon desc";
		}
		
		//if($master->sqlid=="solt1") {print_r($master->whr);echo "<br>";}
		if(!empty($master->having)){$master->having=GetStringFromJson($master,"having"," ");$mhaving=$master->having;}
		if(!empty($master->grp)){$master->grp=GetStringFromJson($master,"grp"," ");$mgrp=$master->grp;}
		if(!empty($master->srt)){$msrt=$master->srt;}
		if(!empty($master->limit)){$master->limit=GetStringFromJson($master,"limit"," ");$mlimit=$master->limit;}
		if(!empty($master->offset)){$master->offset=GetStringFromJson($master,"offset"," ");$moffset=$master->offset;}
	
		
		if(empty($res)){
			$res="{}";
			$res=json_decode($res);			
		}		
		
		if(!isset($master->stmt)){
			/*Fields*/	
			$fld="SELECT ";
			/*Function Fields to be added to fld above as prepend*/
			if(isset($master->functionfield)){
				$fld1=$this->GetFunctionField($master,false);
				if(!empty($fld1))
					$fld.=$fld1;				
			}
			
			/*main fields*/
			if(isset($master->fld)){
				if($fld=="SELECT ")
					$fld.=$master->fld;
				else
					$fld.=",".$master->fld;				
			}
			
			/*Function Fields to be added to fld above as append*/
			if(isset($master->functionfield)){
				$fld1=$this->GetFunctionField($master,true);
				if(!empty($fld1)){
					if($fld=="SELECT ")
						$fld.=$fld1;
					else
						$fld=$fld.",".$fld1;
				}				
			}
			
			/*Table*/
			$tbl="";
			if(isset($master->tbl)){				
				$tbl=(!empty($master->tbl))?"FROM ".$master->tbl:"";
			}else
				$master->tbl="";
			
						
			/*Where criteria*/
			
			$whr="";
			if(!empty($master->whr)){
				$master->whr=str_replace("_apos_","'",$master->whr);
				$whr="WHERE ".$master->whr;
			}
			
			//echo $master->whr;
			/*Group BY*/
			$grp="";
			if(isset($master->grp)){				
				$grp=(!empty($master->grp))?"GROUP BY ".$mgrp:"";
			}		

			/*having criteria*/
			$having="";
			if(isset($master->having)){				
				$having=(!empty($master->having))?"HAVING ".$mhaving:"";
			}				
		
			$srt=!empty($master->srt)?"ORDER BY ".$master->srt:"";
			$limit=!empty($master->limit)?"LIMIT ".$master->limit:"";
			$offset=!empty($master->offset)?"OFFSET ".$moffset:"";				
			if(isset($master->datefield)){
				$datefield=explode(",",$master->datefield);
				foreach($datefield as $dd){
					if(strpos($dd,".")){
						$d=explode(".",$dd);
						$d=$d[1];
					}else
						$d=$dd;
						
					$fld=str_replace($dd,"DATE_FORMAT($dd,'%d-%m-%Y')as $d",$fld);
				}
			}
			
			if(!isset($master->union))
				$str="$fld $tbl $mjoin $whr $grp $having $srt $limit $offset";
			else{
				if(stripos($munion,"UNION")!==false)
					$str="($fld $tbl $mjoin $whr $grp $having $srt $limit $offset)  ".$munion;
				else
					$str="($fld $tbl $mjoin $whr $grp $having $srt $limit $offset) UNION ALL ".$munion;
			}
		}else{
			$stmt=GetStringFromJson($master,"stmt"," ");
			$str=$stmt;
		}
		$str=$this->SupplyParamValue($str);
		
		return $str;
	}
	function GetField($sqlJson){
		$temp=array();$this->FieldArray=array();
		if(!isset($sqlJson->fld)) return "";
		/*Editable fields*/
		$edt=isset($sqlJson->editablefields)?explode(",",$sqlJson->editablefields):"";
		
		if(is_array($sqlJson->fld)){
			foreach($sqlJson->fld as $f){
				if(is_string($f)){
					$ff=explode(",",$f);
					foreach($ff as $fff){
						$temp[]=$fff;
					}
				}else if(is_object($f)){
					//if(isset($f->field))
					//	$temp[]=$f;
					if(isset($f->type))
						$tempstr=$this->GetField1($f,$sqlJson,$this->Info);
					else if(isset($f->if) && (isset($f->then) || isset($f->else))){
						$jj=json_decode("{}");
						$jj->temp=$f;
						$tempstr=GetStringFromJson($jj,"temp"," ");
					}
					if(!empty($tempstr)) $temp[]=$tempstr;
				}
			}
		}else if(is_string($sqlJson->fld)){			
			$ff=explode(",",$sqlJson->fld);
			foreach($ff as $fff){
				$temp[]=$fff;
			}
		}
		/*functionfield*/
		if(isset($sqlJson->functionfield)){
			if(is_array($sqlJson->functionfield)){
				foreach($sqlJson->functionfield as $f){
					//$j=json_decode("{}");
					//$j->field=$f;
					//$temp[]=$j;
					$tempstr=$this->GetField1($f,$sqlJson,$this->Info);
					if(!empty($tempstr)) $temp[]=$tempstr;
				}
			}
		}
		/*includeeditdelete*/
		if(isset($sqlJson->includeeditdelete) && $sqlJson->includeeditdelete){
			
			//$temp[]=json_decode('{"field":{"type":"editdelete"}}');
			$tempstr=$this->GetField1(json_decode('{"type":"editdelete"}'),$sqlJson,$this->Info);
			if(!empty($tempstr)) $temp[]=$tempstr;
		}
		
		foreach($temp as $t){			
			if(is_object($t)){
				$this->FieldArray[]=$t;
			}else if(is_string($t) && !empty($edt) && in_array($t,$edt)){				
				$f=json_decode("{}");				
				$f->name=$t;
				$f->type="editable";	
				$tempstr=$this->GetField1($f,$sqlJson,$this->Info);
				if(!empty($tempstr)) $this->FieldArray[]=$tempstr;
			}else{
				$this->FieldArray[]=$t;				
			}
		}		
		$sqlJson->temp=$this->FieldArray;
		
		$str=trim(GetStringFromJson($sqlJson,"temp",","));
		return $str;			
	}
	function GetField1($elem,$sqlJson,$info){		
		/*$elem is Column-Object {"name":"abcd","type":"text","value":"ijkl","onclick":"run"}*/
		$ux=new EireneUx;
		foreach($elem as $ik=>$iv){
			if(is_string($iv))
				$elem->$ik=$ux->SupplyParamValue($iv,$elem);
		}
		
		/*handle group*/
		$tempgrp=array();
		$str="";
		$tempres=array();
		if($elem->type=="editdelete"){
			$elem->type="group";
			$elem->grp=array();
			$elem->grp[]=json_decode('{"type":"edit"}');
			$elem->grp[]=json_decode('{"type":"delete"}');
			$elem->caption="EditDelete";
		}
		if($elem->type=="group"){
			foreach($elem->grp as $g){
				$str=$this->GetField2($g,$info);
				if(!empty($str)) $tempres[]=$str;
			}
			if(!empty($tempres)) $str="CONCAT(".implode(",",$tempres).")";
		}else{		
			$str=$this->GetField2($elem,$info);
		}
		if(isset($elem->caption)) $str.= " AS ".str_replace(" ","_",$elem->caption);
		return $str;
	}
	
	function GetField2($elem,$info){
		$if=false;$concat=false;
		if($elem->type=="edit" || $elem->type=="delete"|| $elem->type=="editable") $concat=true;
		/*Replace String to suit SQL Query*/
		foreach($elem as $k=>$ii){					
			if(strpos($ii,"<")!==false && strpos($ii,">")!==false) $concat=true;				
			$elem->$k=str_replace("'","&apos;",$ii);				
			$elem->$k=str_replace("&quot;",'"',$ii);				
						
			/*The below replacement is basically needed for SQL IF condition inside any attribute*/
			$elem->$k=str_replace("&apos&","'",$ii);			
		}
		
		if($elem->type=="edit"){
			$elem->type="span";
			if(!isset($elem->class)) $elem->class="icon mif-pencil no-print";
			if(!isset($elem->caption)) $elem->caption="Edit";
			$elem->title="Edit";
			if(!isset($elem->onclick)){				
				if(!empty($info->getid) && !empty($info->tableid)){
					$elem->onclick="Eirene.runStmt(&apos;".$info->getid."&apos;,{formname:&apos;".$info->tableid."&apos;,id:&apos;<".$info->TableAlias."id".">&apos;})";
				}else if(!empty($info->formname)){									
					$elem->onclick="showForm(&apos;".$info->formname."&apos;,&apos;<".$info->TableAlias."id".">&apos;)";					
				}
			}		
		}else if($elem->type=="delete"){			
			$elem->type="span";
			if(!isset($elem->class)) $elem->class="icon mif-cross no-print";
			$elem->title="Delete";
			$elem->caption="Remove";
			if(!isset($elem->onclick)){				
				if(!empty($info->delid)){											
					$elem->onclick="Eirene.currentelem=$(this).parent().parent();Eirene.runStmt2(&apos;".$info->delid."&apos;,&apos;ID&apos;,&apos;<".$info->TableAlias."id".">&apos;)";	
				}
			}		
		}else if($elem->type=="icon" || $elem->type=="label"|| $elem->type=="span"){
			if($elem->type=="icon"){
				if(empty($elem->class)) 
					$elem->class="icon";
				else
					$elem->class.=" icon";
			}
			$elem->type="span";
			
		}if($elem->type=="link"){
			$elem->type="link";
			if(!isset($elem->href)) $elem->href="#";			
		}if($elem->type=="googlelink" ){
			$elem->type="link";			
			if(isset($elem->href))
				$elem->href="http://drive.google.com/uc?export=view&id=".$elem->href;
			else
				$elem->href="#";			
		}else if($elem->type=="switch" || $elem->type=="checkbox"){
			if(($elem->type=="switch" || $elem->type=="checkbox") && isset($elem->value) && ($elem->value=="checked" || $elem->value==1)) $elem->value="checked";			
		}else if($elem->type=="editable"){			
			if(!empty($info->FieldDef->fields)){				
				foreach($info->FieldDef->fields as $f){					
					if($info->TableAlias.$f->name==$elem->name){
						$elem->name=(isset($f->alias))?$f->alias:$f->name;
						if(!empty($f->fieldtype)){
							$elem->type=$f->fieldtype;
						}else if(!empty($f->type)){
							$elem->type=$f->type;
						}else{
							$elem->type="text";
						}			//echo $f->name. "=".$elem->type."<br>";			
						if(isset($f->option)) $elem->option=$f->option;						
						$elem->value="&apos&,IF(".$info->TableAlias.$f->name." is null,".'""'.",".$info->TableAlias.$f->name."),&apos&";
						if(isset($elem->showDialogOnFocus)){
							/*saving or any other command from the element will be transferred to the dialog except for onfocus*/
							if(!isset($elem->caption)) $elem->caption="";
							$elem->onfocus="Eirene.currentelem=$(this);let a=$(this).clone();a.removeAttr(&apos;onfocus&apos;);a.css(&apos;height&apos;,&apos;350px&apos;);a.css(&apos;width&apos;,&apos;90%&apos;);a.attr(&apos;onchange&apos;,&apos;Eirene.currentelem.val($(this).val())&apos;);Eirene.temp=a[0].outerHTML;showDialog(&apos;".$elem->caption."&apos;,Eirene.temp,&apos;info&apos;)";
						}
						//print_r($elem);echo "<br>";
						break;
					}
				}
			}else{ 
				$elem->type="text";
			}			
			$act="";
			if(isset($info->saveid)){				
				if($elem->type!="checkbox" && $elem->type!="switch")
					$act="Eirene.runStmt(&apos;".$info->saveid."&apos;,{ID:&apos;&apos&,".$info->TableAlias."id,&apos&&apos;,".$elem->name.":$(this).val()})";				
				else{
					$act="let vl=$(this).prop(&apos;checked&apos;);if(vl){vl=$(this).attr(&apos;checkvalue&apos;);}else{vl=$(this).attr(&apos;uncheckvalue&apos;);} Eirene.runStmt(&apos;".$info->saveid."&apos;,{ID:&apos;&apos&,".$info->TableAlias."id,&apos&&apos;,".$elem->name.":vl})";
				}
			}			
			if($elem->type=="select"){
				$elem->onchange=$act;
			}else
				$elem->onblur=$act;
		}
		
		
		foreach($elem as $k=>&$i){
			$i=str_replace(array("<",">"),array("&apos&,",",&apos&"),$i);	
		}
		
		/*Get HTML*/
		$htmlCls=new EireneHTMLBuilder();
		$elemhtml=$htmlCls->GetHTMLElement($elem);
		$elemhtml=str_replace("'",'"',$elemhtml);
		$elemhtml=str_replace("&apos&","'",$elemhtml);
		
		if($concat) $elemhtml="CONCAT('".$elemhtml."')";				
		/*Apply IF condition*/
		if(isset($elem->if)){
			$elemhtml="IF(".$elem->if.",".$elemhtml.","."'')";
		}
		return $elemhtml;
	}
	
	function GetWhere($sqlJson){
		$str;
		$foundfieldlist=array();
		if(!empty($sqlJson->whr)){
			if($sqlJson->action!="Save Row From CSV")
				$str=GetStringFromJson($sqlJson,"whr"," ");
			else
				$str=GetStringFromJson($sqlJson,"whr"," ",false,false);
			return $str;
		}else if(empty($sqlJson->whr) && ($sqlJson->action=="Get Table" || $sqlJson->action=="Get Chart New")){			
			$temptbl=explode(" ",$sqlJson->tbl);
			$tempalias=$this->TableAlias;					
			
			if(!empty($sqlJson->defaultwhr)){
				$tempwhr[]=GetStringFromJson($sqlJson,"defaultwhr"," ");
			}else
				$tempwhr[]=$tempalias."recordstatus=1";	//print_r($GLOBALS["value"]);echo "<br>";print_r($_POST);			
			/*TableNameAndAlias[0] Array contains three elements 0=TableName 1=TableAlias 2=FieldDef*/
			//if(isset($this->TableNameAndAlias[0][2])) $flddef=$this->TableNameAndAlias[0][2];
			/*Build Where Clause from Main Table and Join tables*/
			foreach($this->TableNameAndAlias as $tk=>$t){				
				$this->GetWhere1($t,$tempwhr,$foundfieldlist);
			}
			/*Build Where Clause from viewrights*/
			if(empty($sqlJson->viewrights))
				$sqlJson->viewrights="{}";
			$rights=json_decode($sqlJson->viewrights);			
			if(!empty($rights)){				
				foreach($rights as $r){					
					if(isset($r->tbl)){
						foreach($this->TableNameAndAlias as $tn){
							/*$tn may contain two elements 0=tablename 1=alias. Some table names may not have alias*/							
							if($tn[0]==$r->tbl){
								if(!empty($tn[1])){
									$tempwhr[]=str_replace("TBLNM",$tn[1],$r->views);
								}else{
									$tempwhr[]=str_replace("TBLNM",$tn[0],$r->views);
								}
							}
						}						
					}
				}
			}
			
			$this->WhereCriteriaCount=count($tempwhr);			
			$str=implode(" and ",$tempwhr);			
			return $str;
		}
	}
	
	function GetWhere1($tabledetails,&$tempwhr,&$foundfieldlist){
		/*$tabledetails is an array with following components: 0= tablename 1= tablealias 2 = fielddef*/
		if(empty($tabledetails[2]->fields)) return false;
		$tempalias=$tabledetails[1];
		foreach($tabledetails[2]->fields as $f){
			if(empty($f->alias)) $f->alias="";					
			$tempfldnm=empty($f->alias)?$f->name:$f->alias;
			if(!empty($GLOBALS["value"][$tempfldnm]) || (isset($GLOBALS["value"][$tempfldnm]) && $GLOBALS["value"][$tempfldnm]!="")){						
				$fieldfound=false;
				$foundfieldlist[]=$f->name;				
				if(in_array($tempfldnm,$foundfieldlist)) continue;							
				if(strpos($f->type,"int")!==false){
					if(!isset($f->filterop))
						$tempwhr[]=$tempalias.$f->name."=".$GLOBALS["value"][$tempfldnm];
					else
						$tempwhr[]=$tempalias.$f->name." ".$f->filterop." ".$GLOBALS["value"][$tempfldnm];
				}else{							
					if(!isset($f->filterop))
						$tempwhr[]=$tempalias.$f->name."='".$GLOBALS["value"][$tempfldnm]."'";
					else{
						if($f->filterop=="like")
							$tempwhr[]=$tempalias.$f->name." like '%".$GLOBALS["value"][$tempfldnm]."%'";
						else
							$tempwhr[]=$tempalias.$f->name." ".$f->filterop." '".$GLOBALS["value"][$tempfldnm]."'";
					}
				}
			}else if(!empty($GLOBALS["value"][$tempfldnm."fromdate"]) && ($f->type=="date" || $f->type=="datetime")){				
				if(in_array($f->name,$foundfieldlist)) continue;
				$foundfieldlist[]=$f->name;					
				$tempwhr[]=$tempalias.$f->name." >='".$GLOBALS["value"][$tempfldnm."fromdate"]."'";
				if(!empty($GLOBALS["value"][$tempfldnm."todate"]))
					$tempwhr[]=$tempalias.$f->name." <='".$GLOBALS["value"][$tempfldnm."todate"]."'";
			}
		}		
	}
	function GetJoin(&$sqlJson,$joindef){
		if(isset($sqlJson->usejoin)){
			$tempjoin="";
			
			/*usejoin syntax= usejoin:"cmch_patient a,cmch_doctor b"*/
			/*joindef has the syntax joindef:{"tablename":"on statement"}. For eg {"cmch_patient":"cmch_appointment.pid=cmch_patient.pid"}*/
			$declaredusejoin=explode(",",$sqlJson->usejoin);
			$tempstoretblalias=array();
			foreach($declaredusejoin as $duj){
				/*duj contains join table name and alias*/				
				$duj=str_replace("  "," ",trim($duj));
				$leftjoin=false;
				$rightjoin=false;
				$innerjoin=true;
				if(stripos($duj,"left join")!==false){
					$leftjoin=true;$innerjoin=false;$rightjoin=false;
					$duj=str_ireplace("LEFT JOIN","",$duj);
					$duj=trim(str_ireplace("  "," ",$duj));
				}else if(stripos($duj,"right join")!==false){
					$rightjoin=true;$innerjoin=false;$leftjoin=false;
					$duj=str_ireplace("RIGHT JOIN","",$duj);
					$duj=trim(str_ireplace("  "," ",$duj));
				}
				$tempaliasarray=explode(" ",$duj);
				$tempalias1="";
				if(count($tempaliasarray)>1){
					$tempalias=$tempaliasarray[1];
					$tempalias1=$tempalias;
				}else 
					$tempalias=$tempaliasarray[0];
				$tempon="";
				foreach($joindef as $jk=>$jd){
					if($jk==$tempaliasarray[0]){
						$tempon=$jd;break;
					}
				}
				$tempon=str_ireplace(array($tempaliasarray[0].".",$this->TableName."."),array($tempalias.".",$this->TableAlias),$tempon);
				if($innerjoin) $tempjoin.=" INNER JOIN";
				else if($leftjoin) $tempjoin.=" LEFT JOIN";
				else if($rightjoin) $tempjoin.=" RIGHT JOIN";
				$tempjoin.=" $tempaliasarray[0] $tempalias1 ON $tempon";
			}
			if(!empty($tempjoin)){
				foreach($declaredusejoin as $duj){
					$duj=str_replace("  "," ",trim($duj));
					$duj=str_ireplace("LEFT JOIN","",$duj);
					$duj=str_ireplace("RIGHT JOIN","",$duj);
					$duj=trim(str_ireplace("  "," ",$duj));
					$tempaliasarray=explode(" ",$duj);
					$temptbl=$tempaliasarray[0];
					$tempalias=isset($tempaliasarray[1])?$tempaliasarray[1]:$tempaliasarray[0];
					$tempjoin=str_replace($temptbl.".",$tempalias.".",$tempjoin);
				}
				$sqlJson->join=$tempjoin;
			}
		}
	}
	function GetSort($sqlJson){
		$str="";
		if(!empty($sqlJson->srt)){
			$sqlJson->srt=GetStringFromJson($sqlJson,"srt"," ");
			if(isset($this->TableNameAndAlias[0][2])) $flddef=$this->TableNameAndAlias[0][2];
			if(!empty($flddef->fields)){				
				foreach($flddef->fields as $f){					
					if(empty($f->alias)) $f->alias="";
					if(!empty($sqlJson->srt)){
						if(strpos($sqlJson->srt,$f->alias)!==false){							
							$sqlJson->srt=str_replace($f->alias,$f->name,$sqlJson->srt);							
						}
					}
				}
			}
			$str=$sqlJson->srt;			
		}else{
			$sqlJson->srt=$this->TableAlias."createdon desc";
		}
		return $str;
	}
	function GetTableHeader($sqlJson){
		if(!isset($sqlJson->header)) return "";
		if(is_array($sqlJson->header)){
			$newh=array();
			foreach($sqlJson->header as $h){
				if(is_string($h)){
					if(strpos($h,"<")!==false){
						$newh[]=$h;
					}else{
						$hh=explode(",",$h);
						foreach($hh as $hhh){
							$newh[]=$hhh;
						}
					}						
				}else if(is_array($h)){
					foreach($h as $hh){
						$newh[]=$hh;
					}
				}else if(is_object($h)){
					$tempjson=json_decode("{}");
					$tempjson->tempstr=$h;
					$tempstr=GetStringFromJson($tempjson,"tempstr",",");
					if(!empty($tempstr)) $newh[]=$tempstr;
				}
			}				
			$sqlJson->header=$newh;
		}else if(is_string($sqlJson->header)){
			$sqlJson->header=explode(",",$sqlJson->header);
		}
		if(count($sqlJson->header)!=count($this->FieldArray)){
			if(isset($sqlJson->functionfield)){
				foreach($sqlJson->functionfield as $f){
					if(isset($f->caption))
						$sqlJson->header[]=$f->caption;
					else
						$sqlJson->header[]="";
				}
			}
		}
		return $sqlJson->header;
	}
	function GetTableNameAndAlias(&$sqlJson){
		/*this return $this->TableNameAndAlias which is an array of array. the array items are 0=tablename,1=tablealias,2=tabledef,3=joindef*/		
		if(!isset($sqlJson->tbl) && !isset($sqlJson->join)) return false;
		$tablename="";
		$temp=array();
		if(isset($sqlJson->tbl)){
			$tbl=GetStringFromJson($sqlJson,"tbl");
		}else
			$tbl="";
		
		
		/*1. check first in tbl*/
		$joindef="";
		if(!empty($tbl)){
			$temptbl=explode(" ",trim($tbl));			
			$this->TableName=$temptbl[0];
			$this->TableAlias=isset($temptbl[1])?$temptbl[1].".":"";
			$this->Info->TableName=$this->TableName;
			$this->Info->TableAlias=$this->TableAlias;
			if(isset($temptbl[1])) $sqlJson->primarytablenamealias=$temptbl[1];
			$meta="";$joindef="";
			$flddef=$this->ux->GetFieldDefinitionFromTable($this->TableName,$meta,$joindef);
			$this->Info->FieldDef=$flddef;
			if(empty($this->Info->saveid) && !empty($meta->saveid)){
				$this->Info->saveid=$meta->saveid;
				if(is_object($this->Info->saveid)){
					if(isset($this->Info->saveid->id)) $this->Info->saveid=$this->Info->saveid->id;
				}else if(is_array($this->Info->saveid)){
					if(isset($this->Info->saveid[0]->id)) $this->Info->saveid=$this->Info->saveid[0]->id;
				}
				$this->Info->saveid=explode("-",$this->Info->saveid)[0];
			}
			if(empty($this->Info->getid) && !empty($meta->getid)) $this->Info->getid=$meta->getid; 
			if(empty($this->Info->delid) && !empty($meta->delid)) $this->Info->delid=$meta->delid; 
			if(empty($this->Info->delid) && !empty($meta->delpermanentid)) $this->Info->delid=$meta->delpermanentid; 
			$temp[]=array($this->TableName,$this->TableAlias,$flddef,$joindef);
			if(isset($flddef->fields)){
				foreach($flddef->fields as $f){
					$tempnm=$f->name;
					if(isset($GLOBALS["value"][$f->alias])){						
						if(!isset($sqlJson->addedvariables[$tempnm]))
							$sqlJson->addedvariables[$tempnm]=$GLOBALS["value"][$f->alias];
					}else if(isset($GLOBALS["value"][$f->name])){						
						if(!isset($sqlJson->addedvariables[$tempnm]))
							$sqlJson->addedvariables[$tempnm]=$GLOBALS["value"][$f->name];
					}
				}
			}
		}
		
		/*2. Get Join*/
		$this->GetJoin($sqlJson,$joindef);		
		if(isset($sqlJson->join)){
			$sqlJson->join=GetStringFromJson($sqlJson,"join"," ");
		}
		$join=isset($sqlJson->join)?$sqlJson->join:"";
		if(empty($tbl) && empty($join)) return false;
		/*remove as */
		if(!empty($tbl)) $tbl=str_ireplace(" as "," ",$tbl);
		if(!empty($join)) $join=str_ireplace(" as "," ",$join);
		
		/*3. search in join statement*/		
		if(!empty($join)){
			if(empty($sqlJson->usejoin)){
				$tempjoin=str_ireplace(array("inner","left","right"),"",$join);
				$tempjoin=str_replace("  "," ",$tempjoin);
				$tempjoin=str_ireplace(" as "," ",$tempjoin);
				$tempjoin=str_replace(" join "," JOIN ",$tempjoin);
				$tempjoin=explode("JOIN",$tempjoin);
			}else{
				$tempjoin=explode(",",$sqlJson->usejoin);
			}
			foreach($tempjoin as $jj){
				$jj=trim($jj);
				if(empty($jj)) continue;
				$jj=str_replace("  "," ",$jj);
				$jj=str_replace(" on "," ON ",$jj);
				if(strpos($jj," ")){						
					$jj=explode(" ON ",$jj)[0];
					$tempjn=explode(" ",$jj);
					
					//$tempjn=explode(" ",$jn);
					$joindef="";$meta="";
					$flddef=$this->ux->GetFieldDefinitionFromTable($tempjn[0],$meta,$joindef);							
					$tempalias=isset($tempjn[1])?$tempjn[1].".":"";							
					
					$temp[]=array($tempjn[0],$tempalias,$flddef,$joindef);
					if(isset($flddef->fields)){
						foreach($flddef->fields as $f){
							if(isset($GLOBALS["value"][$f->alias])){
								$tempnm=$f->name;
								if(!isset($sqlJson->addedvariables[$tempnm]))
									$sqlJson->addedvariables[$tempnm]=$GLOBALS["value"][$f->alias];
							}
						}
					}					
				}				
			}
		}
		$this->TableNameAndAlias=$temp;
		return $tablename;
	}
	
}
class EireneProcessObject{
	private $ux;
	private $api;
	function __construct(){
		$this->ux=new EireneUx();
		$this->api=new EireneApi();
	}
	function ProcessObject(&$sqlJson,$nodename,$seperator="",$convertSpecialCharToHTMLCode=false,$supplyValueToVariable=true){
		/*initial check*/
		$str="";
		if(empty($sqlJson)) return "";
		if(is_string($sqlJson) && strpos($sqlJson,"||")===false) return $sqlJson;
		if(is_object($sqlJson) && !isset($sqlJson->$nodename)) return "";
					
		if(is_string($sqlJson->$nodename) ||is_numeric($sqlJson->$nodename)){			
			$str=$sqlJson->$nodename;
		}else if(is_array($sqlJson->$nodename) || is_object($sqlJson->$nodename)){
			$str1="";
			if(is_object($sqlJson->$nodename)) $sqlJson->$nodename=array($sqlJson->$nodename);
			foreach($sqlJson->$nodename as $elem){
				if(is_string($elem)) 
					$str1.=(empty($str1))?$elem:$seperator.$elem;
				else if(is_object($elem)){
					if(isset($sqlJson->sqlid)) $elem->sqlid=$sqlJson->sqlid;
					$tempstr=$this->ProcessObject1($elem,$sqlJson);
					if(!empty($tempstr))
						$str1.=(empty($str1))?$tempstr:$seperator.$tempstr;
				}				
			}		
			$str=$str1;
		}		
		if($supplyValueToVariable==true){		
			$str=$this->ux->SupplyParamValue($str,$sqlJson,$convertSpecialCharToHTMLCode);				
		}
		return $str;
	}	
	function ProcessObject1($elem,$sqlJson){
		$elemoriginal=$elem;
		$this->AddVariables($sqlJson,$elem);
		$str="";
		if(isset($elem->if)){			
			$str=$this->ProcessIf($elem);				
		}else if(isset($elem->case)){			
			$str=$this->ProcessCase($elem);	
		}else if(isset($elem->elem)){		
			/*elem objects/arrays here will always add the htmloutput to the main string*/
			$str=$this->ProcessHTML($elem,$sqlJson);
		}else if(isset($elem->field)){			
			$str=$this->ProcessField($elem,$sqlJson);
		}else if(isset($elem->phpfunction)){
			/*{“phpfunction”:{“fun”:”Functionname”,”param1”:”argument1”,”param2”:”argument2”} Upto 6 argument can be specified.*/
			/*This function will not return anything.*/
			/*phpfunction object - in case of phpfunction no output will be generated to the main string. Instead output will be generated to the variablename defined inside of object under node output*/							
			$api->RunPHPFunction($elem);							
		}else if(isset($elem->localvalue)){
			/*This function will not return anything.*/
			/*This function will modify $sqlJson with the properties as specified in the locavalue*/
			/*syntax is "localvalue":{"var1":"value1","var2":"value2" and so on}*/
			$this->ProcessSetLocalGlobalValue($elem,$sqlJson,"localvalue");
		}else if(isset($elem->globalvalue)){
			/*This function will not return anything.*/
			/*Set Global Value if any "globalvalue":{"var1":"b","var2":"||c||","var3":{"if"}}*/
			$this->ProcessSetLocalGlobalValue($elem,$sqlJson,"globalvalue");			
		}else if(isset($elem->sqlstatement)){
			$this->ProcessSqlStatement($elem,$sqlJson);
		}else if(isset($elem->call)){
			/*no output will be generated. Output will only be generated in the variable specified in the sql definition related to the sqlid*/
			/*format: {"call":"id"} optionals {"call":"id","outputto":"php","output":"res"}*/
			$this->ProcessCall($elemoriginal,$sqlJson);			
		}else if(isset($elem->output)){
			/*syntax "output":{"output":"res","outputto":"html","value":"abcd","append":false}*/
			$this->ProcessOutput($elem);
		}else if(isset($elem->run) ){
			/*synatx is same as sql-object containing action*/
			$this->ProcessRunCommand($elem);
		}else if(isset($elem->evaluate)){
			$this->ProcessEvaluate($elem);
		}else if(isset($elem->cookie)){
			$this->ProcessCookie($elem);
		}
		return $str;
	}
	
	function AddVariables($sourcejson,&$newjson){
		foreach($sourcejson as $k=>$v){
			if(in_string('output,outputto,action,call,sql,merge,required,alias,tbl,vsum,hsum,fld,whr,srt,join,having,onsuccess,onfailure,formname,getform,validate,view_rights,tableid,primarytablenamealias,if,and,or,value,value1,append,prepend',$k)) continue;
			if(!is_string($v) && !is_numeric($v)) continue;
			if(isset($newjson->$k)) continue;
			$newjson->$k=$v;
		}
		if(isset($sourcejson->addedvariables))
			$newjson->addedvariables=$sourcejson->addedvariables;
	}

	function ConvertPoorJsonStringToJson($str){
		$v=str_replace(":",'":"',$str);
		$v=str_replace("{",'{"',$v);
		$v=str_replace("}",'"}',$v);				
		$newstr=explode(":",$v);
		$len=count($newstr);
		for($i=1;$i<$len-1;$i++){
			$ns=strrev($newstr[$i]);
			$ns=preg_replace('/,/','","',$ns,1);
			$newstr[$i]=strrev($ns);
		}
		$v=implode(":",$newstr);
		
		$v=json_decode($v);
		return $v;
	}
	function ProcessIf($elem){
		$api=$this->api;
		$str="";
		if($api->CheckIfCondition($elem,$GLOBALS["value"],$GLOBALS["result"])){					
			if(isset($elem->then)){
				if(is_string($elem->then) || is_numeric($elem->then)){					
					$str=GetStringFromJson($elem,"then"," ");
				}else{					
					GetStringFromJson($elem,"then"," ");
				}					
			}
		}else{
			if(isset($elem->else)){
				if(is_string($elem->else) || is_numeric($elem->else)){					
					$str=GetStringFromJson($elem,"else"," ");
				}else{
					GetStringFromJson($elem,"else"," ");
				}
			}
		}
		return $str;
	}
	function ProcessCase($elem){
		$elemcase=$ux->SupplyParamValue($elem->case,$elem);
		$str="";
		if(isset($elem->$elemcase)){
			$elemval=$elem->$elemcase;
			$j=json_decode("{}");
			$j->val=$elem->$elemcase;
			$elemval=GetStringFromJson($j,"val");			
			$str=$elemval;
		}
		return $str;
	}
	function ProcessSetLocalGlobalValue($elem,&$sqlJson,$localglobalvalue){
		if(!is_object($elem->$localglobalvalue)) return false;		
		foreach($elem->$localglobalvalue as $k=>$v){
			$v=GetStringFromJson($elem->$localglobalvalue,$k,"",false,false);
			$v=$this->ux->SupplyParamValue($v,$sqlJson);
			if(substr($v,0,1)==chr(123) && substr($v,-1)==chr(125)){
				/*below method will convert pooerly written json without quotes to proper json with quotes*/
				$v=$this->ConvertPoorJsonStringToJson($v);
				if(!empty($v)){
					foreach($v as $vk=>$vv){
						if($localglobalvalue=="localvalue")
							$sqlJson->$vk=$vv;
						else if($localglobalvalue=="globalvalue")
							$GLOBALS["value"][$vk]=$vv;
					}
				}					
			}else{
				if($localglobalvalue=="localvalue")
					$sqlJson->$k=$v;
				else if($localglobalvalue=="globalvalue")
					$GLOBALS["value"][$k]=$v;
			}
		}		
	}
	function ProcessCall($elem,$sqlJson){
		$sqlJson1=json_decode("{}");
		$callsqlid=$elem->call;
		$sqlJson1->sqlid=$callsqlid;
		$this->ux->FetchSql($sqlJson1,$elem->call);		
		/*override outputto and output and other vaiables if specified with call statement*/
		foreach($elem as $k=>$v){
			if($k!="call" && $k!="sqlid" && $k!="append" && $k!="prepend"){
				$v=GetStringFromJson($elem,$k);
				$v=GetStringFromJson($elem,$k);/*second time is needed in case a variable is supplied*/
				$sqlJson1->$k=$v;				
			}			
		}
		$this->AddVariables($sqlJson,$sqlJson1);
		$sqlJson1->sqlid=$callsqlid;		
		$cmd=new EireneCommandHandler();
		$cmd->ProcessRequest($sqlJson1);
	}
	function ProcessOutput($elem){		
		if(isset($elem->sqlid)) $elem->output->sqlid=$elem->sqlid;
		if(empty($elem->output->value)) $elem->output->value="";
		if(empty($elem->output->output)) $elem->output->output="";
		$elem->output->value=GetStringFromJson($elem->output,"value");
		$elem->output->output=GetStringFromJson($elem->output,"output");
		if(!isset($elem->output->action)) $elem->output->action="";
		if(!isset($elem->output->defaultoutput)) $elem->output->defaultoutput="";
		if(!isset($elem->output->append)) $elem->output->append=false;
		if($elem->output->action=="dom") $elem->output->append=true;
		setOutput($elem->output->defaultoutput,$GLOBALS["result"],$elem->output->value,$elem->output,$elem->output->append);		
	}
	function ProcessCookie($elem){
		if(is_string($elem->cookie)) return false;
		if(empty($elem->cookie)) return false;		
		if(is_object($elem->cookie)) $elem->cookie=array($elem->cookie);
				
		foreach($elem->cookie as $ck){	
			if(empty($ck->name) || empty($ck->value)) continue;
			$time=0;
			$timeNum=0;
			$timeInt="";
			$ck->value=GetStringFromJson($ck,"value");			
			if(isset($ck->time)){
				$ck->time=str_replace("  "," ",$ck->time);
				$timeNum1=explode(" ",$ck->time);
				if(is_numeric($timeNum1[0])){
					$timeNum=$timeNum1[0];
					$timeInt=$timeNum1[1];
				}
			} 
			if($timeInt=="day" || $timeInt=="days"){
				$time=time() + (86400 * $timeNum);
			}else if($timeInt=="week" || $timeInt=="weeks"){
				$time=time() + (86400 * $timeNum * 7);
			}else if($timeInt=="month" || $timeInt=="months"){
				$time=time() + (86400  * $timeNum * 30);
			}else if($timeInt=="year" || $timeInt=="years"){
				$time=time() + (86400  * $timeNum * 365);
			}
			setcookie($ck->name,$ck->value,$time);
		}
		
	}
	
	function ProcessSqlStatement($elem,$sqlJson){
		$this->api->RunSqlStatement($elem,$sqlJson);
	}
	function ProcessHTML($elem,$sqlJson){
		$htmlbuilder=new EireneHTMLBuilder();
		if(isset($sqlJson->sqlid)) $elem->sqlid=$sqlJson->sqlid;
		$str=$htmlbuilder->GetHTML($elem);
		return $str;
	}
	function ProcessField($elem,$sqlJson){
		/*output will be generated*/
		/*format {"field":{all elements of fields can be in this object}}*/
		$json=json_decode("{}");
		if(isset($sqlJson->getform)) $json->getform=$sqlJson->getform;
		if(isset($sqlJson->formname)) $json->formname=$sqlJson->formname;
		if(isset($sqlJson->tbl)) $json->tbl=$sqlJson->tbl;
		if(isset($elem->field->prepend)) unset($elem->field->prepend);
		$json->functionfield[]=$elem->field;
		$json->header="";
		foreach($sqlJson as $k=>$v){
			if(is_string($v) && !isset($json->$k)) $json->$k=$v;
		}	
		$esql=new EireneSql();
		$str=$esql->GetField($json);
		return $str;
	}
	function ProcessRunCommand($elem){
		if(is_string($elem->run)) return false;
		if(is_object($elem->run)) $elem->run=array($elem->run);
		$apifun=new EireneCommandHandler();
		$apifun->ExecuteCommand($elem->run);
	}
	function ProcessEvaluate($elem){
		$elem->evaluate=GetStringFromJson($elem,"evaluate");
		eval($elem->evaluate);
	}
}
class EireneInstaller{
	public $error;
	public $successmsg;
	private $commandstatement;
	private $tablecreatedcount;
	
	function __construct(){
		$this->error=array();
		$this->successmsg=array();
		$this->commandstatement=array();
		$this->tableCreatedcount=0;
	}
	function InstallPlugin($pluginid,$plugin,$userid,$firstinstall=false){
		/*1. delete all sql statement for the pluginid*/		
		if(!empty($pluginid)){		  
			$delsql="DELETE FROM eirene_sqlstatements WHERE pluginid='$pluginid'";
			$GLOBALS["db"]->Execute($delsql);
		}

		/*2. initialize*/
		$api=new EireneApi();	
		$file=null;
		$plugin1=preg_replace("(/  |\r\n|\n|\r|\t/gm)","",$plugin);
		$plugin1=json_decode($plugin1);
		if(empty($plugin1)) return "Either bad json supplied or the plugin file is empty.";	
		$tableCnt=0;
		$str="";		
		
		/*2a. Create Alias for Table Fields*/
		$this->CreateAliasForTableFields($plugin1->table_def);
		
		/*2b. Create sql table/s as per definition if table does not exists*/	
		$this->CreateTable($plugin1);			
		
		/*2c. Import table def if defined*/
		$this->ImportTableDef($plugin1);
		 
		//3. Get Userid on first install
		if($firstinstall){
			$sql="Insert into eirene_users (id,username,fullname,pass,profileid,status) VALUES (UUID(),'".$GLOBALS["value"]["usrname"]."','Administrator','".$GLOBALS["value"]["passkey"]."','',2)";		
			$GLOBALS["db"]->execute($sql);
			/*Get Userid*/
			$userid=$GLOBALS["db"]->GetValue("SELECT id from eirene_users limit 1");
			$api->SetUserInfo($userid);
		}		
		  
		/*4. Create record in plugin*/
		$pluginidcopy=$pluginid;
		$res=$this->CreatePluginRecord($plugin1,$pluginid,$userid);
		if($res && empty($pluginidcopy))
			$this->successmsg[]="Plugin created successfully.";
		else if($res && !empty($pluginidcopy) )
			$this->successmsg[]="Plugin updated successfully.";
		else if(!$res)
			$this->successmsg[]="Plugin creation/update failed.";
		
		/*5. insert sqlid for save, edit, and delete for each form*/
		//if(is_string($plugin1->form)) $plugin1->form=json_decode($plugin1->form);
		if(isset($plugin1->form)) $this->InsertSaveEditDeleteSqlStatements($plugin1->form,$pluginid);
		//if(is_string($table_def)) $table_def=json_decode($table_def);
		
		/*5a. insert Save Edit Delete command*/
		$this->InsertSaveEditDeleteSqlStatements($plugin1->table_def,$pluginid);
		/*5b. insert Get Table command*/
		$this->InsertGetTableSqlStatements($plugin1->table_def,$pluginid);
		/*5c. Insert LookUp Field Commands*/
		$this->InsertLookUpFieldCommands($plugin1->table_def,$pluginid);
		/*5d. Insert Table entries*/		
		$this->InsertTableEntries($plugin1->table_def,$pluginid);				   				
		/*5e. insert/update sql statements*/
		$this->InsertCommands($plugin1,$pluginid,$userid);		  
		
		//$str.=" ". $sqlstmt ." sql statement created/updated".$sql1;
		$str=implode("<br>",$this->successmsg)."<br>";
		if(!empty($this->error)) $str.="<b>Errors</b>:<br>".implode("<br>",$this->error);
		return $str;		
	}
	public function CreateTable(&$jsonObj){		
		if(!is_object($jsonObj))
			$jsonObj=json_decode($jsonObj);	
		$tabledef="";
		$tableCreatedcnt=0;
		$columnUpdatedcnt=0;
		$columnAddedcnt=0;
		$error=array();
		$sql="";
		$sql1="";
		
		if(isset($jsonObj->table_def)){						
			foreach($jsonObj->table_def as &$obj){
				$sql="";
				$sql1="";
				if(isset($obj->importtabledef)) continue;
				
				if(!$this->DoesTableExist($obj->name)){
					$this->CreateTable1($obj);
				}else{
					/*this means that table exists*/
					$existingcolumns=$this->GetTableColumns($obj->name);
					$this->FindAndAddNewColumns($obj->name,$existingcolumns,$obj->fields);
					$this->FindAndUpdateNewColumns($obj->name,$existingcolumns,$obj->fields);													
				}				
			}
			if($tableCreatedcnt>0) $this->successmsg[]= "$tableCreatedcnt table/s created.<br>";
			if($columnAddedcnt>0) $this->successmsg[]="$columnAddedcnt column added.<br>";
			if($columnUpdatedcnt>0) $this->successmsg[]="$columnUpdatedcnt column updated.<br>";					
		}
	}
	function DoesTableExist($tablename){
		$sql="SHOW TABLES LIKE '$tablename'";
		$res=$GLOBALS["db"]->HasRows($sql);
		return $res;
	}
	function CreateTable1($obj){
		$sql="CREATE TABLE IF NOT EXISTS ". $obj->name ."(";
		$sql.="id char(36) ";
		if(stripos(json_encode($obj->fields),"auto_increment")===false)
			$sql.="PRIMARY KEY,";
		else
			$sql.=",";
		foreach($obj->fields as $f){			
			$sql.=$f->name." ".$f->type." ".$f->other.",";									
		}
		$sql.="createdby char(36) not null,createdon TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,modifiedby char(36),modifiedon TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,recordstatus tinyint not null default 1";
		$sql.=")";		   
		$res=$GLOBALS["db"]->Execute($sql);			
		if(trim($GLOBALS["db"]->error)!=""){
			$this->error[]=$obj->name." Table creation error: ".$GLOBALS["db"]->error;
			//echo '<textarea>'.$sql.'</textarea>';
		}else if($res==true)				
			$this->tableCreatedcount++;
	}
	function GetTableColumns($tablename){
		$sql="SHOW COLUMNS FROM $tablename";
		$res=$GLOBALS["db"]->fetchSubProcess($sql,true,true);
		/*Columns that will be generated are: Field,Type,Null,Key,Default,Extra*/
		/*For column Key - PRI will indicate that this is primary key*/
		/*Null - YES or NO will be indicated*/
		/*Default - will contain any default value or formula*/
		/*Extra - Any other values will go here*/
		return $res;
	}
	function FindAndAddNewColumns($tablename,$existingcolumns,$fielddef){
		/*1. Find new Columns*/
		$newcolumns=array();
		foreach($fielddef as $f){
			$found=false;
			foreach($existingcolumns as $e){
				if($e["Field"]==$f->name){
					$found=true;
					break;
				}
			}
			if(!$found) $newcolumns[]=$f;
		}
		/*2. Add New Columns*/
		/*Add column*/
		if(!empty($newcolumns)){
			$addcolumns=array();
			foreach($newcolumns as $f){
				$addcolumns=array("ADD COLUMN ".$f->name." ".$f->type." ".$f->other);
			}			
			$sql="ALTER TABLE ".$tablename." ".implode(", ",$addcolumns);
			$res=$GLOBALS["db"]->execute($sql);
			if(!empty($GLOBALS["db"]->error))
				$this->error[]=$tablename." table add error: ".$GLOBALS["db"]->error.".";
			else
				$this->successmsg[]=$tablename ." columns added: ".count($addcolumns);
		}
	}
	function FindAndUpdateNewColumns($tablename,$existingcolumns,$fielddef){
		$modifycolumns=array();
		$foundfield;
		foreach($existingcolumns as $e){
			$found=false;			
			foreach($fielddef as $f){
				if($e["Field"]==$f->name){
					$found=true;
					$foundfield=$f;
				}
			}
			if(!$found) continue;
			$editneeded=false;
			//if($tablename=="cmch_doctorsattendance") {print_r($e);echo "<br>";print_r($foundfield);echo "<br>";}
			/*Check Column Type*/
			if(!$this->MatchFieldType($foundfield->type,$e["Type"])){
				$editneeded=true;
				print_r($foundfield->type);echo "<br>";print_r($e["Type"]);echo "<br>";
			}
			
			/*Check Column Null*/
			if($e["Null"]=="YES"){
				/*null*/
				if(stripos($foundfield->other,"NOT NULL")!==false){
					$editneeded=true;					
				}
			}else{
				/*null*/				
				if(stripos($foundfield->other,"NOT NULL")===false || empty($foundfield->other)){
					if(stripos($foundfield->other,"auto_increment")===false)
						$editneeded=true;					
				}
			}
			
			if(!empty($e["Default"]) && !empty($foundfield->other)){
				/*default*/
				if(stripos($foundfield->other,$e["Default"])===false) $editneeded=true; 
			}
			if($editneeded==true){									
				$modifycolumns[]="MODIFY ".$foundfield->name." ".$foundfield->type." ".$foundfield->other;
			}
		}
		if(!empty($modifycolumns)){
			$sql="ALTER TABLE ".$tablename." ".implode(", ",$modifycolumns);
			$res=$GLOBALS["db"]->execute($sql);
			if(trim($GLOBALS["db"]->error)!="")
				$this->error[]=$tablename." column modify error: ".$GLOBALS["db"]->error;
			else
				$this->successmsg[]=$tablename." columns modified: ".count($modifycolumns);
		}		
	}
	function MatchFieldType($fieldtype1,$fieldtype2){		
		// Extracting string part
		$stringPart1 = trim(strtok($fieldtype1, "("));
		// Extracting number part
		$numberPart1 = strtok(")");
		// Extracting string part
		$stringPart2 = trim(strtok($fieldtype2, "("));
		// Extracting number part
		$numberPart2 = strtok(")");		
		
		if(stripos($stringPart1,"int")!==false){
			if(strtolower($stringPart1)==strtolower($stringPart2)){
				return true;
			}else{
				return false;
			}
		}else{			
			if(strtolower($stringPart1)==strtolower($stringPart2)){
				if(strtolower($numberPart1)==strtolower($numberPart2))
					return true;
				else{
					return false;
				}
			}else{
				return false;
			}
		}
		
	}
	function CreateAliasForTableFields(&$json){
		/*entire tableobject is to be supplied*/
		foreach($json as $j){
			if(isset($j->importtabledef)) continue;
			$tempnm=$j->name;
			$tempcnt=1;
			if(isset($j->fields)){
				foreach($j->fields as &$f){				
					if(empty($f->alias)){
						$f->alias=$tempnm.$tempcnt;
						$tempcnt++;
					}
					$this->CreateTable_GetFieldNameTypeAndOther($f);
				}
			}
		}
	}
	function CreateTable_GetFieldNameTypeAndOther(&$json){
		if(empty($json->name)) return false;	
		if(strpos($json->name," ")){
			$tempfld=explode(" ",trim($json->name));
		}else{
			if(isset($json->name) && isset($json->type)){
				if(empty($json->other)) $json->other="";			
				$json->type=$this->CreateTable_GetType(explode(" ",$json->name." ".$json->type),$json->other);
						
				return true;
			}else{
				if(!isset($json->name)) $json->name="";
				if(!isset($json->type)) $json->type="";
				if(!isset($json->other)) $json->other="";
				return false;
			}			
		}
		if(count($tempfld)>=2){
			$json->name=$tempfld[0];	
			$json->type=$this->CreateTable_GetType($tempfld,$json->other);		
			return true;
		}else{
			$json->name=$tempfld[0];
			$json->type="";
			$json->other="";
			return false;
		}
	}
		
	function CreateTable_GetType($usergiventype,&$other){
		$type=$usergiventype[1];
		$type1=preg_replace('/\D/','',$type);					
		if(intval($type1)>=4000) $type="text";
		if($type=="uniqueidentifier" || $type=="lookup") 
			$type="char(36)";
		else if($type=="googlelink")
			$type="varchar(50)";
		else if($type=="file")
			$type="varchar(50)";
		else if(strpos(strtolower($type),"enum")!==false){		
			if(stripos($type,"enum")!==false){
				$type="";
				$newi=1;
				for($i=1;$i<count($usergiventype);$i++){
					$newi=$i;
					$type.=" ".$usergiventype[$i];
					if(stripos($usergiventype[$i],")")!==false) break;					
				}			
				for($i=$newi+1;$i<count($usergiventype);$i++){
					$other.=" ".$usergiventype[$i];
				}
			}
		}
		if(empty($other) && stripos($type,"enum")==false){		
			for($i=2;$i<count($usergiventype);$i++){			
				$other.=" ".$usergiventype[$i];							
			}
		}
		if(!empty($other)){
			if(stripos($other,"GETDATE()")===true)
				$other=str_ireplace("GETDATE()","CURRENT_TIMESTAMP",$other);
		}
		
		return $type;
	}
	function ImportTableDef(&$plugin){
		foreach($plugin->table_def as &$obj){
			if(isset($obj->importtabledef)){
				//$sql="SELECT table_def FROM eirene_plugin WHERE pluginname='".$obj->importtabledef->pluginname."'";
				$sql="SELECT tabledef,meta FROM eirene_tables WHERE tablename='".$obj->name."' LIMIT 1";
				$result=$GLOBALS["db"]->FetchRecord($sql);
				/*result[0]=fielddef and result[1]=saveid,getid,delid*/
				if(!empty($result[0])){
					$result[0]=json_decode($result[0]);
					$obj->fields=$result[0];
				}
				if(!empty($result[1])){
					$result[1]=json_decode($result[1]);
					foreach($result[1] as $k=>$v){
						$obj->$k=$v;
					}
				}						
			}
		}		
	}
	function CreatePluginRecord($plugin1,&$pluginid,$userid){
		/*initialize*/
		$sql="";
		$dict=array();
		$tablename="plugin";
		$pluginname=str_replace("'","''",$plugin1->pluginname);
		$version=$plugin1->version;
		$icon=(empty($plugin1->icon))?"":$plugin1->icon;
		$table_def=is_string($plugin1->table_def)==false?json_encode($plugin1->table_def,JSON_UNESCAPED_UNICODE):$plugin1->table_def;
		$form=is_string($plugin1->form)==false?json_encode($plugin1->form,JSON_UNESCAPED_UNICODE):$plugin1->form;
		//$table_def=json_decode($table_def);		
		//$table_def=json_encode($table_def,JSON_UNESCAPED_UNICODE);
		//$form_html=isset($plugin1->form_html)?$plugin1->form_html:"";	
		$others=isset($plugin1->others)?$plugin1->others:"";
		$others=is_string($others)==false?json_encode($others,JSON_UNESCAPED_UNICODE):"";
		//$script=isset($plugin1->script)?$plugin1->script:"";
		$formbuttons=is_string($plugin1->formbuttons)==false?json_encode($plugin1->formbuttons,JSON_UNESCAPED_UNICODE):$plugin1->formbuttons;
		$formbuttons=str_replace("||pluginname||",$pluginname,$formbuttons);
		$status="1";
		$systemplugin=isset($plugin1->systemplugin)?$plugin1->systemplugin:"";
		$iswebsite=isset($plugin1->iswebsite)?$plugin1->iswebsite:0;
		$validationneeded=1;
		if(!isset($plugin1->validationneeded)){
			if(isset($plugin1->iswebsite))
				$validationneeded=0;
			else
				$validationneeded=1;
		}else{
			$validationneeded=$plugin1->validationneeded;
		}
		$initialstmt=isset($plugin1->initialstmt)?$plugin1->initialstmt:"";
		if($systemplugin==true || $systemplugin=="true" || $systemplugin=="1") $status="2";
			
		$res=false;
		/*Generate Field Values*/
		$dict["pluginname"]="'$pluginname'";
		$dict["version"]="'$version'";
		$dict["form"]="'".str_replace("'","''",$form)."'";
		$dict["icon"]="'$icon'";
		$dict["formbuttons"]="'".str_replace("'","''",$formbuttons)."'";
		$dict["status"]=$status;
		$dict["others"]="'".str_replace("'","''",$others)."'";
		$dict["iswebsite"]=$iswebsite;
		$dict["validationneeded"]=$validationneeded;
		$dict["initialstmt"]="'$initialstmt'";
		$dict["modifiedby"]="'$userid'";
		$dict["modifiedon"]="NOW()";
		if(!empty($pluginid)){
			/*For Update*/
			$res=$GLOBALS["db"]->Save($pluginid,$dict,"","eirene_plugin");			
		}else{
			$dict["createdby"]="'$userid'";
			$res=$GLOBALS["db"]->Save("",$dict,"","eirene_plugin");			
		}
		if(!empty($GLOBALS["db"]->error)) $this->error[]="Plugin error: ".$GLOBALS["db"]->error;
		if(empty($pluginid))			
			$pluginid=$GLOBALS["db"]->lastInsertId;
		return $res;
	}
	function InsertTableEntries($json,$pluginid){
		/*1. Delete already existing entries for the pluginid*/
		$sql="DELETE from eirene_tables WHERE pluginid='$pluginid'";
		$GLOBALS["db"]->execute($sql);
		
		foreach($json as $j){
			if(!isset($j->fields)) continue;
			/*2. Insert Table entires*/
			$name=$j->name;
			$fields=json_encode($j->fields);
			$fields=str_replace("'","''",$fields);
			$meta=json_decode("{}");
			$meta->saveid=isset($j->saveid)?$j->saveid:"";
			$meta->getid=isset($j->getid)?$j->getid:"";
			if(!empty($j->delid)) $meta->delid=$j->delid;
			if(!empty($j->delpermanentid)) $meta->delpermanentid=$j->delpermanentid; 
			$joindef=isset($j->joindef)?$j->joindef:"";
			/*joindef*/
			if(!empty($joindef)){
				foreach($joindef as $jk=>$jd){
					$joindef->$jk=str_ireplace("||thistbl||",$name,$jd);
				}
				$joindef=json_encode($joindef);
			}
			
			$meta =json_encode($meta);
			$meta=str_replace("'","''",$meta);
			$sql="INSERT INTO eirene_tables (id,tablename,tabledef,joindef,pluginid,meta) VALUES (UUID(),'$name','$fields','$joindef','$pluginid','$meta')";
			$GLOBALS["db"]->execute($sql);
			if(!empty($GLOBALS["db"]->error)){
				$this->error[]="Table Entries - $name: ".$GLOBALS["db"]->error;
			}
		}
		
	}
	function InsertLookUpFieldCommands($json,$pluginid){
		/*entire tableobject is to be supplied*/
		/*this sub will handle the table->field[lookup field] object*/
		/*{"name":"booksource","type":"lookup","lookup":{"sqlid":"0lkup","tbl":"tablename","fld":"title"}} by default id is selected in the fieldlist that user does not have to specify*/
		foreach($json as $j){
			if(isset($j->fields)){
				foreach($j->fields as $f){
					if(!isset($f->fieldtype)) $f->fieldtype="";				
					if(($f->type=="lookup" || $f->fieldtype=="lookup") && !empty($f->lookup)){
						if(!empty($f->lookup->sqlid) && !empty($f->lookup->tbl) && !empty($f->lookup->fld)){
							$command=json_decode("{}");
							$command->action="Get HTML";
							$command->tbl=trim($f->lookup->tbl);
							$command->key=false;
							$temptablealias="";
							if(strpos($command->tbl," ")!==false){
								$temptablealias=explode(" ",$command->tbl)[1].".";
							}
							
							if(strpos($f->lookup->fld,"(")!==false){							
								/*this means that fld has an sql function defined and is not just a field name*/
								/*fld*/
								$posbracket=strpos($f->lookup->fld,"(");
								$substrfld=substr($f->lookup->fld,0,$posbracket);
								
								if(strpos($substrfld,",")===false)
									$command->fld=$temptablealias."id,".$f->lookup->fld;						
								else
									$command->fld=$f->lookup->fld;
								$tempfld=$f->lookup->fld;
								/*In this case, it is expected that an alias is defined*/
								if(strpos($f->lookup->fld,"as")!==false){
									/*as is a keyword in mysql for defining alias*/
									$tempfld=trim(explode("as",$f->lookup->fld)[1]);								
								}else{
									/*since as keyword is not used for defining alias, it is assumed that a space is used to define alias*/
									$tempfld=explode(" ",$f->lookup->fld);
									$tempfld=trim($tempfld[count($tempfld)-1]);
								}
								//$command->having=$tempfld." like '%||searchtext||%'";
								$command->whr=$temptablealias."recordstatus=1";
							}else{
								if(!strpos($f->lookup->fld,","))
									$command->fld=$temptablealias."id,".$f->lookup->fld;
								else
									$command->fld=$f->lookup->fld;
								$tempfld=$f->lookup->fld;
								$command->whr=$temptablealias."recordstatus=1";
							}
							
							if(!empty($f->lookup->whr)) 
								$command->whr.=" and ".$f->lookup->whr;
							else{
								$tempfld=$f->lookup->fld;
								if(!strpos($tempfld,"(")){
									$tempfld=explode(",",$tempfld);
									if(count($tempfld)==1){
										$command->whr.=" and ".$tempfld[0]." like '%||searchtext||%'";
									}else if(count($tempfld)==2){
										$command->whr.=" and ".$tempfld[1]." like '%||searchtext||%'";
									}
								}else
									$command->whr.=" and ".$f->lookup->fld." like '%||searchtext||%'";
							}
							$command->prepend="<select size='4' style='width:100%' onchange='$(&apos;#||targetelem||&apos;).val($(this).val())'>";
							$command->template="<option value='||0||'>||1||</option>";
							$command->append="</select>";
							foreach($f->lookup as $k=>$v){
								if($k!="action" && $k!="tbl" && $k!="fld" && $k!="whr" && $k!="prepend" && $k!="template" && $k!="append"){
									$command->$k=$v;
								}
							}
							$command=str_replace('"','\"',json_encode($command,JSON_UNESCAPED_UNICODE));
							$command=str_replace("'","\'",$command);
							//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$f->lookup->sqlid."','".$f->lookup->sqlid." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
							$this->commandstatement[]="(UUID(),'".$f->lookup->sqlid."','".$f->lookup->sqlid." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";							
						} 
					}
				}			
			}
		}
	}
	function InsertGetTableSqlStatements($json,$pluginid){
		/*entire tableobject is to be supplied*/
		/*this sub will handle the table->list object*/
		foreach($json as $j){
			if(isset($j->list)){			
				$tablename=$j->name;			
				foreach($j->list as $k=>$v){
					if(!empty($v)){				
						if(!isset($v->action)) $v->action="Get Table";
						if(isset($v->tbl)) $v->tbl=str_replace("||thistbl||",$tablename,$v->tbl);
						if(isset($v->join) && is_string($v->join)) $v->join=str_replace("||thistbl||",$tablename,$v->join);
						
						if($v->action=="Get Search Page"){
							$this->InsertSearchPage($k,$v,$pluginid);
							continue;
						}
						if($v->action=="Get Table"){
							//if(!isset($v->whr)) $v->whr=$tablename.".recordstatus=1";
							if(!isset($v->whr)) $v->whr="";
							if(!isset($v->outputto)) $v->outputto="html";
							if(!isset($v->output)) $v->output="table";
							if(!isset($v->fld) && isset($v->fields)){$v->fld=$v->fields;unset($v->fields);}
							if(!isset($v->formname)) $v->formname=$j->name;					
							if(!isset($v->tbl)) $v->tbl=$tablename;							
							
						}
						if(isset($v->getform)) $v->getform=str_replace("||thistbl||",$tablename,$v->getform);
						if(!isset($v->saveid) && isset($j->saveid)) $v->saveid=$j->saveid;
						if(!isset($v->getid) && isset($j->getid)) $v->getid=$j->getid;
						if(!isset($v->delid) && isset($j->delid)) $v->delid=$j->delid;
						if(!isset($v->delpermanentid) && isset($j->delpermanentid)) $v->delpermanentid=$j->delpermanentid;
						if(isset($v->tbl)){						
							$v->tbl=str_replace(" as "," ",$v->tbl);
							$v->tbl=trim(str_replace("  "," ",$v->tbl));
							$tablealias=explode(" ",$v->tbl);
							if(count($tablealias)>1)
								$tablealias=$tablealias[count($tablealias)-1];
							else
								$tablealias=$tablealias[0];
						}
						/*set header if it is not set*/					
						if(!isset($v->header) && $v->action=="Get Table"){
							if(isset($j->fields)){
								$flds=GetStringFromJson($v,"fld",",");
								$flds=str_replace("$tablealias.","",$flds);
								$flds=explode(",",$flds);
								$heads=array();							
								foreach($flds as $fld){
									foreach($j->fields as $f){
										if($fld==$f->name) $heads[]=$f->label;
									}
								}
								$v->header=implode(",",$heads);
							}
						}
						/*del id */
						//if(isset($j->delid)) $v->delid=$j->delid;
						//if(isset($j->delpermanentid)) $v->delpermanentid=$j->delpermanentid;
						$command=str_replace('"','\"',json_encode($v,JSON_UNESCAPED_UNICODE));
						$command=str_replace("'","\'",$command);						
						//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$k."','".$tablename." list','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
						$this->commandstatement[]="(UUID(),'".$k."','".$tablename." list','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";						
					}
				}
			}
		}
	}
	function InsertSaveEditDeleteSqlStatements($json,$pluginid){
		/*entire table-object is to be supplied*/
		foreach($json as $f){
			$sqlid=array();
			if(isset($f->saveid)){
				/*saveid can be string for e.g. 2sv or array of object like below*/
				/*
				"saveid":[
					{"id":"cclasv","whr":"rollno='||rollno||' and date(createdon)=CURDATE()"}
				]
				*/
				$tempsave=array();
				if(is_string($f->saveid)){
					//$temps=json_decode("{}");
					$sqlid=explode("-",$f->saveid)[0];
					//$temps->id=$sqlid;
					//$tempsave[]=$temps;
					//$f->saveid=$tempsave;
					$tempsql=json_decode("{}");
					$tempsql->whr="id='||ID||'";					
					$tempsql->action="SaveNew";				
					$this->AddSupportingNodesforSaveAndEditCommand($tempsql,$f,"Save");
					$tempsql->trigger=json_decode("{}");
					if(isset($f->trigger->insert)) $tempsql->trigger->insert=$f->trigger->insert;
					if(isset($f->trigger->update)) $tempsql->trigger->update=$f->trigger->update;				
					$tempsql=json_encode($tempsql,JSON_UNESCAPED_UNICODE);
					$tempsql=str_replace("'","''",$tempsql);
					$this->commandstatement[]="(UUID(),'".$sqlid."','".$f->name." Save','".$tempsql."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
					
				}else if(is_array($f->saveid)){
					foreach($f->saveid as $j){
						if(!is_object($j)) continue;
						$sqlid=explode("-",$j->id)[0];
						$tempsql=json_decode("{}");
						if(isset($j->whr)){
							$tempsql->whr=$j->whr;
						}else
							$tempsql->whr="id='||ID||'";					
						$tempsql->action="SaveNew";
						$tempsql->formname=$f->name;
						$this->AddSupportingNodesforSaveAndEditCommand($tempsql,$f,"Save");
						$tempsql->trigger=json_decode("{}");
						if(isset($f->trigger->insert)) $tempsql->trigger->insert=$f->trigger->insert;
						if(isset($f->trigger->update)) $tempsql->trigger->update=$f->trigger->update;						
						
						$tempsql=json_encode($tempsql,JSON_UNESCAPED_UNICODE);
						$tempsql=str_replace("'","''",$tempsql);
						//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$sqlid."','".$f->name." Save','".$tempsql."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
						$this->commandstatement[]="(UUID(),'".$sqlid."','".$f->name." Save','".$tempsql."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";					
					}
				}
			}
			if(isset($f->getid)){
				$sqlid=explode("-",$f->getid);
				$tempsql=json_decode("{}");
				
				$tempsql->action="Edit";
				$tempsql->outputto="html";
				$tempsql->output="edit";
				$tempsql->formname=$f->name;
				/*fld*/
				$newfld=array();
				$newfld[]="id";
				foreach($f->fields as $ff){				
					if(isset($ff->alias))
						$newfld[]=$ff->name." as ".$ff->alias;
					else
						$newfld[]=$ff->name;
					
				}
				$tempsql->fld=implode(",",$newfld);
				$tempsql->jsfunction="edit1";
				$tempsql->tbl=$f->name;
				$tempsql->whr="id='||ID||'";
				//AddSupportingNodesforSaveAndEditCommand($tempsql,$f,"Edit");						
				//print_r($tempsql);			
				$tempsql=json_encode($tempsql,JSON_UNESCAPED_UNICODE);
				$tempsql=str_replace("'","''",$tempsql);
				//echo $tempsql."<br>";
				//$tempsql=str_replace('"','\"',$tempsql);
				//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$sqlid[0]."','".$f->name." Get Record','".$tempsql."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";			
				$this->commandstatement[]="(UUID(),'".$sqlid[0]."','".$f->name." Get Record','".$tempsql."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";							
			}
			if(isset($f->delid)){
				$sqlid=explode("-",$f->delid);
				$tempsql=json_decode("{}");
				$tempsql->action="Delete Row";
				$tempsql->formname=$f->name;
				if(!isset($json->whr)) $tempsql->whr="id='||ID||'";
				$tempsql->tbl=$f->name;
				$tempsql->trigger=json_decode("{}");
				if(isset($f->trigger->delete)){
					$tempsql->trigger->delete=$f->trigger->delete;				
				}
				if(isset($f->ondeletesuccess)) $tempsql->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-cmd:dom,fun:hide;current-".$f->ondeletesuccess;
				if(isset($f->ondeletefailure)) $tempsql->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed-".$f->ondeletefailure;
				if(!isset($tempsql->onsuccess)) $tempsql->onsuccess="cmd:dom,fun:showtoast;success;Delete Successful-cmd:dom,fun:hide;current";	
				if(!isset($tempsql->onfailure)) $tempsql->onfailure="cmd:dom,fun:showtoast;alert;Delete Failed";	
				$tempsql=json_encode($tempsql,JSON_UNESCAPED_UNICODE);
				$tempsql=str_replace("'","''",$tempsql);
				//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$sqlid[0]."','".$f->name." Delete Record','$tempsql','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
				$this->commandstatement[]="(UUID(),'".$sqlid[0]."','".$f->name." Delete Record','$tempsql','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";				
			}
			if(isset($f->delpermanentid)){
				$sqlid=explode("-",$f->delpermanentid);
				$tempsql=json_decode("{}");
				$tempsql->action="Delete Row Permanently";
				$tempsql->formname=$f->name;
				if(!isset($json->whr)) $tempsql->whr="id='||ID||'";
				$tempsql->tbl=$f->name;
				$tempsql->trigger=json_decode("{}");
				if(isset($f->trigger->delete)){
					$tempsql->trigger->delete=$f->trigger->delete;				
				}
				$tempsql=json_encode($tempsql,JSON_UNESCAPED_UNICODE);
				$tempsql=str_replace("'","''",$tempsql);
				//$sql1="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$sqlid[0]."','".$f->name." Delete Record','$tempsql','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
				$this->commandstatement[]="(UUID(),'".$sqlid[0]."','".$f->name." Delete Record','$tempsql','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";				
			}
		}
	}
	function InsertSearchPage($id,$json,$pluginid){	
		$id1=substr($id,0,strlen($id)-1)."1";
		$id2=substr($id,0,strlen($id)-1)."2";
		/*id is the customid and $json must contain output,$tbl,$fld*/
		/*importation definition output,tbl,searchfld,displayfld*/
		
		/*step1- Insert sqlstatement for page*/
		$tempjson=json_decode("{}");
		$tempjson->action="Return";$tempjson->output=$json->output;
		$tempjson->value="<div data-role='splitter' data-split-sizes='30,70'><div><input type='text' placeholder='Search' onkeyup='if(event.keyCode==13){Eirene.runStmt(&apos;".$id1."&apos;,{topic:$(this).val()})}'><div id='searchdiv'></div></div><div id='helplt'></div></div>";
		$command=str_replace('"','\"',json_encode($tempjson,JSON_UNESCAPED_UNICODE));
		$command=str_replace("'","\'",$command);
		//$sql="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$id."','".$id." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
		$this->commandstatement[]="(UUID(),'".$id."','".$id." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";		
		
		/*Step2- */
		/*json->searchfld must be specified withoutwhich this function will not run*/
		$tempjson=json_decode("{}");
		$tempjson->action="Get HTML";
		$tempjson->output="#searchdiv";
		$tempjson->tbl=$json->tbl;
		$tempjson->fld="CONCAT('<div class=fg-blue onclick=Eirene.runStmt(&apos;".$id2."&apos;,{id:&apos;',id,'&apos;})>',".$json->searchfld.",'</div>') as topic";
		$tempjson->whr=$json->searchfld. " like '%||topic||%'";
		$tempjson->template="<div class='p-2'>||topic||</div>";
		$command=str_replace('"','\"',json_encode($tempjson,JSON_UNESCAPED_UNICODE));
		$command=str_replace("'","\'",$command);
		//$sql="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$id1."','".$id1." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
		$this->commandstatement[]="(UUID(),'".$id1."','".$id1." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";		
		
		/*Step3- Display of Result on click of resultpage item*/
		/*Step3 will not function without json->displayfld*/
		$tempjson=json_decode("{}");
		$tempjson->action="Get Value";
		$tempjson->output="#helplt";
		$tempjson->tbl=$json->tbl;
		$tempjson->fld=$json->displayfld;
		$tempjson->whr="id='||id||'";
		$command=str_replace('"','\"',json_encode($tempjson,JSON_UNESCAPED_UNICODE));
		$command=str_replace("'","\'",$command);
		//$sql="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES (UUID(),'".$id2."','".$id2." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";
		$this->commandstatement[]="(UUID(),'".$id2."','".$id2." lookup','".$command."','".$pluginid."','".$GLOBALS["userinfo"]["id"]."')";				
	}
	function InsertCommands($plugin1,$pluginid,$userid){
		$sqlstmt=0;
		$stmtfailed=0;		
		foreach($plugin1->sqlstatement as $x){
			$res=false;
			/*Check*/
			if(isset($x->id))
				$sqlid=$x->id;
			else if(isset($x->customid))
				$sqlid=$x->customid;
			else
				continue;
			if(isset($x->sql))
				$ssql=$x->sql;
			else
				continue;			
			$ssql=json_encode($ssql,JSON_UNESCAPED_UNICODE);
			//$ssql=str_replace('"','\"',$ssql);			
			$this->commandstatement[]="(UUID(),'$sqlid','".str_replace("'","''",$x->name)."','".str_replace("'","''",$ssql)."','$pluginid','$userid')";			
		}
		$sql="INSERT INTO eirene_sqlstatements (id,customid,pname,sql_statement,pluginid,createdby) VALUES ";
		$sql.= implode(",",$this->commandstatement);
		$GLOBALS["db"]->Execute($sql);
		if($GLOBALS["db"]->error!=""){
			//$this->error[]="$sqlid: ".$GLOBALS["db"]->error;
			$this->successmsg[]="<span style='color:red'>Command generation failed</span>";
			//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$GLOBALS["db"]->Sql).'</textarea><br>';
		}else
			$this->successmsg[]="Commands generated: ".$GLOBALS["db"]->rowsAffected;
		  
	}
	function AddSupportingNodesforSaveAndEditCommand(&$json,$form,$addNodeFor="Save"){
		$field=array();
		$fieldtype=array();
		$fieldvalue=array();			
		foreach($form->fields as $f){
			$field[]=$f->name;							
		}
		if(!isset($json->whr)) $json->whr="id=''||ID||''";
		$json->fld=implode(",",$field);
		if(!isset($json->tbl)) $json->tbl=$form->name;	
		if($addNodeFor=="Save"){				
			if(!empty($f->customsave)){
				//$tempsql->customsave=GetStringFromJson($f,"customsave",'\n');
				$json->customsave=json_encode($f->customsave,JSON_UNESCAPED_UNICODE);
				$json->customsave=str_replace("'","&apos;",$json->customsave);				
			}
			if(isset($json->onsavesuccess)) $json->onsuccess=$json->onsavesuccess;
			if(!isset($json->onsuccess)) $json->onsuccess="cmd:dom,fun:showtoast;success;Saving Successful-cmd:dom,fun:attr;current;disabled;1";
			if(!isset($json->onfailure)) $json->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed";
		}
	}
	
	function ExportPlugin($pluginid){
		$export=json_decode("{}");
		/*1. Export Plugin*/
		$sql="SELECT pluginname,version,icon,status,form,formbuttons,others,iswebsite,validationneeded,initialstmt FROM eirene_plugin WHERE id='$pluginid' and recordstatus=1";
		
		$plugin=$GLOBALS["db"]->fetchSubProcess($sql,false,true);
		foreach($plugin as $k=>$v){
			$export->$k=$v;
		}
		$export->form=json_decode($export->form);
		$export->formbuttons=json_decode($export->formbuttons);
		if($plugin["status"]==2) $export->systemplugin=2;
		unset($export->status);
				
		/*2. Export Tables*/
		$sql="SELECT tablename,tabledef,meta,joindef FROM eirene_tables WHERE pluginid='$pluginid'";
		$tableres=$GLOBALS["db"]->fetchSubProcess($sql,true,true);
		$export->table_def=array();
		foreach($tableres as $t){
			$table=json_decode("{}");
			$table->name=$t["tablename"];
			$temp=json_decode($t["meta"]);
			if(!empty($temp)){
				foreach($temp as $i=>$k) $table->$i=$k;
			}
			$table->joindef=$t["joindef"];
			$table->fields=json_decode($t["tabledef"]);
			$export->table_def[]=$table;
		}
		unset($tableres);
		/*3. Export Sql Statements*/
		$sql="SELECT customid,pname,sql_statement FROM eirene_sqlstatements WHERE pluginid='$pluginid'";
		$tableres=$GLOBALS["db"]->fetchSubProcess($sql,true,true);
		$export->sqlstatement=array();
		foreach($tableres as $s){
			$command=json_decode("{}");
			$command->id=$s["customid"];
			$command->name=$s["pname"];
			$command->sql=json_decode($s["sql_statement"]);
			$export->sqlstatement[]=$command;
		}
		
		/*4. Save string as export.json*/
		if(file_put_contents("export.json", json_encode($export))){			
			return true;
		}else
			return false;
		
		
	}
}
?>