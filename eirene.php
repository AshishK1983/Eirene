<?PHP
$version="v2.647";
$debug=1;

class Connection{
    public static function conn(){
        if(isset($GLOBALS['conn'])){
            return $GLOBALS['conn'];
        }
    }    
}

class EireneApi{
	
	
	public function CreateTableBulk($tables){
		$db=$GLOBALS["db"];		
		$tables=json_decode($tables);		
		$cnt=0;
		$error=array();
		$msg=array();
		foreach($tables as $t){
			foreach($t->table_def as $obj){
				if($db->TableExists($obj->name)==false){
					$sql="CREATE TABLE IF NOT EXISTS ". $obj->name ."(";
					$sql.="id char(36) PRIMARY KEY,";
					foreach($obj->fields as $value){
						$name=$value->name;
						$type=$value->type;
						$other="";
						$type1=str_replace("varchar","",$type);
						$type1=str_replace("(","",$type1);
						$type1=str_replace("(","",$type1);
						if(intval($type1)>=4000)
							$type="text";
						if($type=="uniqueidentifier")
							$type="char(36)";
						if(isset($value->other))
							$other=$value->other;
						if(strpos(strtoupper($other),"GETDATE()">-1))
							$other=str_replace("GETDATE()","CURRENT_TIMESTAMP",$other);
						$sql.=$name ." ".$type." ".$other .",";				
					}
					$sql.="createdby char(36) not null,createdon date DEFAULT CURRENT_TIMESTAMP not null,modifiedby char(36),modifiedon date,recordstatus tinyint not null default 1";
				   $sql.=")";		   
				   $res=$db->Execute($sql);			
				   if(trim($db->error)!=""){
						array_push($error,$db->error);
						array_push($msg,"Error creating Table ".$obj->name." ".$db->error);
				   }else if($res==true){			
						$cnt++;
						array_push($msg,"Tabl ".$obj->name." is created");
				   }
				}else{
					array_push($msg,"Table ".$obj->name." exists already");
				}
			}
		}
		return implode("\n",$msg);
	}
	
	public function InsertIntoTable($tablename,$fieldname,$data){
		$sql="INSERT INTO $tablename ($fieldname) VALUES $data ON DUPLICATE KEY UPDATE ";
		$fieldname=explode(",",$fieldname);
		$len=count($fieldname);
		for($i=0;$i<$len;$i++){
			$sql.=" $fieldname[$i] = VALUES($fieldname[$i])";
			if($i<$len-1)
				$sql.=",";
		}
		$db=$GLOBALS["db"];			
		$res=$db->Execute($sql);		
		if($db->error!="")
			return $db->error."\n".$db->Sql;
		else if($res==true)
			return intval($db->rowsAffected)." rows synced";
		else
			return "No records synced";
			
	}
	public function GetHTMLViaTemplate(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		if(isset($sqlJson->parent)) 
			$tag=$sqlJson->parent;		
		else
			$tag="";
		if(empty($sqlJson->template)) $sqlJson->template="";		
		
		$tag1=explode(" ",$tag);
		$db=$GLOBALS["db"];		
		$db->error="";
		$list="";
		if(!isset($sqlJson->convertDataSourceToJson)){
			if(!isset($sqlJson->key))
				$list=$db->GetList1($sqlJson->sql,true);
			else
				$list=$db->GetList1($sqlJson->sql,$sqlJson->key);
		}else{
			//if(isset($sqlJson->whr)) $sqlJson->whr=GetStringFromJson($sqlJson,whr);
			$list=$db->GetValue($sqlJson->sql);
			$list=json_decode($list);
		}
		
		$error="";
		//echo str_replace('&apos;','&apos','<textarea>'.$sqlJson->sql.'</textarea><br>');print_r($GLOBALS["globalphp"]);
		//print_r($GLOBALS['value']);
		if(!empty($db->error)){$sqlJson->error=$db->error;return false;}		
		if(is_string($list)){
			if(isset($sqlJson->convertDataSourceToJson)){
				$list=json_decode($list);
			}else{
				$sqlJson->error=$list;
				return false;
			}
		}
		$sqlJson->success=true;
		$str="";
		if(!empty($tag))
			$str='<'.$tag.'>';
		$temp="";
		$row=0;
		$templatecopy=$sqlJson->template;
		//print_r($templatecopy);
		if(!isset($sqlJson->addedvariables) && is_object($sqlJson)){			
			$sqlJson->addedvariables=array();
		}
		foreach($list as $ll){			
			$row++;$GLOBALS["value"]["currentrow"]=$row;
			$sqlJson->serialno=$row;
			//foreach($ll as $k=>$v){				
			//	$GLOBALS["value"][$k]=$v;				
			//}
			$sqlJson->template=$templatecopy;
			//echo $sqlJson->sqlid." = ";print_r($sqlJson->addedvariables);echo "<br>";
			foreach($ll as $k=>$l){
				
				$sqlJson->addedvariables[$k]=$l;
			}
			$str.=GetStringFromJson($sqlJson,"template");
			
			if(isset($sqlJson->loopcolumns)){
				$temp="";
				foreach($ll as $k=>$l){
					$replacearray=array();
					$replacearray[$k]=$l;
					$temp.=ConvertTemplateToHTML($sqlJson,$replacearray);
				}
			}else{
				
				//$str.=GetStringFromJson($sqlJson,"template");
				
								
			}
			
		}
		if(!empty($tag))
			$str.='</'.$tag1[0].'>';
		
		$str=str_replace("\n","<br>",$str);		
		
		$ux=new EireneUx();
		$str=$ux->SupplyParamValue($str);
		
		if(strpos($str,"||SIGNATURE||")!==false){			
			$list=$db->FetchRecord($sqlJson->sql);
			$orderid=$list["orderid"];
			$amount=$list["amount"];
			$fullname=$list["fullname"];
			$phoneno=$list["phoneno"];
			$emailid=$list["emailid"];
			$returnurl=(empty($sqlJson->returnurl))?"":$sqlJson->returnurl;
			$notifyurl=(empty($sqlJson->notifyurl))?"":$sqlJson->notifyurl;
			$ordernote=(empty($sqlJson->ordernote))?"":$sqlJson->ordernote;
			$str=str_replace("||SIGNATURE||",GetSignature($orderid,$amount,$fullname,$phoneno,$emailid,$returnurl,$notifyurl,$ordernote),$str);
		}		
		setOutput("res",$result,$str,$sqlJson);	
	}
	
	public function GetTemplate1(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		$str=array();		
		$db=$GLOBALS["db"];		
		$db->error="";
		$list=$db->GetList1($sqlJson->sql,true);
		//print_r($list);
		//echo $sqlJson->sql;
		//echo $db->Sql;
		$error="";
		if(!empty($db->error)){$sqlJson->error=$db->error;return false;}
		$sqlJson->success=true;
		if(is_string($list)){
			$sqlJson->error=$list;
			return false;
		}
		$sqlJson->success=true;
		$distinct=array();
		
		$ux=new EireneUx();
		$row=0;
		foreach($list as $ll){
			$row++;$GLOBALS["value"]["currentrow"]=$row;
			if(!empty($sqlJson->distinct)){
				if(isset($ll[$sqlJson->distinct])){
					if(!in_array($ll[$sqlJson->distinct],$distinct))$distinct[]=$ll[$sqlJson->distinct];
				}
			}
			$template="";
			$replace=array();			
			$parent="";
			
			
			if(isset($sqlJson->caseparent) && empty($parent)){
				foreach($sqlJson->caseparent as $c){					
					$con=array();					
					$con["parent"]=$c->parent;$con["parent"]=$ux->SupplyParamValue($con["parent"],$sqlJson);
					$cc=json_decode("{}");
					$iif=array("if","operator","op","value","andif","andoperator","andop","andvalue","orif","oroperator","orop","orvalue");
					foreach($c as $k=>$v){
						if(in_array($k,$iif)){
							if(strpos($v,"||")!==false){
								$tt=str_replace("||","",$v);
								$cc->$k=str_replace("||".$tt."||",$ll[$tt],$v);
							}else
								$cc->$k=$v;
						}						
					}					
					
					$check=false;
					$check=$this->CheckIfCondition($cc,$value,$result);					
					if($check==true){						
						$parent=$con["parent"];											
						break;						
					}
				}				
			}
			$template1="";
			if(isset($sqlJson->template)){
				//print_r($sqlJson->template);echo "<br><br>";
				if(is_string($sqlJson->template)){
					$template1=$sqlJson->template;
				}else if(is_array($sqlJson->template) || is_object($sqlJson->template)){
					$template1=ConvertTemplateToHTML($sqlJson,$ll);
				}
				
			}			
				
			/*replace variable for sql results*/
			foreach($ll as $k=>$v){
				if(strpos($template1,"||$k||")!==false){
					$template1=str_replace("||$k||",$v,$template1);
				}
			}
			$parent=(empty($parent))?"str":$parent;				
				$str[$parent][]=$template1;				
			
		}
		/*get parent tag*/
		foreach(array_keys($str) as $k){
			foreach($sqlJson->parents as $k1=>$p){
				if($k==$k1){
					$tag=explode(" ",trim($p))[0];
					$pcnt=count($str[$k]);					
					$str[$k]="<".$p.">".implode(" ",$str[$k])."</".$tag.">";
					$str[$k]=str_replace("||pcnt||",$pcnt,$str[$k]);
					break;
				}
			}
		}
		if(!empty($distinct)) $sqlJson->distinct=$distinct;
		/*get mainparent tag */
		//print_r($str);
		
		
		$str=join("",$str);
		//echo is_string($str);
		$tag=explode(" ",trim($sqlJson->mainparent))[0];
		$str="<".$sqlJson->mainparent.">".$str."</".$tag.">";		
		
		setOutput("res",$result,$btn.$str,$sqlJson);
	}
	public function GetRows(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		if(strpos(strtolower($sqlJson->sql),"select")===false || strpos(strtolower($sqlJson->sql),"from")===false){	
			$sqlJson->error="Invalid Query (Rows)";
			return false;
		}		
		$db=$GLOBALS["db"];
		$db->error="";
		$res=$db->GetJSONData1($sqlJson->sql,true);
		//echo $db->Sql."<br>";
		if(!empty($db->error)){
			$sqlJson->error=$db->error;
			return false;
		}
		
		
		if(!empty($res)) {$sqlJson->success=true;}
		//print_r($value);echo $sqlJson->sql;
		setOutput("res",$result,$res,$sqlJson);
		return $sqlJson->success;
	}
	public function GetColumn(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		if(strpos(strtolower($sqlJson->sql),"select")===false || strpos(strtolower($sqlJson->sql),"from")===false){	
			$sqlJson->error="Invalid Query (Column)";
			return false;
		}		
		$db=$GLOBALS["db"];
		$res=$db->GetJSONData1($sqlJson->sql,true);
		if(!empty($db->error)){
			$sqlJson->error=$db->error;
			return false;
		}
		//echo $db->Sql;
		$res1=false;
		if(!empty($res)) $res1=true;
		$str=array();
		foreach ($res as $r){
			array_push($str,$r);
		}
		$str="[".implode(",",$str)."]";
		//print_r($value);echo $sqlJson->sql;
		
		setOutput("res",$result,$str,$sqlJson);
		return $sqlJson->success;
	}
	public function GetRow(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		if(strpos(strtolower($sqlJson->sql),"select")===false || strpos(strtolower($sqlJson->sql),"from")===false){		
			$sqlJson->error="Invalid Query (Row)";			
			return false;
		}
		$db=$GLOBALS["db"];
		
		$res=$db->GetJSONData($sqlJson->sql);
		
		if(substr($res,0,1)!=chr(123)) $res="";
		//print_r($value);echo "<br>";
		//echo "<textarea>".$db->Sql."</textarea><br>";
		//print_r($res);echo "<br>";
		if(!empty($res)){
			$sqlJson->success=true;			
		}
				
		if($db->error!=""){
			$sqlJson->error=$db->error;
		}else
			setOutput("res",$result,$res,$sqlJson);		
		return $sqlJson->success;
	}
	
	public function GetValue(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		if(!isset($sqlJson->sql)) return false;
		if(strpos(strtolower($sqlJson->sql),"select")===false || strpos(strtolower($sqlJson->sql),"from")===false ){		
			print_r($sqlJson);
			$sqlJson->error="Invalid Query (Value)"; return false;			
		}
		$db=$GLOBALS["db"];
		$res=$db->GetValue($sqlJson->sql);
		//echo "<textarea>".$sqlJson->sql."</textarea>";
		//print_r($res);
		if(!empty($db->error)){
			$sqlJson->error=$db->error;
		}else{
			$sqlJson->success=true;
			setOutput("res",$result,$res,$sqlJson);
		}
		return $res;
	}
	
	public function RunStatement(&$sqlJson,$value,&$result){
		$db=$GLOBALS["db"];		
		$sqlJson->success=false;
		if(!isset($sqlJson->loop)){
			if(isset($sqlJson->sql))
				$sql=explode(";",$sqlJson->sql);
			else if(isset($sqlJson->stmt))
				$sql=explode(";",$sqlJson->stmt);
			else if(is_array($sqljson->stmt))
				$sql=$sqljson->stmt;
			else if(is_array($sqljson->sql))
				$sql=$sqljson->sql;
			$ux=new EireneUx();
			
			//print_r($sqlJson);echo "<br>";
			
			foreach($sql as $s){
				$s=$ux->SupplyParamValue($s,$sqlJson);
				//echo '<textarea>'.$s."</textarea><br>";				
				
				
				$res=$db->Execute($s);				
				if(empty($sqlJson->output)) $sqlJson->output="res";
				if($db->error!=""){
					setOutput("error",$result,$db->error,$sqlJson);
					//echo $db->error;
					return false;
				}else{
					setOutput("res",$result,$res,$sqlJson);
				}
			}
			$sqlJson->success=true;
		}else{
			$ux=new EireneUx();
			if(!isset($sqlJson->looptype)) return false;
			$vvv=array();			
			if($sqlJson->looptype=="number"){				
				$sqlJson->loopbeginsatnumber=intval($ux->SupplyParamValue($sqlJson->loopbeginsatnumber,$sqlJson));
				$sqlJson->loopendsatnumber=intval($ux->SupplyParamValue($sqlJson->loopendsatnumber,$sqlJson));
				if(!isset($sqlJson->loopstep)) $sqlJson->loopstep=1;
				//echo $sqlJson->loopbeginsatnumber."<br>";
				//echo $sqlJson->loopendsatnumber."<br>";
				for($i=$sqlJson->loopbeginsatnumber;$i<=$sqlJson->loopendsatnumber;$i+=intval($sqlJson->loopstep)){
					$vvv[]=$i;					
				}
			}else if($sqlJson->looptype=="commavalue"){
				$sqlJson->loopvalues=$ux->SupplyParamValue($sqlJson->loopvalues,$sqlJson);
				$vvv=explode(",",$sqlJson->loopvalues);
			}
			if(!empty($vvv)){
				
				$sqlJson->sql=$ux->SupplyParamValue($sqlJson->sql,$sqlJson);
				foreach($vvv as $v){
					$s=$sqlJson->sql;					
					$s=str_replace("||loopvalue||",$v,$s);					
					$res=$db->Execute($s);			
					if($db->error!=""){
						setOutput("error",$result,$db->error,$sqlJson);					
						return false;
					}else{
						setOutput("res",$result,$res,$sqlJson);
					}
				}
				$sqlJson->success=true;
			}
		
		}
	}
	
	public function DeleteRow(&$sqlJson,&$value,&$result,$deletepermanently){
		$sqlJson->success=false;
		$db=$GLOBALS["db"];
		/*
		if(isset($sqlJson->permission)){
			if($sqlJson->permission<2){
				$sqlJson->error="Permission Conflict";
				return false;
			}
		}
		*/
		
		

		$tablename=$sqlJson->tbl;
		$whr=$sqlJson->whr;
		$fields="recordstatus,modifiedby";
		$fieldtypes="n,u";
		$values="0,||USERID||";
		$param=isset($sqlJson->param)?$sqlJson->param:"";
		$paramvalue=isset($value["paramvalue"])?$value["paramvalue"]:"";
		$seperator=",";
		if(isset($sqlJson->paramseperator)) $seperator=$sqlJson->paramseperator;
		$ux=new EireneUx();
		$whr=$ux->SupplyParamValue($whr,$sqlJson);
		$tablename=$ux->SupplyParamValue($tablename,$sqlJson);
		$values=$ux->SupplyParamValue($values,$sqlJson);
		$values=explode(",",$values);
		$values=implode(chr(1),$values);
		if($seperator=="chr(1)") $seperator=chr(1);
		
		if($deletepermanently==false)		
			$res=$db->SaveTable("update",$tablename,$fields,$fieldtypes,$values,$whr);
		else{
			$sql="DELETE FROM ".$sqlJson->tbl." WHERE ".$whr;
			$res=$db->Execute($sql);
		}
		//echo "<textarea>".$db->Sql."</textarea>";
		$error="";
		
		if(empty($sqlJson->output)) $sqlJson->output="res";
		if($db->error!=""){
			$sqlJson->error=$db->error."<br>";return false;
		}else if(is_bool($res)==false){
			$sqlJson->error=$res;
			return false;
		}else{			
			$sqlJson->success=true;			
		}
		
		/*trigger*///print_r($sqlJson);
		if(isset($sqlJson->trigger->delete)){			
			$sqlJson->delete=$sqlJson->trigger->delete;
			/*deletetrigger must be sqlstatement-object*/
			GetStringFromJson($sqlJson,"delete");
		}
		
		return $res;
	}
	
	
	
	public function SaveRow(&$sqlJson,&$value,&$result){
		/*print_r($sqlJson);echo "<br><br>";*/
		/*print_r($GLOBALS["value"]);
		/*1. Initialize*/
		
		if(isset($sqlJson)) $sqlJson->success=false;
		$db=$GLOBALS["db"];
		if(!isset($sqlJson->outputo)) $sqlJson->outputto="php";
		$validate=isset($sqlJson->validate)?$sqlJson->validate:"eirene_users";
		if(isset($sqlJson->permission) && $validate!="none"){
			if($sqlJson->permission!=3){
				$sqlJson->error="Permission Conflict2";
				return false;
			}
		}
		$ux=new EireneUx();$seperator=",";$param=isset($sqlJson->param)?$sqlJson->param:"";
		$paramvalue=isset($value["paramvalue"])?$value["paramvalue"]:"";
		
		/*2. Custom Save*/
		if(isset($sqlJson->customsave)){			
			/*php statments written in json will be processed here*/
			/*if customsave is specified, then normal saving will not take place.*/
			//echo '<textarea>'.$sqlJson->customsave.'</textarea>';			
			$sqlJson->customsave=str_replace("&apos;","'",$sqlJson->customsave);
			$sqlJson->customsave=str_replace("&quot;",'"',$sqlJson->customsave);
			//print_r($sqlJson->customsave);echo "<br><br>";
			//$sqlJson->customsave=implode("\n",$sqlJson->customsave);
			//$sqlJson->customsave=json_encode($sqlJson->customsave);
			$sqlJson->customsave=GetStringFromJson($sqlJson,"customsave","\n");			
			$sqlJson->customsave=str_replace("&apos;","'",$sqlJson->customsave);
			//echo '<textarea>'.$sqlJson->customsave.'</textarea>';
			if(eval($sqlJson->customsave)){$sqlJson->success=true;}
			return true;
		}		
		/*3. Save with old method - Depriciated*/
		if(empty($sqlJson->command)){
			$command=$value["command"];
			$tablename=$value["tablename"];
			$fields=$value["fields"];
			$fieldtypes=$value["fieldtypes"];
			$values=$value["values"];				
			$output="";
			$outputcontent="";
			if(isset($value["whr"]))
				$whr=$value["whr"];
			else
				$whr="";
		}
		
		/*4. Save with new method*/
		if(!empty($sqlJson->command)){
			/*4a - Initialize*/
			$command=isset($sqlJson->command)?$sqlJson->command:"";
			$tablename=isset($sqlJson->tbl)?$sqlJson->tbl:"";
			$fields=isset($sqlJson->fld)?$sqlJson->fld:"";			
			$fieldtypes=isset($sqlJson->fldtype)?$sqlJson->fldtype:"";			
			if(isset($sqlJson->paramseperator)) $seperator=$sqlJson->paramseperator;
			if(isset($sqlJson->seperator)) $seperator=$sqlJson->seperator;
			if($seperator=="chr(1)") $seperator=chr(1);
			$whr=isset($sqlJson->whr)?$sqlJson->whr:"";			
			//$whr=$ux->SupplyParamValue($whr);
			$whr=GetStringFromJson($sqlJson,"whr"," ");
			$tablename=$ux->SupplyParamValue($tablename,$sqlJson);
			
			/*arrays of field,fieldtype and values*/
			if(is_string($fields)) $fields=explode(",",$fields);
			if(is_string($fieldtypes)) $fieldtypes=explode(",",$fieldtypes);
			if(is_string($sqlJson->value)) $values=explode(",",$sqlJson->value);			
			//$values=GetStringFromJson($sqlJson,"value",",",false,false);
			//echo $sqlJson->value."<br>";
			//return false;
			
			/*4b- Insert|Update|InsertorUpdate*/
			if(empty($whr)) $sqlJson->command="insert";
			if($sqlJson->command=="insertorupdate"){				
				$whr1=" WHERE ".$whr;				
				$sql1="SELECT count(*) FROM $tablename $whr1";				
				$res=$db->GetValue($sql1);				
				if($res>=1){
					$command="update";
				}else{
					$command="insert";
				}
			}
			if($sqlJson->command=="insert"){				
				if(!in_array("createdby",$fields)){
					$fields[]="createdby";
					$fieldtypes[]="u";
					$values[]="||userid||";					
				}
			}elseif($sqlJson->command=="update"){				
				if(!in_array("modifiedby",$fields)){
					$fields[]="modifiedby";
					$fieldtypes[]="u";
					$values[]="||userid||";
				}				
			}
			
			/*4c- Treatment of value*/
			if(isset($sqlJson->value)){				
				//echo $sqlJson->sqlid." = <br>value1=";print_r($value)."<br>";				
				/*supply values to field and values*/				
				foreach($fields as $k=>$f){
					$fields[$k]=$ux->SupplyParamValue($f,$sqlJson);
				}
				foreach($values as $k=>$v){
					$values[$k]=trim($ux->SupplyParamValue($v,$sqlJson));
					/*eliminate values where variables values are not supplied*/
					if(substr($values[$k],0,2)=="||" && substr($values[$k],-2)=="||"){
						unset($values[$k]);
						unset($fields[$k]);
						unset($fieldtypes[$k]);
					}
				}
								
				if(count($values)!=count($fieldtypes) || count($values)!=count($fields)){
					$sqlJson->error="$tablename Field(".count($fields)."), Field Types(".count($fieldtypes).") or Values(".count($values).") are not matching1";
					//print_r($fields);echo "<br>";
					//print_r($fieldtypes);echo "<br>";
					//print_r($values);echo "<br><br>";
					//print_r($sqlJson);
					return false;
				}				
			}					
		}	
		//print_r($values);echo"<br>";
		//print_r($fields);echo"<br>";
		//print_r($fieldtypes);echo"<br>";
		//print_r($GLOBALS["value"]);
		/*5. Save*/
		$res=$db->SaveTable($command,$tablename,$fields,$fieldtypes,$values,$whr);
		//print_r($values);
		//echo "<textarea>".$db->Sql."</textarea>";
		//print_r($GLOBALS["globalphp"]);echo '<br>';
		$error="";
		
		/*6. Error handling and output*/
		if($db->error!=""){
			$sqlJson->error=$db->error."<br>";
			/*setOutput("error",$result,$db->error."\n".$db->Sql,$sqlJson);*/
			return false;
		}else if(is_bool($res)==false){
			$sqlJson->error=$res."<br>";
			/*setOutput("error",$result,$res,$sqlJson);*/
			return false;
		}else{
			$msg=$res;			
			if($res) $sqlJson->success=true;
			//print_r($db);
			
			$sqlJson->output="lastinsertid";
			$sqlJson->outputto="html";
			setOutput("lastinsertid",$result,$db->lastInsertId,$sqlJson);
			
		}
		return $res;
	}	
	public function SaveNew(&$sqlJson){		
		$sqlJson->success=false;
		$dict=array();		
		$ux=new EireneUx();
		if(isset($this->sqlClass->TableNameAndAlias)){
			$tbldef=$this->sqlClass->TableNameAndAlias[0][2];
		}		
		if(!isset($tbldef->fields)) return false;
		if(empty($GLOBALS["value"]["id"]) && empty($GLOBALS["value"]["ID"]))
			$isInsert=true;
		else
			$isInsert=false;
		
		foreach($tbldef->fields as $f){
			$tempval="";
			$tempnm=$f->name;
			if(isset($sqlJson->addedvariables[$tempnm])) $tempval=$sqlJson->addedvariables[$tempnm];
			/*saveif - check conditions for saving*/
			if(isset($f->saveif)){				
				if(is_object($f->saveif))
					$saveif=array($f->saveif);
				else if(is_string($f->saveif)){					
					$saveif=array($f->saveif);
				}else if(is_array($f->saveif))
					$saveif=$f->saveif;
				$check=true;
				$saveErrorMessage="";
				foreach($saveif as $sv){
					if(is_string($sv)){
						$if=json_decode("{}");
						$if->if=$if->saveif;
						$sv=$if;
					}
					/*this refers to alias of the field*/
					/*thisvalue refers to valuesupplied for the specific field*/
					$GLOBALS["value"]["this"]=$f->alias;
					if(isset($GLOBALS["value"][$f->alias]))
						$GLOBALS["value"]["thisvalue"]=$GLOBALS["value"][$f->alias];
					else
						$GLOBALS["value"]["thisvalue"]="";
					if(isset($sv->applicableto) && $sv->applicableto=="insert" && $isInsert==false){
						break;
					}else if(isset($sv->applicableto) && $sv->applicableto=="update" && $isInsert==true){
						break;
					}else if(isset($sv->if)){						
						/*saveif will be if-object for e.g. "saveif":{"if":"||this|| > 0","and":"||this|| < 10"}*/
						if(isset($sv->if)) $sv->if=str_ireplace("||this||","||".$f->alias."||",$sv->if);
						if(isset($sv->and)) $sv->and=str_ireplace("||this||","||".$f->alias."||",$sv->and);
						if(isset($sv->or)) $sv->or=str_ireplace("||this||","||".$f->alias."||",$sv->or);
						foreach($sqlJson as $k=>$v){
							if(in_string('output,outputto,action,sql,required,alias,tbl,vsum,fld,whr,srt,join,having,onsuccess,onfailure,formname,getform,validate,view_rights,tableid,primarytablenamealias,if,and,or,value,value1',$k)) continue;
							if(!is_string($v) && !is_numeric($v)) continue;
							if(isset($tempjson->$k)) continue;
							$sv->$k=$v;
						}
						if(isset($sqlJson->addedvariables)) $sv->addedvariables=$sqlJson->addedvariables;
						$res=$this->CheckIfCondition($sv,$GLOBALS["value"],$GLOBALS["result"]);
						if($res==false){
							$check=false;
							if(isset($sv->errormsg)) $saveErrorMessage=$sv->errormsg;
							//print_r($sv);echo "<br>";
							//print_r($GLOBALS["value"]);echo "<br>";
							break;
						}
					}else if(is_object($sv)){						
						$sqlJson->temprun=$sv;
						//if(isset($sqlJson->addedvariables)) $tempjson->addedvariables=$sqlJson->addedvariables;
						//print_r($tempjson->test);echo "<br>";
						GetStringFromJson($sqlJson,"temprun");
						//print_r($GLOBALS["value"]);echo "<br><br>";
					}
				}
				if($check==false){
					$label="";
					if(isset($f->label)) $label=str_replace(":","",$f->label);
					if(!empty($saveErrorMessage))
						$sqlJson->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed. ".$saveErrorMessage;
					else
						$sqlJson->onfailure="cmd:dom,fun:showtoast;alert;Saving Failed.".$label." Saving condition is not fulfilled.";
					return false;
				}
			}
			
			/*Default value*/
			if(empty($tempval) && isset($f->default)){				
				if(is_string($f->default) && $isInsert==true){
					$sqlJson->default=$f->default;
					$tempval=GetStringFromJson($sqlJson,"default");					
				}else if(is_object($f->default)){
					/*default can take two node, insert and update. values of both nodes must be string*/
					if(isset($f->default->insert) && (empty($GLOBALS["value"]["id"]) && empty($GLOBALS["value"]["ID"]))){
						/*insert*/
						$sqlJson->insert=$f->default->insert;
						$tempval=GetStringFromJson($sqlJson,"insert");
					}else if(isset($f->default->update) && (isset($GLOBALS["value"]["id"]) || isset($GLOBALS["value"]["ID"]))){
						/*update*/
						$sqlJson->update=$f->default->update;
						$tempval=GetStringFromJson($sqlJson,"update");
					}
				}
				if(stripos($tempval,"select ")!==false && stripos($tempval,"from ")){
					$db=$GLOBALS["db"];
					$temp=$db->GetValue($tempval);
					if(empty($db->error)) $tempval=$temp;
				}else if(strpos($tempval,"(") && strpos($tempval,")") && strpos($tempval," ")===false){
					$f->type="int";
				}				
			}
			/*pass value to dict for saving*/
			if($tempval!=""){				
				$tempval=str_replace("'","''",trim($tempval));
				if($tempval=="null")
					$tempval="null";
				else if(substr($tempval,-2)=="()" && strpos($tempval," ")===false){
									
				}else if(strpos($f->type,"int")!==false){
					
				}else{					
					$tempval="'".$tempval."'";
				}				
				$sqlJson->tempval=$tempval;
				$dict[$f->name]=GetStringFromJson($sqlJson,"tempval");				
			}
		}
		
		if(!empty($dict)){
			$id="";
			if(!empty($GLOBALS["value"]["id"])) 
				$id=$GLOBALS["value"]["id"];
			else if(!empty($GLOBALS["value"]["ID"])) 
				$id=$GLOBALS["value"]["ID"];
			if($isInsert==true && !empty($GLOBALS["userinfo"]["id"])) 
				$dict["createdby"]="'".$GLOBALS["userinfo"]["id"]."'";
			else if($isInsert==false && !empty($GLOBALS["userinfo"]["id"])) 
				$dict["modifiedby"]="'".$GLOBALS["userinfo"]["id"]."'";
			$db=$GLOBALS["db"];	
			$sqlJson->whr=GetStringFromJson($sqlJson,"whr"," ");
			if($db->Save($id,$dict,$sqlJson->whr,$sqlJson->tbl)) $sqlJson->success=true;
			//echo $id;
			//echo '<textarea>'.$db->Sql.'</textarea>';print_r($GLOBALS["value"]);
			if(!empty($db->error)){
				$sqlJson->error=$db->error."<br>";
				return false;
			}
			
			/*trigger*/
			/*"trigger":{"insert":{},"update":{},"delete":{}}*/
			if(isset($sqlJson->trigger)){				
				if(stripos($db->Sql,"update ")!==false){
					if(isset($sqlJson->trigger->update)){
						$sqlJson->update=$sqlJson->trigger->update;
						/*updatetrigger must be sqlstatement-object*/
						GetStringFromJson($sqlJson,"update");
					}
				}else if(stripos($db->Sql,"insert ")!==false){					
					if(isset($sqlJson->trigger->insert)){
						$sqlJson->insert=$sqlJson->trigger->insert;
						/*updatetrigger must be sqlstatement-object*/
						GetStringFromJson($sqlJson,"insert");
					}
				}
			}
		} 
	}
	public function SaveRowsFromCSV(&$sqlJson){
		/*multiple insert statement from CSV string*/
		/*following nodes are needed : tbl,fld,fldtype,value,csvstring*/
		$ux=new EireneUx();
		$sqlJson->success=false;
		$datasource=GetStringFromJson($sqlJson,'csvstring');		
		$datasource=str_getcsv($datasource, "\n");
		if(!isset($sqlJson->command)) $sqlJson->command="insert";
		$val=GetStringFromJson($sqlJson,"value","",false,false);
		$whr=GetStringFromJson($sqlJson,"whr"," ",false,false);
		$db=$GLOBALS["db"];
		if($sqlJson->command=="insert"){
			$sql="INSERT INTO ".$sqlJson->tbl." (".$sqlJson->fld.") VALUES ";
			$valuearray=array();			
			$cnt=0;
			foreach($datasource as $ll){
				$cnt++;
				$ll=explode(",",$ll);
				$val1=$val;
				foreach($ll as $k=>$v){
					$val1=str_replace("||$k||","$v",$val1);
				}
				/*replace loopcount variable*/
				if(stripos($val1,"||loopcount||")!==false){
					$val1=str_replace("||loopcount||",$cnt,$val1);
				}
				$valuearray[]="(".$val1.")";
			}
			$valuearray=implode(",",$valuearray).";";
			$sql.=$valuearray;
			//echo '<textarea>'.$sql.'</textarea>';			
			$sql=$ux->SupplyParamValue($sql);
			$db->execute($sql);
		}else if($sqlJson->command=="insertorupdate" && isset($sqlJson->whr)){
			$fld=GetStringFromJson($sqlJson,"fld",",");			
			$fld=explode(",",$fld);
			$fldtype=explode(",",$sqlJson->fldtype);			
			//$val=explode(",",$val);				
			foreach($datasource as $ll){
				$ll=explode(",",$ll);
				$val1=$val;	
				$whr1=$whr;	
				foreach($ll as $i=>$iv){
					$val1=str_replace("||$i||",$iv,$val1);
					$whr1=str_replace("||$i||",$iv,$whr1);									
				}
				$val1=$ux->SupplyParamValue($val1);
				$val1=explode(",",$val1);
				$whr1=$ux->SupplyParamValue($whr1);
				$res=$db->SaveTable($sqlJson->command,$sqlJson->tbl,$fld,$fldtype,$val1,$whr1);
			}
		}
		if(empty($db->error)) 
			$sqlJson->success=true;
		else{
			print_r($GLOBALS["globalphp"]);echo '<br>';
			echo $db->error;
		}
	}
	public function GetCarousel(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		$str="<div data-role='carousel' data-effect-func='easeInQuart'
				 data-controls-on-mouse='true' data-cls-controls='fg-white'";
				 
				/*carousel effect*/
				if(!isset($sqlJson->effect))
					$str.=" data-effect='slide'";
				else{
					if($sqlJson->effect=="slide" || $sqlJson->effect=="slide-v" || $sqlJson->effect=="switch" || $sqlJson->effect=="fade")
						$str.=" data-effect='".$sqlJson->effect."'";
					else
						$str.=" data-effect='slide'";
				}
				
				/*carousel auto start by default*/
				if(!isset($sqlJson->autostart))
					$str.=" data-auto-start='true'";
				else{
					if($sqlJson->autostart==true) $str.=" data-auto-start='true'";
				}
				
				/*data height*/
				if(isset($sqlJson->height))
					$str.=" data-height='".$sqlJson->height."'";
				
				/*carousel showbullet by default*/
				if(!isset($sqlJson->showbullet))
					$str.=" data-bullets='true'";
				else{
					if($sqlJson->showbullet) 
						$str.=" data-bullets='true'";
					else
						$str.=" data-bullets='false'";
				}
				/*carousel bulletstyle */
				if(!isset($sqlJson->bulletstyle))
					$str.=" data-bullets-style='square'";
				else{
					/*permissible are: circle,square,rect,diamond */
					$str.=" data-bullets-style='".$sqlJson->bulletstyle."'";					
				}
				
				/*carousel bulletsize*/
				if(isset($sqlJson->bulletsize)){
					/*permissible are: mini|small|default|large*/
					$str.=" data-bullets-size='".$sqlJson->bulletsize."'";					
				}
				
				/*carousel bulletposition*/
				if(isset($sqlJson->bulletposition)){
					/*permissible are: left|right|center*/
					$str.=" data-bullets-position='".$sqlJson->bulletposition."'";					
				}
				
				/*carousel showbullet by default*/
				if(!isset($sqlJson->showcontrol))
					$str.=" data-controls='true'";
				else{
					if($sqlJson->showcontrol==true) 
						$str.=" data-controls='true'";
					else
						$str.=" data-controls='false'";
				}
					
				$str.=">";
				if(isset($sqlJson->slides)){					
					foreach($sqlJson->slides as $s){
						$attrib="";
						$style=array();
						if(is_object($s)){
							if(isset($s->image)) $attrib.=" data-cover='".$s->image."'";
							if(isset($s->duration)) 
								$attrib.=" data-period='".$s->duration."'";
							else
								$attrib.=" data-period='3000'";
						}else if(is_string($s)) 
							$attrib=" data-cover='".$s."'";				
						
						
						$str.="<div class='slide'";
						if(isset($sqlJson->borderradius)){
							$style[]="border-radius:".$sqlJson->borderradius;
							//$str.=" style='border-radius:".$sqlJson->borderradius."'";
						}
						/*
						if(isset($sqlJson->height)){
							$style[]="height:".$sqlJson->height."'";
						}
						*/
						if(!empty($style)) $str.=" style='".implode(";",$style)."'";
						$str.=" ".$attrib.">";
							if(isset($s->text)) $str.=$s->text;
						$str.="</div>";
					}
				}else if(isset($sqlJson->tbl)){					
					$db=$GLOBALS["db"];
					$lst=$db->GetList1($sqlJson->sql,true);
					
					if(!empty($lst)){
						foreach($lst as $v){
							$attrib="";
							if(is_array($v)){
								if(isset($v["image"])) $attrib.=" data-cover='".$v["image"]."'";
								if(isset($v["duration"])) 
									$attrib.=" data-period='".$v["duration"]."'";
								else
									$attrib.=" data-period='3000'";
							}else if(is_string($v)) 
								$attrib=" data-cover='".$v."'";
							
							$str.="<div class='slide'";
							if(isset($sqlJson->borderradius)) $str.=" style='border-radius:".$sqlJson->borderradius."'";
							$str.=" ".$attrib.">";
								if(isset($v["text"])) $str.=$v["text"];
							$str.="</div>";
						}
					}
				}
		$str.="</div>";
		$sqlJson->success=true;	
		setOutput("",$result,$str,$sqlJson);	
			
	}
	
	public function GetTable(&$sqlJson,$value,&$result){		
		/*1. initialize*/
		$sqlJson->success=false;		
		$res1="{}";
		$res1=json_decode($res1);
		$displayfunctionbutton=true;
		if(!isset($sqlJson->datatable)) $sqlJson->datatable=true;
		if(!isset($sqlJson->class)) $sqlJson->class="eirene-table dataTable";
		$db=$GLOBALS["db"];		
		
		/*2. set variable associated with table*/
		if(isset($sqlJson->getform)&& empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"])){			
			$sqlJson->associatedTableDetails=json_decode("{}");			
			//$sqlJson->associatedTableDetails->data["tablename"]=$sqlJson->associatedTableDetails->tablename;
			$sqlJson->associatedTableDetails->data=$GLOBALS["value"];
			$sqlJson->associatedTableDetails->sqlid=$sqlJson->sqlid;		
			if(isset($sqlJson->getform)) $sqlJson->associatedTableDetails->getform=$sqlJson->getform;		
			if(isset($sqlJson->formname)) $sqlJson->associatedTableDetails->getform=$sqlJson->formname;		
		}
		
		/*3. Execute Sql to get data*/		
		$table=$db->GetTable($sqlJson);
		//print_r($sqlJson);
		if(empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"]))
			$table="<div id='".$sqlJson->tableid."Table' class='table'>".$table;
		/*convert table into datatable*/
		if($sqlJson->outputto=="html" && $sqlJson->datatable){		
			$table.="<script>datatable('#".$sqlJson->tableid."Table')</script>";			
		}
		$table.="</div>";		
		if(!empty($db->error)){
			$sqlJson->error=$db->error;			
			return false;
		}		
		if(!empty($table)) $sqlJson->success=true;
		//print_r($GLOBALS["value"]);		
		//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$sqlJson->sql).'</textarea><br>';
		/*
		if($sqlJson->sqlid=="solt1"){
			echo "<textarea>".$sqlJson->sql."</textarea>";
			echo "<bR>";
			print_r($GLOBALS["value"]);echo "<bR>";
		}*/
		
		/*4. Get Form,filter and function buttons*/
		$tablefunction=$this->GetTableFunctionButton($sqlJson);
		$formdef="";
		$tableform=$this->GetForm($sqlJson,$formdef);		
		$tablefilter=$this->GetTableFilter($sqlJson);			
		$tablechart=$this->GetTableFilter($sqlJson,true);			
		$res=$tablefunction.$tablefilter.$tablechart.$tableform.$table;
				
		/*5. wrapping table in div*/
		$res="<div id='".$sqlJson->tableid."'>".$res."</div>";
				
		/*6. table output*/		
		setOutput("table",$result,$res,$sqlJson);
		
		/*7. Update table function link button when a filter is set.*/
		if(!empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"])){
			$ux=new EireneUx();
			$linkbtn=$ux->SupplyParamValue("<button class='mif-link  fg-green' onclick='copy(&apos;https://cmch.niea.in/cmchapp.php?needlogin=false&pluginid=||pluginid||");
			$linkbtn.="&var".$sqlJson->sqlid."_outputto=html&var".$sqlJson->sqlid."_output=table";
			$linkbtn.="&stmt=".$sqlJson->sqlid;
			$linkbtn.="&var".$sqlJson->sqlid."_whr=".str_replace("'","_apos_",$sqlJson->whr);		
			$linkbtn.="&apos;)'></button>";
			setOutput("#".$sqlJson->tableid."FunctionButtons .linkbtn",$result,$linkbtn,"");
		}
		return $res;
	}
	private function GetTableFunctionButton($sqlJson,$forced=false){
		/*1. initialize*/
		$ux=new EireneUx();		
		$displayfunctionbutton=true;
		if(isset($sqlJson->displayfunctionbutton)) $displayfunctionbutton=$sqlJson->displayfunctionbutton; 
		$str="";
		if(empty($sqlJson->sqlid)) return $str;
		if(!empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"]) && $forced==false) return $str;
		if($displayfunctionbutton==false) return $str;
			
		/*2. Build HTML*/
		$str="<div id='".$sqlJson->tableid."FunctionButtons' class='no-print functionbuttons'>";				
		$str.="<button class='mif-refresh fg-green' title='Refresh' onclick='";
		$str.="Eirene.runStmt(\"".$sqlJson->sqlid."\",{";
		$str.=$this->GetVariablesForUseInUserFunction($sqlJson);			
		$str.="})";
		$str.="'></button>";
		
		/*copy table button*/
		$str.="<button class='mif-copy fg-green' title='Copy' onclick='copyHTML(&apos;".$sqlJson->tableid."Table&apos;)'></button>";
		/*print table button*/
		$str.="<button class='mif-print fg-green' title='Print' onclick='printHTML1(&apos;".$sqlJson->tableid."Table&apos;)'></button>";
		/*add filter button here*/
		if(isset($sqlJson->filter)){
			$str.="<button class='mif-filter fg-green' title='Filter' onclick='$(&apos;#".$sqlJson->tableid."Filter&apos;).toggle(&apos;slow&apos;)'></button>";
		}
		/*add chart button here*/
		if(isset($sqlJson->getchart)){
			$str.="<button class='mif-chart-bars icon' title='Chart' onclick='$(&apos;#".$sqlJson->tableid."Chart&apos;).toggle(&apos;slow&apos;)'></button>";
		}
		/*Table Link*/
		$str.=$ux->SupplyParamValue("<span class='linkbtn'><button class='mif-link  fg-green' onclick='copy(&apos;https://cmch.niea.in/cmchapp.php?needlogin=false&pluginid=||pluginid||");
		$str.="&var".$sqlJson->sqlid."_outputto=html&var".$sqlJson->sqlid."_output=table";
		$str.="&stmt=".$sqlJson->sqlid;
		$str.="&var".$sqlJson->sqlid."_whr=".$sqlJson->whr;		
		$str.="&apos;)'></button></span>";
		$str.="</div>";		
				
		return $str;
	}
	
	private function GetTableFilter($sqlJson,$isChart=false){
		if($isChart && empty($sqlJson->getchart)) return "";
		
		/*This function will provide a form from where queries to filter the table can be run*/
		/*1. Initialize*/		
		if(!empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"])) return "";
		if(empty($sqlJson->filter)) return "";
		if(empty($sqlJson->tbl)) return "";	
		$filter=$sqlJson->filter;		
		$chartid=isset($sqlJson->getchart)?explode(",",$sqlJson->getchart):array("");
		if($isChart==false) $chartid=array($sqlJson->sqlid);
		$ux=new EireneUx();
		$html=new EireneHTMLBuilder();
		$tableid=$sqlJson->tableid;
		$elemName=!$isChart?$tableid."Filter":$tableid."Chart";
		
		/*1a. Build Tab menu for charts*/		
		$chartStr=array();
		$tabmenu=array();		
		foreach($chartid as $i=>$chrt){
			$str="";			
			if($isChart){
				$sqlJson->sqlid=$chrt;
				$tempsql="SELECT sql_statement FROM eirene_sqlstatements where pluginid='".$GLOBALS["plugin"]["id"]."' and customid='".$chrt."'";
				$tempres=$GLOBALS["db"]->GetValue($tempsql);
				$tempres=json_decode($tempres);
				$filter=isset($tempres->filter)?$tempres->filter:"";
				$tabmenu[$chrt]=isset($tempres->name)?$tempres->name:"Chart ".$i+1;				
			}		
			$filterfields=array();		
			$filterfieldobjects=array();			
			if($isChart) $elemName="chrt_".$chrt;
			
			/*2. Build filter field list*/
			if(is_string($filter)){
				$filterfields=explode(",",$filter);
				if(isset($this->sqlClass->TableNameAndAlias[0]))
					$tempprimarytablealias=$this->sqlClass->TableNameAndAlias[0][1];
				else
					$tempprimarytablealias="";
				foreach($filterfields as &$f){
					if(strpos($f,".")===false){
						$f=$tempprimarytablealias.$f;
					}
				}			
			}else if(is_array($filter)){
				foreach($filter as $f){
					if(is_string($f)){
						$ff=explode(",",$f);
						foreach($ff as $fff)
							$filterfields[]=$fff;
					}else if(is_object($f))
						$filterfieldobjects[]=$f;
				}
			}			
			
			/*4. Build Filter Form*/		
			if(!$isChart){
				$str="<div id='".$sqlJson->tableid."Filter' class='tablefilter' style='display:none'>";
				$str.="<div data-role='panel' data-cls-panel='inherit-colors' data-title-icon='<span class=&apos;mif-filter&apos;></span>' data-title-caption='Filter' data-collapsible='true'>";
			}		
			$str.="<div class='inline-form'>";
			
			/*4a. Get Field as HTML Object*/
			$tempdata=array();
			$sortopt=array();
			$sortopt[]="";
			$filterfieldsdone=array();
			foreach($this->sqlClass->TableNameAndAlias as $tna){
				if(empty($tna[2]->fields)) continue;				
				if(count($filterfields) == count($filterfieldsdone)) break;				
				if(!empty($tna[2]->fields)){				
					foreach($tna[2]->fields as $k=>$v){
						/*Sort Option*/
						if(!empty($v->alias) && !empty($v->label))
							$sortopt[]=$v->alias.":".$v->label;
						else if(empty($v->alias) && !empty($v->label))
							$sortopt[]=$v->name.":".$v->label;
						else if(empty($v->alias) && empty($v->label))
							$sortopt[]=$v->name.":".$v->name;
						if(count($filterfields) == count($filterfieldsdone)) break;
						/*Filter field*/					
						if(in_array($tna[1].$v->name,$filterfields) && !in_array($tna[1].$v->name,$filterfieldsdone)){
							//echo $v->name." = ".$v->type."<br>";
							$filterfieldsdone[]=$tna[1].$v->name;
							$str.=$this->GetTableFilterField($v,$elemName,$tempdata,$html);							
						}
					}					
				}				
			}
			/*4b. Get custom filter objects as HTML Object*/
			if(!empty($filterfieldobjects)){
				foreach($filterfieldobjects as $f){
					if($f->fieldtype=="select") $f->addemptyvalue=1;
					$str.=$this->GetForm_GetField($f,"3 Columns",$html);
					$tempdata[]=$f->name.":$(&apos;#".$elemName." [name=".$f->name."]&apos;).val()";
				}
			}
			$str.="</div>";		
			/*5. sort options/search button/Chart button*/
			if(!$isChart){
				$str.="<div><div class='pt-5'>Sort</div><hr class='border bd-blue'/>";
				$str.="<div class='row pl-2'>";		
				$str.="<select name='".$sqlJson->tableid."FilterSort' class='cell-3'>";
				$str.=FillDropdownHTML(implode(",",$sortopt));
				$str.="</select>";
				$str.="<select style='float:left' class='mr-2 cell-2' name='".$sqlJson->tableid."FilterSort1'><option></option><option value='asc'>Ascending</option><option value='desc'>Descending</option></select>";		
				$tempdata[]="var".$sqlJson->sqlid."_srt:$(&apos;#".$elemName." [name=".$elemName."Sort]&apos;).val()+&apos; &apos;+$(&apos;#".$elemName." [name=".$elemName."Sort1]&apos;).val()";
				$str.="</div></div>";
				$str.="<div class='cell-6' style='text-align:right'>";//echo $chrt." = "."mif-search<br>";
				$str.=$this->GetTableFilterButton($sqlJson,$chrt,$tempdata,"mif-search",$isChart);
				$str.="</div>";
			}else{				
				$str.="<div class='cell-6' style='text-align:right'>";
				$str.=$this->GetTableFilterButton($sqlJson,$chrt,$tempdata,"mif-chart-bars",$isChart);
				$tempdata[]="var".$chrt."_displaytable:1";
				$str.=$this->GetTableFilterButton($sqlJson,$chrt,$tempdata,"mif-table",$isChart);
				$str.="</div>";
			}
			
				
			/*Close all divs*/
			if(!$isChart)
				$str.="</div></div>";
			if($isChart){
				$str="<div id='chrt_$chrt'>$str</div>";
				$chartStr[]=$str;
			}
		}
		if($isChart){
			$tabmenu1="<div id='".$sqlJson->tableid."Chart' class='chartfilter' style='display:none'>";
			$tabmenu1.="<ul data-role='tabs' data-expand='true'>";
			foreach($tabmenu as $k=>$v){
				$tabmenu1.="<li><a href='#chrt_".$k."'>";
				$tabmenu1.=$v."</a></li>";		
			}
			$tabmenu1.="</ul>";
			$str=$tabmenu1."<div class='border bd-default no-border-top p-2'>".implode("",$chartStr)."</div></div>";
		}
		return $str;
	}
	private function GetTableFilterField($fld,$parentelemid,&$tempdata,$html){
		/*Initialize*/
		$t1=json_decode("{}");
		$t1->name=$fld->alias;						
		$t1->label=isset($fld->label)?$fld->label:$fld->name;
		
		foreach($fld as $k1=>$v1){
			if($k1=="required" ||$k1=="name" || $k1=="label"|| $k1=="type"|| $k1=="alias") continue;
			$t1->$k1=$v1;
		}
		if(empty($fld->fieldtype) && isset($fld->type)) $t1->type=$fld->type;
		if($fld->type=="datetime") $t1->type="date";		
		if($fld->type=="select" ||(isset($fld->fieldtype) && $fld->fieldtype=="select")) $t1->addemptyvalue=1;
		if(stripos($fld->type,"enum")!==false) $t1->addemptyvalue=1;
		
		$str="";
		
		if($fld->type=="date" || $fld->type=="datetime" || $fld->fieldtype=="date"|| $fld->fieldtype=="datetime"){			
			$tempnm=$t1->name;
			$templbl=$t1->label;
			$t1->name.="fromdate";
			$t1->label.=" From";
			$str=$this->GetForm_GetField($t1,"3 Columns",$html);			
			$tempdata[]=$t1->name.":$(&apos;#".$parentelemid." [name=".$t1->name."]&apos;).val()";		
			$t1->name=$tempnm."todate";
			$t1->label=$templbl." To";
			$str.=$this->GetForm_GetField($t1,"3 Columns",$html);
			$tempdata[]=$t1->name.":$(&apos;#".$parentelemid." [name=".$t1->name."]&apos;).val()";		
		}else{
			$str=$this->GetForm_GetField($t1,"3 Columns",$html);			
			$tempdata[]=$t1->name.":$(&apos;#".$parentelemid." [name=".$t1->name."]&apos;).val()";		
		}
		return $str;
	}
	private function GetTableFilterButton($sqlJson,$sqlid,$tempdata,$icon,$isChart){
		$str="<button type='button' onclick='Eirene.runStmt(&apos;".$sqlid."&apos;,{";
		$str.=$this->GetVariablesForUseInUserFunction($sqlJson,$isChart);			
		$str.=",".implode(",",$tempdata);		
		$str.="})'><span class='$icon icon'></span></button>";
		return $str;
	}
	private function GetVariablesForUseInUserFunction($sqlJson,$isChart=false){
		/*Below functions will be used to receive global-values and convert into clicable user commands and autogenerate variables. Usage includes auto loading of tables after save or table refresh fuctions*/
		/*this functions returns a string*/		
		$datavalue=array();
		$tempsqlid=isset($sqlJson->sqlid)?$sqlJson->sqlid:"";
		
		if(empty($tempsqlid)) return "";
		foreach($GLOBALS["value"] as $k=>$v){
			if($k=="userid" || $k=="screenheight" || $k=="screenwidth"|| $k=="pluginid"|| $k=="stmt"|| $k=="silent" || strpos($k,"_output")) continue;
			if(is_string($v)) {
				$v=str_replace("var".$tempsqlid."_","",$v);
				//$datavalue[]="var".$tempsqlid."_".$k.":\"".$v."\"";
				$datavalue[]="var".$tempsqlid."_".$k.":&apos;".$v."&apos;";
			}
		}				
		$datavalue[]="var".$tempsqlid."_donotgenerateform:true";		
		$datavalue[]="var".$tempsqlid."_output:&apos;#".$sqlJson->tableid."Table&apos;";
		$datavalue[]="var".$tempsqlid."_outputto:&apos;html&apos;";
		$datavalue[]="var".$tempsqlid."_tableid:&apos;".$sqlJson->tableid."&apos;";
		//if(isset($sqlJson->tableid)) $datavalue[]="var".$tempsqlid."_tablename:\"".$sqlJson->tableid."\"";
		if(isset($sqlJson->associatedTableDetails->getform)) $datavalue[]="var".$tempsqlid."_getform:&apos;".$sqlJson->getform."&apos;";
		if(isset($sqlJson->includeeditdelete) && !$isChart) $datavalue[]="var".$tempsqlid."_includeeditdelete:".$sqlJson->includeeditdelete;
		$datavalue=implode(",",$datavalue);
		return $datavalue;
	}
	public function GetJSONForDropdown($sql){
		$db=$GLOBALS["db"];
		$res=$db->GetJSONDataForDropdown($sql);
		return $res;
	}
	
	function ExtractForSqlToArray($res){
		$cols=array();		
		if(empty($res))
			return $cols;
		$colBuildingDone=false;
		
		if(!$res[1]){		
			if (strpos($res[0][1],",")>-1){
				$vals=explode(",",$res[0][1]);
				foreach($vals as $key=>$val){					
					$cols[$val]=$val;					
				}
			}else{
				$cols[$res[0][0]]=$res[0][0];
			}
			$colBuildingDone=true;			
		}
		if($colBuildingDone==false){
			foreach($res as $key=>$val){									
				if(isset($val[1])){					
					$cols[$val[0]]=$val[1];
				}else{
					$cols[$val[0]]=$val[0];
				}			
			}
		}
		return $cols;
	}
	public function FindAndReturnField3InJSON($json,$field1Name,$field1Value,$field2Name,$field2Value,$field3Name){		
		$res="";
		foreach($json as $x){
			if($x[$field1Name]==$field1Value && $x[$field2Name]==$field2Value){
				$res=$x[$field3Name];
				break;
			}
		}
		return $res;	
	}
	public function UploadFile(&$sqlJson,$value,&$result,&$file){
		$sqlJson->success=false;
		//print_r($value);
		$kb=1024;
		$mb=1048576;
		$gb=1073741824;
		$tb=1099511627776;
		$output="";
		
		if(isset($value["stmt"])){					
			$permissiblesize=$sqlJson->maxsize;
			$path=trim(GetStringFromJson($sqlJson,"path"," "));			
			$permissiblefiletype=$sqlJson->filetype;
			$directory=$sqlJson->directory;
			if(!empty($sqlJson->output))
				$output=$sqlJson->output;
		}else{
			$permissiblesize=$value["documentpermissiblesize"];
			$path=$value["documentpath"];
			$permissiblefiletype=$value["documentpermissiblefiletype"];
			$directory=$value["directory"];
			$output="res";
		}		
	
		$path=trim($path);
		$backslash=substr($path,-1);
		if($backslash!="/")
			$path.="/";
		if(isset($directory)){
			if(empty($directory)==false){
				if(file_exists($path.$directory)==false){
					if(mkdir($path.$directory."/",0755)==true){
						$path.=$directory."/";
					}else{
						$sqlJson->error="Error creating directory";
						return false;
					}
				}
			}
		}		
		$path=str_replace("//","/",$path);
		$actualsize=$file["size"];
		/*calculate permissible size*/
		$permissiblesize1=$permissiblesize;
		$permissiblesize=strtolower($permissiblesize);
		if(strpos($permissiblesize,"kb")){
			$permissiblesize=intVal($permissiblesize);
			$permissiblesize=$permissiblesize*$kb;
		}else if(strpos($permissiblesize,"mb")){
			$permissiblesize=intVal($permissiblesize);
			$permissiblesize=$permissiblesize*$mb;
		}else if(strpos($permissiblesize,"gb")){
			$permissiblesize=intVal($permissiblesize);
			$permissiblesize=$permissiblesize*$gb;
		}else if(strpos($permissiblesize,"tb")){
			$permissiblesize=intVal($permissiblesize);
			$permissiblesize=$permissiblesize*$tb;
		}
		
		$file["name"]=strtolower($file["name"]);
		$file["name"]=str_replace(":","",$file["name"]);
		$file["name"]=str_replace(",","",$file["name"]);
		$file["name"]=str_replace(";","",$file["name"]);
		
		if($actualsize>$permissiblesize) {
			$sqlJson->error="Document exceeds ($actualsize) permissible limit of $permissiblesize1";			
			return false;
		}
		$target_file = $path .str_replace(",","",basename($file["name"]));
		$ext = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
		if(strpos(strtolower($permissiblefiletype),$ext)===false){
			$sqlJson->error="Document is different than permissiblefiletype ($permissiblefiletype)";
			return false;
		}
		
		$target_dir = $path;/*"resource/media/";*/
		
		$filename=str_replace(".".$ext,"",str_replace(",","",basename($file["name"])));
		$filename=str_replace("'","",$filename);
		$filename=strtolower(str_replace(" ","",$filename));
		for($i=0;$i<=20;$i++){
			$target_file = $target_dir . $filename.".".$ext;
			if (file_exists($target_file)){
			  $filename=$filename.rand(1,10000);
			}else{
			  break;	
			}
		}
		
		/*image compression*/
		if($ext=="jpg" || $ext=="jpeg" || $ext=="png" || $ext=="gif" || $ext=="bmp"){
			/*Create an image resource*/
			$im;
			if($ext=="jpg" || $ext=="jpeg")			
				$im = imagecreatefromjpeg($file["tmp_name"]);
			else if($ext=="png")
				$im = imagecreatefrompng($file["tmp_name"]);
			else if($ext=="gif")
				$im = imagecreatefromgif($file["tmp_name"]);
			else if($ext=="bmp")
				$im = imagecreatefrombmp($file["tmp_name"]);
			
			try{
				imagejpeg($im,$target_file, 75);
				imagedestroy($im);
				chmod($target_file,0755);
				$GLOBALS['globalphp']['uploadedfilename']=$target_file;
				setOutput("res",$result,"true".chr(1)."$target_file",$sqlJson);
				$sqlJson->success=true;
			}catch(Exception $e){
				$sqlJson->error="Sorry, there was an error uploading your file.";
				return false;
			}
		}else{
			$GLOBALS['globalphp']['uploadedfilename']=$target_file;
			if(move_uploaded_file($file["tmp_name"], $target_file)) {
				chmod($target_file,0755);
				setOutput("res",$result,"true".chr(1)."$target_file",$sqlJson);
				$sqlJson->success=true;
			}else{
				$sqlJson->error="Sorry, there was an error uploading your file.";			
			}
		}
		return $sqlJson->success;
	}
	public function DownloadAssignment($lessonid){
		$db=$GLOBALS['db'];
		//$sql="SELECT responses FROM online_course_read_tracker a INNER JOIN online_lesson b ON a.onlinecourse_lessonid=b.id INNER JOIN online_course c ON b.courseid=c.id INNER JOIN online_course_mycourse d ON c.id=d.courseid AND a.onlinecourse_userid=d.createdby  WHERE b.lesson_type='Upload Document Page' AND d.expirydate>='||TODAY||'";
		$sql="SELECT a.responses FROM online_course_read_tracker a INNER JOIN online_lesson b ON a.onlinecourse_lessonid=b.id INNER JOIN online_course c ON b.courseid=c.id INNER JOIN online_course_mycourse d ON c.id=d.courseid AND a.onlinecourse_userid=d.createdby INNER JOIN online_course_users e ON d.createdby=e.id  WHERE b.lesson_type='Upload Document Page' AND d.expirydate>='||TODAY||' and (a.responses is not null and a.responses!='') and a.onlinecourse_lessonid='".$lessonid."'";
		$res=$db->GetJSONData1($sql);
		$res=json_decode($res);
		$zip = new ZipArchive();
		$filename = "resource/archive.zip";
		if(file_exists($filename))
			unlink($filename);
		if ($zip->open($filename, ZipArchive::CREATE)!==TRUE)
		  return ("Error: cannot open archive file");
		
		foreach($res as $i=>$x){
			if(file_exists($x->responses))
				 $zip->addFile($x->responses,basename($x->responses));
		}
		$zip->close();
		if(file_exists($filename)){
			chmod($filename,0755);
			return $filename;
		}else{
			return "Error: No file exists";
		}
	}
	public function ZipFolder($folder,$directory){
		$zip = new ZipArchive();
		$filename = "resource/archive.zip";

		if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
		  exit("cannot open <$filename>\n");
		}

		$dir = "$folder/$directory/";

		// Create zip
		if (is_dir($dir)){

		 if ($dh = opendir($dir)){
		   while (($file = readdir($dh)) !== false){		 
			 // If file
			 if (is_file($dir.$file)) {
			   if($file != '' && $file != '.' && $file != '..'){
				 $zip->addFile($dir.$file,basename($dir.$file));
			   }
			 }
		 
		   }
		   closedir($dh);
		  }
		}
		$zip->close();

		return $filename;
	}
	public function GetUserForm(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		$db=$GLOBALS["db"];
		$ux=new EireneUx();
		/*
		$sql="SELECT table_def FROM eirene_plugin WHERE id='".$value["pluginid"]."'";		
		$res=$db->GetValue($sql);
		$res1=json_decode($res);
		if(isset($res))
		*/
		$sql="SELECT form FROM eirene_plugin WHERE id='".$value["pluginid"]."'";		
		$res=$db->GetValue($sql);
		
		if(empty($res)) return "";
		if(substr($res,0,1)==chr(123)) $res="[".$res."]";
		$res1=json_decode($res);
		$sqlJson->success=true;
		$str="";
		$formlist=array();
		if(!empty($sqlJson->formlist)) $formlist=explode(",",$sqlJson->formlist);
		
		foreach($res1 as $res){
			if(empty($res->name))
				return "";
			if(!empty($formlist)){				
				if(!in_array($res->name,$formlist)) continue;
			}
			if(!isset($res->title))$res->title="";
			$formtype=empty($res->formtype)?"Default":$res->formtype;
			$formname=str_replace(" ","_",strtolower($res->name));
			$result["formname"]=$formname;
			$flex_direction="d-flex";
			/*flex direction*/
			if(empty($res->flex_row)==false){
				if($res->flex_row=="false")
					$flex_direction="d-flex-column";
			}
			/*flex-justify*/
			$flex_justify=empty($res->flex_justify)?"around":$res->flex_justify;
			$flex_width=empty($res->flex_width)?"width:100%":$res->flex_width;
		
			/*icon*/
			if(empty($res->icon))
				$ico="";
			else{
				$ico=explode(" ",$res->icon);
				$ico=$ico[0];
			}
			
			/*build form string*/
			if(isset($res->savecustomid)) $res->saveid=$res->savecustomid;
			if(isset($res->getrecordcustomid)) $res->getid=$res->getrecordcustomid;
			$str.="<div class='form'  name='".$formname."' title='".$res->title."'><div id='".$formname."Form' formtype='".$formtype."' saveid='".$res->saveid."' getid='".$res->getid."' class='cell-fs-full win-shadow bd-gray'>";
				//$str.="<div class='window-caption ml-2'><span class='icon ".$ico."'></span><span class='title'>".$res->title."</span></div>";
				$str.="<div class='window-content p-2'>";
					//$str.="<div class='d-flex fontColorBGCombination'>Form View <input type='radio' data-role='radio' data-caption='Default' onclick='$(&apos;#".$formname."Form .form-group&apos;).addClass(&apos;row&apos;);$(&apos;#".$formname."Form label&apos;).addClass(&apos;cell-sm-2&apos;);$(&apos;#".$formname."Form .form-group div&apos;).addClass(&apos;cell-sm-10&apos;);' name='formview'><input type='radio' data-role='radio' onclick='$(&apos;#".$formname."Form .form-group&apos;).removeClass(&apos;row&apos;);$(&apos;#".$formname."Form label&apos;).removeClass(&apos;cell-sm-2&apos;);$(&apos;#".$formname."Form .form-group div&apos;).removeClass(&apos;cell-sm-10&apos;);' data-caption='Row' onclick='' name='formview'></div>";
					$str.="<div class='".$flex_direction." flex-wrap flex-justify-".$flex_justify."'>";
						$str.="<input type='hidden' field='true' name='id'  fieldType='s' value=''>";
						foreach($res->field as $x){						
							/*clear data*/
							$clearData="";
							$attrib="";
							
							/*short cuts*/
							if(!empty($x->nm)) $x->name=$x->nm;
							if(!empty($x->lbl)) $x->label=$x->lbl;
							if(!empty($x->ft)) $x->fieldtype=$x->ft;
							if(!empty($x->req)) $x->required=true;
							if(!empty($x->max)) $x->maxlength=$x->max;
							if(!empty($x->nul)) $x->allownull=$x->nul;
							if(!empty($x->opt)) $x->option=$x->opt;
							if(!empty($x->functionname)) $x->functionname="";							
							
							$required="";
							if(isset($x->required) || isset($x->req)) $attrib.=" required";
							//$required=" required";
							
							/*id*/
							$attrib.=empty($x->id)?" id='".$x->name."'":" id='".$x->id."'";							
							$fieldid=empty($x->id)?$x->name:$x->id;							
							$attrib.=empty($x->name)?"":" name='".$x->name."'";							
							$attrib.=empty($x->style)?"":" style='".$x->style."'";							
							$attrib.=empty($x->readonly)?"":" readonly";							
							/*js function */
							$attrib.=empty($x->jsevent)?"":" ". $x->jsevent."='".$x->jsfun."' ";
							if(isset($x->onclick)) $attrib.="onclick='".str_replace("'","&apos;",$x->onclick)."' ";
							if(isset($x->onchange)) $attrib.="onchange='".str_replace("'","&apos;",$x->onchange)."' ";
							if(isset($x->onblur)) $attrib.="onblur='".str_replace("'","&apos;",$x->onblur)."' ";
							if(isset($x->ondblclick)) $attrib.="ondblclick='".str_replace("'","&apos;",$x->ondblclick)."' ";
							
							
							/*maxlength*/
							$attrib.=empty($x->maxlength)?"":" maxlength='".$x->maxlength."'";
							
							if($x->type!="hidden"){
								$str.="<div ";
								$clsfrm="";
								if($formtype=="Default"){
									if($flex_direction=="d-flex")
										$clsfrm='w-100-fs w-50-xxl form-group';
									else
										$clsfrm="w-100 form-group";
								}else if($formtype=="Horizontal"){
									if($flex_direction=="d-flex")
										$clsfrm='inline-form w-100-fs w-50-xxl form-group';
									else
										$clsfrm="inline-form w-100 form-group";
								}

								$str.=" class='".$clsfrm."'>";
								/*required*/
								$x->label.=isset($x->required)?" *":"";
								$str.="<label class='cell-3' style='width:20%'>".$x->label."</label>";
							}
								
							/*jsonarray build*/
							$jsonArrayBuild=empty($x->jsonArrayBuild)?"":" onclick='jsonArrayFieldBuilder($(this))'  parentformname='".$formname."'  parentfieldname='".$x->name."' ";
							
							$default="";
							if(isset($x->default)){
								$default=$x->default;
								if(strpos($default,"||")!==false){
									$default=$ux->SupplyParamValue($default,$sqlJson);
								}
							}
							/*build field*/
							$str.="<div style='width:70%'>";
							if($x->type=="text"){
								$str.="<input type='text' class='w-100 metro-input' field='true' value='".$default."'".$attrib.">";
								$str.=empty($jsonArrayBuild)?"":"<button ".$jsonArrayBuild.">+</button>";							
							}else if($x->type=="number" || $x->type=="numeric"){
								$str.="<input type='number' class='w-100 metro-input' field='true' value='".$default."'".$attrib.">";
								$str.=empty($jsonArrayBuild)?"":"<button ".$jsonArrayBuild.">+</button>";							
							}else if($x->type=="password"){
								$str.="<input type='password' data-type='input' class='w-100 metro-input' field='true' value='".$default."'".$attrib.">";
							}else if($x->type=="signature"){
								$str.="<div class='w-100 metro-input signature' onchange='\$(&apos;input[name=".$x->name."]&apos;).val($(this).jSignature(&apos;getData&apos;,&apos;svg&apos;)[1]);'  name='".$x->name."_signature'></div>";
								$str.="<input type='hidden' class='signatureinput' field='true' ".$attrib."/>";
							}else if($x->type=="textarea"){
								$str.="<textarea type='text' class='w-100 metro-input' style='line-height:1.2'  field='true'".$attrib.">".$default."</textarea>";
								$str.=empty($jsonArrayBuild)?"":"<button ".$jsonArrayBuild.">+</button>";
							}else if($x->type=="texteditor"){
								$str.="<textarea  class='w-100 metro-input texteditor'  field='true'".$attrib.">".$default."</textarea>";
								$str.=empty($jsonArrayBuild)?"":"<button ".$jsonArrayBuild.">+</button>";
							}else if($x->type=="hidden"){
								$str.="<input type='hidden' class='w-100'  field='true'".$attrib." value='".$default."'>";
							}else if($x->type=="datepicker"){
								$str.="<input type='date'  data-type='input' class='w-100 metro-input'  field='true'".$attrib.">";
							}else if($x->type=="timepicker"){
								$str.="<input type='time'  data-type='input' class='w-100 metro-input'  field='true'".$attrib.">";
							}else if($x->type=="datetime"){
								$str.="<input type='datetime-local' fieldtype='datetime'  data-type='input' class='w-100 metro-input' field='true'".$attrib.">";
							}else if($x->type=="checkbox"){
								$str.="<input type='text' data-type='input' data-role='checkbox' class='w-100 metro-input' field='true'".$attrib.">";
							}else if($x->type=="select" || $x->type=="select3"){
								$opt=empty($x->option)?"":GetStringFromJson($x,"option");
								$cls=$x->type=="select"?"select1":"select3";								
								if($cls!="select3")
									$str.="<select type='select' data-role='select' class='".$cls." w-100'  field='true'".$attrib.">";
								else
									$str.="<select type='select' data-append='<span class=&apos;mif-plus&apos; onclick=&apos;let a=prompt(&quot;Write option you want to add.&quot;);let sel=$(&quot;#".$fieldid."&quot;).data(&quot;select&quot;);sel.data(&quot;<option value=&quot;+a+&quot;>&quot;+a+&quot;</option>&quot;)&apos; ></span>'  data-role='select' class='".$cls." w-100'  field='true'".$attrib.">";
								$commaseperatedvalue=false;
								if(isset($x->commaseperatedvalue)) $commaseperatedvalue=true;
								$str.=FillDropdownHTML($opt,$default,$commaseperatedvalue);
								
								$str.="</select>";								
							}else if($x->type=="audio"){
								if(isset($x->uploadstmt)==false) $x->uploadstmt="upload";
								$str.="<div><button class='button primary' name='".$x->name."_audiobtn' field='true' id='".$x->name."_audiobtn' onclick='Eirene.currentelem=$(this);recordAudio($(this).attr(\"id\"),\"".$x->uploadstmt."\",\"".$value["userid"]."\")'>Record/Stop Record </button></div>";
							}else if($x->type=="file"){
								$temp="<form id='||ID||' method='post' enctype='multipart/form-data'><div class='d-flex'>";							
								//$temp.="<input type='hidden' name='action' value='Upload File' />";
								$temp.="<input name='uploadfile' type='file' class='w-100-fs w-100-sm w-50-lg' ";
								if(isset($x->maxsize)) $temp.="onchange='checkFileSize(this,\"".$x->maxsize."\");$(\".progressBtn\").css(\"width\",\"0%\");' ";
								$temp.="/>";
								$temp.="<div style='width:80px;height:36px'><button type='button' style='width:80px;height:36px;z-index:2;' onclick='if($(\"input[name=".$x->name."]\").val()){Eirene.runStmt(\"remf\",{path:$(\"input[name=".$x->name."]\").val()});}let form = ||FORMELEM||;let formData = new FormData(form[0]);callAjax(||DATA||,||FUNCTIONNAME||,formData)'><div style='z-index:1;'>Upload</div><div style='display: block;position: absolute;left: 0px;top: 3px;height: 98%;margin: 1px;background-color:#4CAF50;width:0%;' class='progressBtn'></div></button></div></div></form>";
								
								//$temp.="<button type='button'  >Upload</button>";
								$temp.="<div>(You can compress your image file <a target='_blank' href='https://www.imgonline.com.ua/eng/compress-image-size.php'>here</a>)</div>";
								$temp=str_replace("||ID||","fileupload-".$x->name,$temp);
								$temp=str_replace("||FORMELEM||","$(\"#fileupload-".$x->name."\")",$temp);							
								if(isset($x->uploadstmt)==false) $x->uploadstmt="upload";
								$data2="{stmt:\"".$x->uploadstmt."\",userid:Eirene.userinfo.userid,pluginid:Eirene.activePluginId,elemname:\"".$x->name."\",formname:\"".$formname."Form\",silent:true}";
								$temp=str_replace("||DATA||",$data2,$temp);
								$functionname="";
								if(empty($x->functionname))
									$temp=str_replace("||FUNCTIONNAME||","\"\"",$temp);
								else
									$temp=str_replace("||FUNCTIONNAME||",'"'.$x->functionname.'"',$temp);
								$str.=$temp;
								$str.="<input type='hidden' class='file' field='true'".$attrib.">";
							}
							$str.="</div>";
							$str.=$x->type!="hidden"?"</div>":"";	
						}
					$str.="</div><br/><br/>";
					
					/*button*/
					if(!empty($res->saveButton))
						$str.=$res->saveButton==false?"":"<button  class='button primary mr-3' onclick='save(\"".$formname."\")'>Save</button>";
					/*custom buttons*/
					$custBtn=empty($res->custombutton)?"":$res->custombutton;
					if(!empty($custBtn)){
						if(is_string($custBtn)) $custBtn=json_decode($custBtn);
						foreach($custBtn as $b){
							$ico=empty($b->icon)?"":$b->icon;
							$str.="<button class='button ". $ico ."' onclick='".$b->onclick."'>".$b->caption."</button>";
						}
					}
					if(!empty($res->cancelButton))
						$str.=$res->cancelButton==false?"":"<button class='button secondary js-dialog-close' onclick='hideWindow(\"".$formname."\")'>Cancel</button>";
				$str.="<br/><br/></div>";
			$str.="</div></div>";
		}
		//if(isset($sqlJson->output)){
			//if($sqlJson->output!="form"){
				setOutput("form",$result,$str,$sqlJson);
			//}
		//}
		return $str;				
	}
	public function GetForm(&$sqlJson,&$formdefRes=""){
		/*formname can be string with formname space formnumber or an object with formproperties with required name property*/
		/*initialize*/
		if(empty($sqlJson->getform) || !empty($GLOBALS["value"]["var".$sqlJson->sqlid."_donotgenerateform"])) return "";
		$formname=$sqlJson->getform;
		if(is_object($formname)) $formname=$formname->name;			
		$formname=explode(" ",$formname);
		if(count($formname)==1) $formname[1]="";
		$formno=$formname[1];
		$sqlJson->success=false;
		$db=$GLOBALS["db"];
		$ux=new EireneUx();			
		$str="";
		$formdef="";
		$title="";
		$icon="";
		$fielddef="";		
		$formdefRes=$ux->GetFormDefinitionFromTable($formname[0],false);
		
		if(empty($formdefRes)) return false;
		$tableid=$sqlJson->tableid;
		
		/*1. Import table def if defined*/
		if(isset($formdefRes->importtabledef)){				
			$sql="SELECT table_def FROM eirene_plugin WHERE pluginname='".$formdefRes->importtabledef->pluginname."'";
			$result=$db->GetValue($sql);			
			$result=json_decode($result);
			foreach($result as $v){
				if($v->name!=$formname[0]){
					continue;
				}else{
					$formdefRes=$v;
					break;
				}
			}				
		}
				
		/*2. find the right form*/
		$formdef="";
		if(isset($formdefRes->form->$formno))
			$formdef=$formdefRes->form->$formno;
		else{
			if(isset($formdefRes->form)){
				foreach($formdefRes->form as $f){
					$formdef=$f;break;
				}
			}else{
				return false;
			}
		}
			
		/*3. title, formtype & Icon*/
		$title=!isset($formdef->title)?"":$formdef->title;
		$formtype="2 Columns";
		if(!empty($formdef->type)) $formtype=$formdef->type;
		else if(!empty($formdef->formtype)) $formtype=$formdef->formtype;
		$formname=str_replace(" ","_",strtolower($formname[0]));
		$icon="mif-delicious";
		if(!empty($formdef->icon)){				
			$icon=explode(" ",$formdef->icon);
			$icon=$icon[0];
		}
		
		/*4. process form fields*/
		foreach($formdefRes->fields as $x1){
			$tempnm=$x1->name."_alias";
			if(!isset($x1->alias)) $x1->alias="";
			$sqlJson->$tempnm=$x1->alias;
		}
		
		$fielddef=array();
		if(empty($formdef->fields)) $formdef->fields="*";	
		if(is_string($formdef->fields)){					
			if(trim($formdef->fields)=="*"){						
				$fielddef=array();
				foreach($formdefRes->fields as $x1){					
					if($x1->name=="createdon" || $x1->name=="modifiedon") continue;
					$fielddef[]=$x1->name;							
				}						
			}else
				$fielddef=explode(",",$formdef->fields);
		}else if(is_array($formdef->fields)){
			foreach($formdef->fields as $f){
					if(is_string($f)){
						$f2=explode(",",$f);
						foreach($f2 as $f3)
							$fielddef[]=$f3;
					}else
						$fielddef[]=$f;
			}
		}			
		/*5. saveid*/
		if(empty($formdefRes->saveid)) $formdefRes->saveid="";
		if(empty($formdefRes->getid)) $formdefRes->getid="";
		if(is_array($formdefRes->saveid)){
			foreach($formdefRes->saveid as $s){
				if(is_object($s)){
					if(isset($s->id)){
						$tempid=$s->id;
						$formdefRes->saveid=explode("-",$tempid)[0];
						break;
					}
				}
			}
		}		
		
		/*6. Form Class*/
		$formclass="";
		$formclass="data-role='panel' id='".$tableid."Panel' data-cls-panel='inherit-colors' data-title-icon='<span class=&apos;".$icon."&apos;></span>' data-title-caption='".$title."' data-collapsible='true'";											
		if(isset($formdef->datacollapsed)){
			if($formdef->datacollapsed==true)
				$formclass.=" data-collapsed='true'";
			else
				$formclass.=" data-collapsed='false'";
		}else{
			$formclass.=" data-collapsed='true'";
		}
		if(isset($formdef->dataclstitle)) $formclass.=" data-cls-title='".$formdef->dataclstitle."'";
		if(isset($formdef->dataclstitlecaption)) $formclass.=" data-cls-title-caption='".$formdef->dataclstitlecaption."'";
		if(isset($formdef->dataclstitleicon)) $formclass.=" data-cls-title-icon='".$formdef->dataclstitleicon."'";
		if(isset($formdef->dataclscontent)) $formclass.=" data-cls-content='".$formdef->dataclscontent."'";
		if(isset($formdef->dataclscollapsetoggle)) $formclass.=" data-cls-collapse-toggle='".$formdef->dataclscollapsetoggle."'";
		
		/*7. Form building starts*/
		$str.="<div class='userform' name='".$tableid."Form' id='".$tableid."Form' title='".$title."'>";
			$str.="<div class=' bd-gray mb-4'>";									
				$str.='<div '.$formclass.'>';					
				$rowClass="row mb-2";						
				/*7a. form options*/
				if(strtolower($formtype)=="inline") $rowClass="inline-form";
					
				/*7b. form display setting & button*/						
				$str.="<div>";
					$str.="<div style='text-align:right' onclick=\"$('#".$tableid."Form .formdisplaysetting').toggle('slow')\"><span class='mif-cog'></span></div>";
					$str.="<div style='text-align:left;display:none;' class='formdisplaysetting'>";											
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="Horizontal" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;Horizontal&apos;,$(this))">';
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="1 Col" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;1 Column&apos;,$(this))">';
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="2 Col" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;2 Columns&apos;,$(this))">';
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="3 Col" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;3 Columns&apos;,$(this))">';
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="4 Col" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;4 Columns&apos;,$(this))">';
						$str.='<input type="radio" name="'.$tableid.'radio" data-role="radio" data-caption="Inline" onchange="changeFormType(&apos;'.$tableid.'&apos;,&apos;Inline&apos;,$(this))">';
					$str.="</div>";
				$str.="</div>";
				/*7c. hidden id field*/
				$str.="<input type='hidden' field='true' name='id'  fieldType='s' value=''>";
					
				/*7d. build form fields*/
				$htmlCls=new EireneHTMLBuilder();
				$str.="<div class='formelem ".$rowClass."'>";
				foreach($fielddef as $f){
					if(is_string($f)){
						$x="";
						
						foreach($formdefRes->fields as $x1){
							if($x1->name==$f){
								foreach($sqlJson as $k=>$v){
									if(!isset($x1->$k)){
										if(is_string($v) && !in_string('output,outputto,action,sql,required,alias,tbl,vsum,fld,whr,srt,join,having,onsuccess,onfailure,formname,getform,validate,view_rights,tableid,primarytablenamealias',$k)){
											$x1->$k=$v;
										}
									}
								}
								$str.=$this->GetForm_GetField($x1,$formtype,$htmlCls,$tableid);
								break;
							} 
						}								
					}else if(is_object($f)){
						foreach($sqlJson as $k=>$v){
							if(!isset($f->$k)){
								if(is_string($v) && !in_string('output,outputto,action,sql,required,alias,tbl,vsum,fld,whr,srt,join,having,onsuccess,onfailure,formname,getform,validate,view_rights,tableid,primarytablenamealias',$k)){
									$f->$k=$v;
								}
							}
						}
						$str.=$this->GetForm_GetField($f,$formtype,$htmlCls,$tableid);
					}
				}				
				$str.="</div>";
				
				/*7e. Form buttons*/
				$str.="<div class='d-flex flex-justify-end p-2'>";					
				if(empty($formdef->saveButton))$formdef->saveButton=true;
				if(empty($formdef->clearButton))$formdef->clearButton=true;
				if(empty($formdef->cancelButton))$formdef->cancelButton=true;
				
				/*7f. savebutton*/				
				if($formdef->saveButton!=false){					
					$str.="<button disabled  class=\"button save primary mr-3\" onclick=\"Eirene.currentelem=$(this);save('".$tableid."','".$formdefRes->saveid."');";
					//if(!empty($sqlJson->associatedTableDetails)){
						//$str.="Eirene.runStmt('".$sqlJson->associatedTableDetails->sqlid."',{";
						//$str.=$this->GetVariablesForUseInUserFunction($sqlJson);									
						//$str.="})\"";
					//}
					$str.="\">Save</button>";
				}				
				/*8g: clear button*/	
				$str.=$formdef->clearButton==false?"":"<button  class='button secondary mr-3' onclick='Eirene.currentelem=$(this);clearForm(\"".$tableid."\")'>Clear</button>";
				/*8h: cancel button*/
				//$str.=$formdef->cancelButton==false?"":"<button class='button secondary mr-3 js-dialog-close' onclick='hideWindow(\"".$tableid."\")'>Cancel</button>";
				/*8i: custom buttons*/
				$custBtn=empty($formdef->customButton)?"":$formdef->customButton;
				if(!empty($custBtn)){
					if(is_string($custBtn)) $custBtn=json_decode($custBtn);
					foreach($custBtn as $b){
						$b->type="button";						
						$str.=$htmlCls->GetHTMLElement($b);
					}
				}
				$str.="</div>";
			$str.="</div>";				
			$str.="</div>";
		$str.="</div>";
		$sqlJson->success=true;
		setOutput("form",$GLOBALS["result"],$str,$sqlJson);			
		setOutput("title",$GLOBALS["result"],$title,"");
		setOutput("getid",$GLOBALS["result"],$formdefRes->getid,"");
		setOutput("saveid",$GLOBALS["result"],$formdefRes->saveid,"");
				
		return $str;				
	}
	private function GetForm_GetField($elem,$formtype,$htmlCls,$tableid=""){
		/*initialize*/
		$x=json_decode("{}");
		foreach($elem as $k=>$v)
			$x->$k=$v;
		//$x=$elem;
		
		$str="";
		/*name*/
		if(isset($x->alias)) $x->name=$x->alias;
		
		/*label*/
		$label=empty($x->label)?"":$x->label;
		$x->label="";								
		$x->label=$label;
		if(isset($x->default)) $x->value=$x->default;
		/*columns 1,2,3,4,horizontal*/		
		$elemclass="formfield ";
		if($formtype=="Horizontal") 
			$elemclass.="cell-md-12";
		else if($formtype=="1 Column")
			$elemclass.="cell-md-12";
		else if($formtype=="2 Columns" || $formtype=="Default")
			$elemclass.="cell-md-6";
		else if($formtype=="3 Columns")
			$elemclass.="cell-md-4";
		else if($formtype=="4 Columns")
			$elemclass.="cell-md-3";
		else if(strtolower($formtype)=="inline")
			$elemclass.="cell-md-6";
		/*fieldtype*/
		
		if(empty($x->fieldtype)){			
			//print_r($x);echo "<br>";
			//echo stripos($x->type,"char");echo $x->type."<br><br>";
			$numericvalueofstorage=preg_replace('/\D/','',$x->type);
			if(stripos($x->type,"char")>-1 && empty($x->option) && stripos($x->type,"enum(")===false){				
				if(intVal($numericvalueofstorage)<3000){
					$x->type="text";											
				}else
					$x->type="textarea";
				if(intVal($numericvalueofstorage)>0) $x->maxlength=intVal($numericvalueofstorage);
			}else if(stripos($x->type,"char")>-1 && !empty($x->option) && stripos($x->type,"enum(")===false){
				$x->type="select";
			}else if(stripos($x->type,"int")>-1 && stripos($x->type,"enum(")===false){				
				$x->type="number";
			}else if(strtolower($x->type=="date")){
				$x->type="datepicker";
			}else if(strtolower($x->type=="time")){
				$x->type="time";
			}else if(strtolower($x->type=="datetime")){
				$x->type="datetime";
			}else if(strtolower($x->type=="lookup")){
				$x->type="lookup";
			}else if(stripos($x->type,"enum(")!==false){
				$x->type=substr(trim($x->type),0,-1);
				$opt=str_ireplace("enum(","",$x->type);
				$opt=str_replace("'","",$opt);
				$opt=str_replace(")","",$opt);				
				$x->type="select";
				$x->option=$opt;
			}else
				$x->type="text";
			
		}else
			$x->type=$x->fieldtype;
		
		/*label and element*/
		if(isset($x->required)){
			if(!isset($x->label)) $x->label="";
			$x->label.=" *";
		}
		
		/*Detect change*/
		if(!empty($tableid)){
			if(isset($x->onchange)){
				$x->onchange="$('#".$tableid."Form .save').prop('disabled',0);".$x->onchange;
			}else
				$x->onchange="$('#".$tableid."Form .save').prop('disabled',0);";
		}
		
		//print_r($x);echo "<br>";
		$elm=$htmlCls->GetHTMLElement($x);
		if($x->type=="hidden"){
			$elemclass="' style='display:none";
		}
		$str.="<div class='".$elemclass."'>";
			//if(strtolower($formtype)=='inline'){
			//	$x->labelclass="label cell-4";								
			//	$x->class="elem cell-7";
			//}else if(strtolower($formtype)=='1 column' || strtolower($formtype)=='2 columns'){
			if(strtolower($formtype)!=="horizontal"){
				$x->labelclass="label cell-12 p-1";								
				$x->class="elem cell-12 p-1";
			}else{
				$x->labelclass="label cell-2 p-1";								
				$x->class="elem cell-9 p-1";
			}
			//}else{
				/*for 2 or more columns*/
			//	$x->labelclass="label cell-3";								
			//	$x->class="elem cell-9";
			//}
			
			$elm=$htmlCls->GetHTMLElement($x);								
			if(strtolower($formtype)=='horizontal'){								
				$str.="<div class='labelelem pr-2 row'>".$elm."</div>";
			}else if(strtolower($formtype)=='inline'){								
				$str.="<div class='labelelem pr-2 row'>".$elm."</div>";
			}else if($x->type=="lookup"){
				$str.="<div class='labelelem pr-2 row'>".$elm."</div>";
			}else{																		
				$str.="<div class='labelelem pr-2'>".$elm."</div>";
			}
		$str.="</div>";
		return $str;
	}
	function DeleteFile(&$sqlJson,$value,&$result){
		$path=trim(GetStringFromJson($sqlJson,"path"," "));		
		$res=false;
		$sqlJson->success=false;
		if(file_exists($path)){
			$res=unlink($path);
			$sqlJson->success=$res;
			setOutput("res",$result,$res,$sqlJson);
		}else
			setOutput("error",$result,"File Not Found",$sqlJson);
		return $res;
	}
	function SendEmail(&$sqlJson,$value,&$result){
		$default_template="<table border='0' bgcolor='#f3f3f3' style='border-collapse:collapse;font-family:Segoe UI,Open Sans,-apple-system,sans-serif;height:100%;width:100%'><tr><td colspan='3' style='height:30px;'></td></tr><tr><td style='width:10%' bgcolor='#f3f3f3' ></td><td style='width:80%;color:#fff;font-size:2em;padding:20px;' bgcolor='#1e88e5'>||TITLE||</td><td style='width:10%' bgcolor='#f3f3f3' ></td></tr><tr><td style='width:10%' bgcolor='#f3f3f3' ></td><td style='width:80%;padding:20px;' bgcolor='#fff' > <br><div style='font-size:1.2em;font-weight:bold'>||SUBJECT||</div> <br> <br> ||CONTENT||</td><td style='width:10%' bgcolor='#f3f3f3'></td></tr><tr><td colspan='3' style='height:30px;'></td></tr></table>";
		$sqlJson->success=false;
		include_once 'eirene_mail.php';
		
		$ux=new EireneUx(); $to="";$cc="";$subject="";$content="";
		if(!empty($sqlJson->to))	$to=$ux->SupplyParamValue($sqlJson->to,$sqlJson);
		if(!empty($sqlJson->cc))	$cc=$ux->SupplyParamValue($sqlJson->cc,$sqlJson);
		if(!empty($sqlJson->subject))	$subject=GetStringFromJson($sqlJson,"subject"," ");
		if(!empty($sqlJson->content))	$content=GetStringFromJson($sqlJson,"content"," ");
		if(!empty($sqlJson->title))	$sqlJson->title=GetStringFromJson($sqlJson,"title"," ");
		if(!empty($sqlJson->sendername))	$sqlJson->sendername=GetStringFromJson($sqlJson,"sendername"," ");
		if(strpos($content,"\n")) $content=str_replace("\n","<br>",$content);		
		if(empty($to)){
			$sqlJson->error="Please add atleast one recipient";
			return false;			
		} 
		
		$template="";
		if(empty($sqlJson->template)) 
			$template=$default_template;
		else
			$template=$sqlJson->template;
		$title=(empty($sqlJson->title))?"NIEA Online":$sqlJson->title;
		$content=str_replace(array("||CONTENT||","||SUBJECT||","||TITLE||"),array($content,$subject,$title),$template);
		$sendername=(isset($sqlJson->sendername))?$sqlJson->sendername:"";
		
		$mail=new EireneMail();
		$replyto=!empty($sqlJson->replyto)?$ux->SupplyParamValue($sqlJson->replyto,$sqlJson):"";
		$res=$mail->sendEmail($to,$cc,$subject,$content,$sendername,$replyto);
		if(!empty($mail->error)){
			$sqlJson->error=$mail->error;
			
		}else{
			$sqlJson->success=true;			
			setOutput("success",$result,$res,$sqlJson);
		}
		return $sqlJson->success;
	}
	function GetMatrix(&$sqlJson,$value,&$result){
		/*initialize*/
		$db=$GLOBALS["db"];	
		$sqlJson->success=false;		
		$row=$sqlJson->row;
		$col=$sqlJson->col;		
		$additionalcol=isset($sqlJson->additionalcol)?explode(",",$sqlJson->additionalcol):"";/*additionalcol is comma seperated col name*/
		$val=empty($sqlJson->val)?"":$sqlJson->val;
		if(empty($val)){
			$val=empty($sqlJson->value)?"":$sqlJson->value;
		}
		$matrixeditable=(isset($sqlJson->matrixeditable))?$sqlJson->matrixeditable:false;
		$matrixinputtype=(isset($sqlJson->matrixinputtype))?$sqlJson->matrixinputtype:"";
		$matrixoption=(isset($sqlJson->matrixoption))?$sqlJson->matrixoption:"";
		$matrixinputonblur=(isset($sqlJson->matrixinputonblur))?$sqlJson->matrixinputonblur:"";
		$mixmatrix=false;
		$mixmatrixres=array();
		$horizontalsum=false;
		$verticalsum=false;
		$input="";
		
		$rows=[];$rows1=[];
		$cols=[];$cols1=[];
		$rowdisplay=[];$rowdisplay1=[];
		$rowindex=[];$rowindex1=[];
		$coldisplay=[];$coldisplay1=[];
		$mix=[];$mix1=[];		
		$id=[];$id1=[];
		
		//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$sqlJson->sql).'</textarea>';
		$res=$db->GetList1($sqlJson->sql,true);
		if(empty($res)) {
			setOutput("res",$result,"",$sqlJson);
			return false;
		}
		
		
		/*additionalcol data*/
		$addcol=array();
		if(!empty($additionalcol)){			
			foreach($res as $r){
				$ar=array();
				foreach($additionalcol as $a){					
					if(isset($r[$a])){
						$ar[$a]=$r[$a];
					}
				}
				if(!isset($addcol[$r[$row]])) $addcol[$r[$row]]=$ar;
			}
		}
		//print_r($res);
		//echo str_replace('&apos;','&apos','<textarea>'.$sqlJson->sql.'</textarea><br>');
		//echo $db->error."<br>".$sqlJson->sql;
		if(!empty($db->error)){$sqlJson->error=$db->error;return false;}
		$sqlJson->success=true;
		
				
		/*mix matrix*/		
		if(isset($sqlJson->mixmatrix)){
			$mixmatrix=true;
			$ux=new EireneUx();
			$json="{}";$json=json_decode($json);
			$sql=$ux->FetchSql($json,$sqlJson->mixmatrix);
			$mixmatrixres=$GLOBALS["db"]->GetList1($sql,true);
			//echo $sql;
			
		}
		
		
		if(!empty($sqlJson->matrixeditable)){
			if(!empty($matrixoption)){
				if(IsSqlStatement($matrixoption)){
					$res1=$db->GetList1($sqlJson->sql,false);
					$dropdownoptions=array();
					foreach($res1 as $r){
						if(count($r)==1)
							$dropdownoptions[$r[0]]=$r[0];
						else
							$dropdownoptions[$r[0]]=$r[1];
					}
				}else if(is_string($matrixoption)){
					$dropdownoptions=$matrixoption;
				}
			}
			if($matrixinputtype=="select")
				$input="select";
			else if($matrixinputtype=="text")
				$input ="input type='text'";
			else if($matrixinputtype=="textarea")
				$input="textarea";
		}
		if(!empty($matrixinputonblur)){
			if($matrixinputtype=="select")
				$input.=" onchange='".$matrixinputonblur."'";
			else
				$input.=" onblur='".$matrixinputonblur."'";
		}
		
		if(isset($sqlJson->hsum) || isset($sqlJson->horizontalsum))$horizontalsum=true;
		if(isset($sqlJson->vsum) || isset($sqlJson->verticalsum))$verticalsum=true;
				
		
		if(!empty($res)){
			$mix=ConvertIntoMatrix($res,$rowdisplay,$coldisplay,$rows,$cols,$rowindex,$id,$sqlJson);
			//print_r($mix);
			if($mixmatrix==true){
				$mix1=ConvertIntoMatrix($mixmatrixres,$rowdisplay,$coldisplay,$rows1,$cols1,$rowindex1,$id1,$sqlJson);
			}
			/*sort columns*/
			if(isset($sqlJson->sortcol)) sort($cols);
			
			/*build table*/
			$tableid="Table".rand(1,9999);
			$str="<div id='".$tableid."'><table";			
			if(!empty($sqlJson->class)) $str.=" class='".$sqlJson->class."'";
			if(!empty($sqlJson->style)) $str.=" style='".$sqlJson->style."'";
			$str.="><thead><tr>";
			if(isset($sqlJson->serialno))
				$str.="<th>S.No.</th>";
			$str.="<th></th>";
			$vsum=array();
			$vsum1=array();
			/*headers for additional columns*/
			if(!empty($additionalcol)){
				foreach($additionalcol as $a){
					$str.="<th></th>";
				}
			}
			foreach($cols as $c){
				$str.="<th>".$coldisplay[$c]."</th>";
				$vsum[$c]=0;
				$vsum1[$c]=0;
			}
			if($horizontalsum) $str.="<th>Total</th>";
			$str.="</tr></thead><tbody>";
			$serialno=0;
			foreach($rows as $r){
				$serialno++;
				$str.="<tr>";
				if(isset($sqlJson->serialno))
					$str.="<td>".$serialno."</td>";
				$str.="<td>".$rowdisplay[$r]."</td>";
				$sum=0;$sum1=0;
				
				if(!empty($additionalcol)){					
					if(isset($addcol[$r])){
						foreach($additionalcol as $a){						
							$str.="<td>".$addcol[$r][$a]."</td>";
						}
					}
				}
				
				foreach($cols as $c){
					if($verticalsum){
						if(isset($mix[$r."_".$c])) $vsum[$c]+=floatval(preg_replace('/[^0-9.]+/', '', $mix[$r."_".$c]));
						if($mixmatrix){
							if(isset($mix1[$r."_".$c])) $vsum1[$c]+=floatval(preg_replace('/[^0-9.]+/', '', $mix1[$r."_".$c]));
						}
					}
					$input1=$input;
					if(isset($rowindex[$r."_".$c])){
						foreach($res[$rowindex[$r."_".$c]] as $ky=>$kv){											
							//$input1=str_replace("||id||",$id[$r."_".$c],$input1);						
							$input1=str_replace("||$ky||",$kv,$input1);						
						}
					}else{
						$input1=str_replace("||".$sqlJson->row."||",$r,$input1);	
						$input1=str_replace("||".$sqlJson->col."||",$c,$input1);	
					}
					
					if(stripos($input1,"||id||") && isset($id[$r."_".$c])){
						$input1=str_ireplace("||id||",$id[$r."_".$c],$input1);	
					}
					
					if(isset($mix[$r."_".$c])){
						if(empty($matrixeditable)){
							$str.="<td>";
							if($mixmatrix==true){
								if(isset($mix1[$r."_".$c]))
									$str.=$mix1[$r."_".$c]."/";
								else
									$str.="/";								
							}
							$str.=$mix[$r."_".$c]."</td>";
						}else{
							$str.="<td>";							
							$str.="<".$input1;
							if($matrixinputtype=="text")
								$str.=" value='".$mix[$r."_".$c]."'>";
							else if($matrixinputtype=="checkbox"){
								if(!empty($mix[$r."_".$c]))
									$str.=" checked>";
								else
									$str.=">";
							}else if($matrixinputtype=="textarea")
								$str.=">".$mix[$r."_".$c];
							else if($matrixinputtype="select")
								$str.=">".FillDropdownHTML($dropdownoptions,$mix[$r."_".$c]);
							$str.="</".explode(" ",$input1)[0].">";
							$str.="</td>";
						}
						if($horizontalsum) {$sum+=floatval(preg_replace('/[^0-9.]+/', '', $mix[$r."_".$c]));}
						if($mixmatrix){
							if(isset($mix1[$r."_".$c])) $sum1+=floatval(preg_replace('/[^0-9.]+/', '', $mix1[$r."_".$c]));
						}
					}else{
						if(empty($dropdownoptions))
							$str.="<td></td>";
						else{
							$str.="<td>";						
							$str.="<".$input1;
							if($matrixinputtype=="text")
								$str.=">";
							else if($matrixinputtype=="checkbox"){
								$str.=">";
							}else if($matrixinputtype=="textarea")
								$str.=">";
							else if($matrixinputtype="select")
								$str.=">".FillDropdownHTML($dropdownoptions,"");
							$str.="</".explode(" ",$input1)[0].">";
							$str.="</td>";
						}
					}
				}
				if($horizontalsum){
					if(!$mixmatrix)
						$str.="<td>$sum</td>";
					else
						$str.="<td>$sum1/$sum</td>";
				}
				$str.="</tr>";				
			}
			/*vertical sum*/
			if($verticalsum){
				$str.="<tr>";
				if(isset($sqlJson->serialno)) $str.="<td></td>";
				$str.="<td>Total</td>";
				if(!empty($additionalcol)){
					foreach($additionalcol as $a){
						$str.="<td></td>";
					}
				}
				$sum=0;$sum1=0;
				
				foreach($cols as $c){
					if(!$mixmatrix){
						$str.="<td>".$vsum[$c]."</td>";
						$sum+=floatval($vsum[$c]);
					}else{
						$str.="<td>".$vsum1[$c]."/".$vsum[$c]."</td>";
						$sum+=floatval($vsum[$c]);
						$sum1+=floatval($vsum1[$c]);
					}
				}
				if($horizontalsum){
					if(!$mixmatrix)
						$str.="<td>".$sum."</td>";
					else
						$str.="<td>".$sum1."/".$sum."</td>";
				}
				$str.="</tr>";
			}
			$str.="</tbody></table>";
			if(isset($sqlJson->datatable)){
				if($sqlJson->datatable)
					$str.="<script>datatable('".$tableid."')</script>";
			}
			$str.="</div>";
			//echo $str;
			setOutput("res",$result,$str,$sqlJson);
			return false;
		}else
			return false;
	}
	function GetCaseVariableValue(&$json,$value,&$result){
		if(!isset($json->casevariable)) return "";
		$ux=new EireneUx();
		$cvar=$ux->SupplyParamValue($json->casevariable,$json);
		$val="";
		$json->success=false;
		foreach($json as $c=>$v){
			if(strpos($c,":")===false)
				if($c!=$cvar) continue;
			else{
				$cc=explode(":",$c);
				if($cc=="=")
					if($cvar!=$c) continue;
				else if($cc==">=")
					if($cvar<$c) continue;
				else if($cc==">")
					if($cvar<$c) continue;
				else if($cc=="<=")
					if($cvar>$c) continue;
				else if($cc=="<")
					if($cvar>$c) continue;
				else if($cc=="!=")
					if($cvar==$c) continue;
			}
			if(is_string($v)){				
				$val=$v;
				break;
			}else if(is_object($v)){
				$t=$v;
				/*if $v is object then it must be an if defition - follow if definition here*/
				$res=$this->CheckIfCondition($v,$value,$result);
				if($res & isset($v->then)) $val=$v->then;
				if(!$res & isset($v->else)) $val=$v->else;					
			}else if(is_array($v)){
				/*all array elemtn must be treated as if defition; Else node in all if definition will be ignored*/
				foreach($v as $vv){
					$res=$this->CheckIfCondition($vv,$value,$result);
					if($res==true){
						if(isset($vv->then)) $val=$vv->then;
						break;
					}
				}
			}
		}
		$json->success=true;
		return $val;
		
	}
	function CheckSwitchCondition(&$sqlJson,$value,&$result){
		$ux=new EireneUx();
		if(strpos($sqlJson->value,"||")!==false) $sqlJson->value=$ux->SupplyParamValue($sqlJson->value,$sqlJson);
		$val=$sqlJson->value;
		$sqlJson->success=false;
		if(isset($sqlJson->$val)){
			$sqlJson->success=true;
			if(isset($sqlJson->onsuccess)){
				if(is_array($sqlJson->onsuccess))
					$sqlJson->onsuccess[]=$sqlJson->$val;
				else if(is_string($sqlJson->onsuccess))
					$sqlJson->onsuccess.="-".$sqlJson->$val;
			}else{
				$sqlJson->onsuccess=$sqlJson->$val;
			}
		}
		setOutput("res",$result,true,$sqlJson);
		return $sqlJson->success;
	}
	function CheckIfCondition(&$sqlJson,$value,&$result){		
		$ux=new EireneUx();
		$andcheck=false;
		$orcheck=false;
		/*process shortcut codes*/		
		if(isset($sqlJson->if) && !isset($sqlJson->value)) $this->ParseIfStatement($sqlJson,"if");
		if(isset($sqlJson->runif) && !isset($sqlJson->value))$this->ParseIfStatement($sqlJson,"runif");
		if(isset($sqlJson->and) && !isset($sqlJson->andvalue))$this->ParseIfStatement($sqlJson,"and");
		if(isset($sqlJson->or) && !isset($sqlJson->orvalue))$this->ParseIfStatement($sqlJson,"or");	
		if(is_string($sqlJson)){
			$tempstr=$sqlJson;
			$sqlJson=json_decode("{}");
			$sqlJson->if=$tempstr;
			$this->ParseIfStatement($sqlJson,"if");
		}
		/*Supply value to variables*/		
		if(strpos($sqlJson->value,"||")!==false) $sqlJson->value=GetStringFromJson($sqlJson,"value");
		if(isset($sqlJson->value1))$sqlJson->value1=GetStringFromJson($sqlJson,"value1");
		if(isset($sqlJson->if)){$sqlJson->value1=GetStringFromJson($sqlJson,"if");}		
		if(!isset($sqlJson->op) && !isset($sqlJson->operator)) $sqlJson->op="=";
		if(isset($sqlJson->op))$sqlJson->operator=$sqlJson->op;
		//print_r($sqlJson);echo "<br>";print_r($GLOBALS["value"]);echo "<br><br>";
		if(isset($sqlJson->and)){
			$andcheck=true;
			$and=GetStringFromJson($sqlJson,"and");
			if(!isset($sqlJson->andop) && !isset($sqlJson->andoperator)) $sqlJson->andop="=";
			if(isset($sqlJson->andop))$sqlJson->andoperator=$sqlJson->andop;
			$andop=isset($sqlJson->andoperator)?$sqlJson->andoperator:"=";
			$andval=isset($sqlJson->andvalue)?GetStringFromJson($sqlJson,"andvalue"):"";
		}
		if(isset($sqlJson->or)){
			$orcheck=true;
			$or=GetStringFromJson($sqlJson,"or");
			if(isset($sqlJson->orop))$sqlJson->oroperator=$sqlJson->orop;
			$orop=isset($sqlJson->oroperator)?$sqlJson->oroperator:"=";
			$orval=isset($sqlJson->orvalue)?GetStringFromJson($sqlJson,"orvalue"):"";
		}
		$res="";
		if(!isset($sqlJson->value1)){
			$res=$this->GetValue($sqlJson,$value,$result);			
		}else{			
			$res=$sqlJson->value1;			
		}
		
		setOutput("res",$result,$res,$sqlJson);		
		$operator=isset($sqlJson->operator)?$sqlJson->operator:"=";
		$val=isset($sqlJson->value)?$sqlJson->value:"";
		$check=false;
		
		if(isset($sqljson->and)) $andcheck=true;
		if(isset($sqljson->or)) $orcheck=true;
		//echo (int)$andcheck." ;".(int)$orcheck."<br>";
		if($andcheck==false && $orcheck==false){
			//print_r($sqlJson);echo "<br>";
			//echo $res." ".$sqlJson->operator." ".$val."<br>";
			$check=conditionCheck($res,$operator,$val);
			
		}elseif($andcheck==true){
			$check=conditionCheckAnd($res,$operator,$val,$and,$andop,$andval);
			//echo $check."<br>";
		}elseif($orcheck==true){
			$check=conditionCheckOr($res,$operator,$val,$or,$orop,$orval);
		}
		if($check==true)
			$sqlJson->success=true;
		else
			$sqlJson->success=false;
		
		return $sqlJson->success;	
	}	
	function ParseIfStatement(&$sqlJson,$type="if"){
		/*type can be if, and, or,runif*/
		/*check */
		if($type=="if" && isset($sqlJson->value)) return false;
		if($type=="and" && isset($sqlJson->andvalue)) return false;
		if($type=="or" && isset($sqlJson->orvalue)) return false;
		if(!in_string("if,and,or,runif",$type)) return false;
		
		$tempif=str_replace("  "," ",trim($sqlJson->$type));
		$tempif=str_replace("  "," ",$tempif);/*second time will ensure that all extra spaces are eliminated*/
		$tempif=explode(" ",$tempif);
		$newif=array();
		
		if(count($tempif)>=3){
			$newif[]=$tempif[0];			
			$tempif1=array();
			if(!in_string("=,!=,<,>,<=,>=",$tempif[1])){
				$newif[]="=";
				$tempif1[]=$tempif[1];
			}else{
				$newif[]=$tempif[1];
			}
			foreach($tempif as $k=>$v){
				if($k==0 or $k==1) continue;
				$tempif1[]=$v;
			}
			$newif[]=implode(" ",$tempif1);
		}else if(count($tempif)==2){			
			$tempif1=array();			
			$newif=array();
			$newif[]=$tempif[0];
			$newif[]="=";
			$newif[]=$tempif[1];			
		}		
		if($type=="if" || $type=="runif"){
			$sqlJson->if=$newif[0];
			$sqlJson->op=$newif[1];
			$sqlJson->value=$newif[2];
			if(isset($sqlJson->runif)) unset($sqlJson->runif);
		}else if($type=="and"){
			$sqlJson->and=$newif[0];
			$sqlJson->andop=$newif[1];
			$sqlJson->andvalue=$newif[2];
		}else if($type=="or"){
			$sqlJson->or=$newif[0];
			$sqlJson->orop=$newif[1];
			$sqlJson->orvalue=$newif[2];
		}
	}
	function RunPHPFunction(&$sqlJson){
		/*{“phpfunction”:{“fun”:”Functionname”,”param1”:”argument1”,”param2”:”argument2”} Upto 6 argument can be specified.*/
		$sqlJson->success=false;
		if(isset($sqlJson->phpfunction)) $sqlJson->fun=$sqlJson->phpfunction;
		if(!isset($sqlJson->fun)) return false;
		if(is_string($sqlJson->fun)==true) return false;
		if(is_object($sqlJson->fun)) $sqlJson->fun=array($sqlJson->fun);
		
		foreach($sqlJson->fun as $f){			
			/*Command will run if runif is true.*/
			if(!$this->RunIf($f)){continue;}			
			
			$fun=$f->fun;
			$res="";			
			/*parse the node and supply variable if needed*/
			if(isset($f->param1)) $f->param1=GetStringFromJson($f,"param1","");
			if(isset($f->param2)) $f->param2=GetStringFromJson($f,"param2","");
			if(isset($f->param3)) $f->param3=GetStringFromJson($f,"param3","");
			if(isset($f->param4)) $f->param4=GetStringFromJson($f,"param4","");
			if(isset($f->param5)) $f->param5=GetStringFromJson($f,"param5","");
			if(isset($f->param6)) $f->param6=GetStringFromJson($f,"param6","");
			if(isset($f->param1)) {if($f->param1=="true"){$f->param1=true;}}
			if(isset($f->param2)) {if($f->param2=="true"){$f->param2=true;}}
			if(isset($f->param3)) {if($f->param3=="true"){$f->param3=true;}}
			if(isset($f->param4)) {if($f->param4=="true"){$f->param4=true;}}
			if(isset($f->param5)) {if($f->param5=="true"){$f->param5=true;}}
			if(isset($f->param6)) {if($f->param6=="true"){$f->param6=true;}}
			if(isset($f->param1)) {if($f->param1=="false"){$f->param1=false;}}
			if(isset($f->param2)) {if($f->param2=="false"){$f->param2=false;}}
			if(isset($f->param3)) {if($f->param3=="false"){$f->param3=false;}}
			if(isset($f->param4)) {if($f->param4=="false"){$f->param4=false;}}
			if(isset($f->param5)) {if($f->param5=="false"){$f->param5=false;}}
			if(isset($f->param6)) {if($f->param6=="false"){$f->param6=false;}}			
			
			
			if(isset($f->param1) && isset($f->param2) && isset($f->param3) && isset($f->param4) && isset($f->param5) && isset($f->param6)){										
				$res=$fun($f->param1,$f->param2,$f->param3,$f->param4,$f->param5,$f->param6);
			}else if(isset($f->param1) && isset($f->param2) && isset($f->param3) && isset($f->param4) && isset($f->param5)){										
				$res=$fun($f->param1,$f->param2,$f->param3,$f->param4,$f->param5);
			}else if(isset($f->param1) && isset($f->param2) && isset($f->param3) && isset($f->param4)){										
				$res=$fun($f->param1,$f->param2,$f->param3,$f->param4);
			}else if(isset($f->param1) && isset($f->param2) && isset($f->param3)){				
				$res=$fun($f->param1,$f->param2,$f->param3);				
			}else if(isset($f->param1) && isset($f->param2)){				
				$res=$fun($f->param1,$f->param2);
			}else if(isset($f->param1)){				
				$res=$fun($f->param1);
			}else if(isset($f->param2)){				
				$res=$fun($f->param2);
			}else{
				$res=$fun();
			}
			/*if output is array and if outputarraynumber is not defined than first item will be set as output*/
			if(is_array($res)){
				if(!isset($f->outputarraynumber))
					$res=$res[0];
				else{
					if(isset($res[$f->outputarraynumber]))
						$res=$res[$f->outputarraynumber];
					else
						$res=$res[0];
				}
			}
			setOutput("res",$GLOBALS["result"],$res,$f);
		}
		//print_r($GLOBALS["globalphp"]);
		$sqlJson->success=true;
	}
	function RunSqlStatement(&$sqlObj,$sqlJson=""){		
		//print_r($GLOBALS["value"]["context"]);echo "<br>";
		/*This function will not return any string, It will only update PHPglobalvariable after running sqlcommand*/
		/*format "sqlstatement":{"a":"statement1","b":"statement2"}*/
		if(!isset($sqlObj->sqlstatement)) return false;
		$canproceed=true;		
		if(isset($sqlObj->sqlstatement->runif)){
			$this->ParseIfStatement($sqlObj->sqlstatement,"runif");
			
			/*runif must be an if-object*/
			/*below sqlstatements can proceed only if runif returns true*/
			if(!$this->CheckIfCondition($sqlObj->sqlstatement,$GLOBALS["value"],$GLOBALS["result"])){		
				$canproceed=false;
				//print_r($sqlObj->sqlstatement);echo "<br>";
				//print_r($GLOBALS["globalphp"]);echo "<br>";
			}
		}
		
		if($canproceed==true){
			$ux=new EireneUx();
			foreach($sqlObj->sqlstatement as $k=>$v){
				if($k=="runif") continue;
				if(!is_string($v)) continue;
				//$v=GetStringFromJson($sqlObj->sqlstatement,$k);
				$v=$ux->SupplyParamValue($v,$sqlJson);
				$commandv=explode(" ",$v)[0];/*This will tell us if the statement is insert statement, select statemetn, delete statement etc.*/
				if(empty(trim($v))) continue;
				$pos=stripos($v,"from");
				$fld=explode(",",trim(str_ireplace("select ","",substr($v,0,$pos))));
				if(stripos($commandv,"insert")===false && stripos($v,"select")!==false  && count($fld)==1){					
					$GLOBALS["globalphp"][$k]=$GLOBALS["db"]->GetValue($v);					
				}else if(stripos($commandv,"insert")===false && stripos($v,"select")!==false && stripos($v,"from")!==false && count($fld)>1){
					$GLOBALS["globalphp"][$k]=$GLOBALS["db"]->GetList1($v);
					//echo $v."<br>";
					if(!empty($GLOBALS["globalphp"][$k])){
						if(count($GLOBALS["globalphp"][$k])==1){
							if(count($GLOBALS["globalphp"][$k][0])==1){
								$GLOBALS["globalphp"][$k]=$GLOBALS["globalphp"][$k][0][0];
							}else
								$GLOBALS["globalphp"][$k]=$GLOBALS["globalphp"][$k][0];
						}
					}
				}else{
					//echo '<textarea>'.$v.'</textarea>';
					$GLOBALS["db"]->Execute($v);
				}
				//echo '<textarea>'.$v.'</textarea><br>';print_r($GLOBALS["globalphp"]);echo "<br>";
			}
		}
	}
	function GetChart(&$sqlJson,$value,&$result){
		$sqlJson->success=false;
		$db=$GLOBALS['db'];
		$db->error="";
		$res=$db->GetList1($sqlJson->sql,true);
		//echo "<textarea>".$sqlJson->sql."</textarea>";
		if(!empty($db->error)) {$sqlJson->error=$db->error;return false;}
		$mainval=array();
		$legend=array();
		$val1;
		if(!empty($res)){
			$sqlJson->success=true;
			$lbl=array();
			$lbld=array();
			$val=array();			
			$val1=$sqlJson->value;
			foreach($res as $k=>$v){				
				if(isset($sqlJson->label)) $lbl[]=$v[$sqlJson->label];
				if(isset($sqlJson->labeldisplay)) $lbld[]=$v[$sqlJson->labeldisplay];
				if(isset($sqlJson->value)) $val[]=$v[$val1];
			}
		}
		if(isset($sqlJson->legend)) $legend[]=$sqlJson->legend;
		$mainval[]=implode(",",$val);
		$secval=array();
		if(isset($sqlJson->dataset) && isset($sqlJson->datafilter)){
			$df=explode(",",$sqlJson->datafilter);
									
			foreach($df as $f){
				$legend[]=$f;
				$secval=array();
				if(strtoupper($f)!="TOTAL")
					$ds=str_replace("||DATAFILTER||",$f,$sqlJson->dataset);
				else
					$ds=str_replace("||DATAFILTER||","%",$sqlJson->dataset);
				$ds1=$ds;
				foreach ($lbl as $l){
					if(strtoupper($l)!="TOTAL")
						$ds=str_replace("||LABEL||",$l,$ds1);
					else
						$ds=str_replace("||LABEL||","%",$ds1);
					//echo $ds."<br>";
					$list=$GLOBALS['db']->GetList1($ds,true);
					//print_r($list);					
					foreach($list as $k=>$v){
						/*print_r($v);*/
						if(isset($sqlJson->value)) $secval[]=$v[$val1];
					}				
				}
				$mainval[]=implode(",",$secval);
			}				
			
			
		}
		if(!isset($sqlJson->title)) $sqlJson->title="";
		if(!isset($sqlJson->labeldisplay))
			$lbl=implode(",",$lbl);
		else
			$lbl=implode(",",$lbld);
		$val=implode(":",$mainval);
		$j=json_decode("{}");
		$j->action="dom";
		$j->fun="showchart";
		$j->elem=$sqlJson->output;
		$j->type=$sqlJson->type;
		$j->val=$val;
		$j->label=$lbl;
		$j->title=$sqlJson->title;
		$j->legend=implode(",",$legend);
		if(isset($sqlJson->bg)) $j->bg=$sqlJson->bg;
		setOutput("dom",$GLOBALS["result"],"",$j,true);
		//$j="cmd:dom,fun:showchart;".$sqlJson->output.";".$sqlJson->type.";$val;$lbl;".$sqlJson->title.";".implode(",",$legend);
		//setOutput("dom",$GLOBALS["result"],$j,"",true);		
		
		//setOutput("dom",$result,"",$sqlJson);
	}
	function GetChartNew(&$sqlJson){
		/*1. Initialize*/
		if(!empty($sqlJson->displaytable)){		
			$this->GetTable($sqlJson,$GLOBALS["value"],$GLOBALS["result"]);
			return true;
		}
		$sqlJson->success=false;
		if(empty($sqlJson->value)){
			$sqlJson->error="Value column is empty";
			return false;
		}		
		$db=$GLOBALS['db'];
		$db->error="";		
		$data=array();
		$mainLabelCol=$sqlJson->label;
		$mainLabelList=array();		
		$secondaryLabelCol=isset($sqlJson->secondarylabel)?$sqlJson->secondarylabel:"";
		$secondaryLabelList=array();
		$valueCol=$sqlJson->value;
		if(!isset($sqlJson->title)) $sqlJson->title="";
		if(!isset($sqlJson->type)) $sqlJson->type="bar";
		$bg=array();
		$bghover=array();
		
		/*2. Process Sql Query*/
		$res=$db->GetList1($sqlJson->sql,true);
		//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$sqlJson->sql).'</textarea><br>';print_r($GLOBALS["value"]);
		if(!empty($db->error)) {$sqlJson->error=$db->error;return false;}
						
		if(!empty($res)){
			$sqlJson->success=true;			
			foreach($res as $k=>$v){
				if(is_null($v[$mainLabelCol])) $v[$mainLabelCol]=0;
				if(!empty($secondaryLabelCol) && is_null($v[$secondaryLabelCol])) $v[$secondaryLabelCol]=0;
				if(is_null($v[$valueCol])) $v[$valueCol]=0;
				if(!empty($secondaryLabelCol)){
					if(!in_array($v[$mainLabelCol],$mainLabelList)) $mainLabelList[]=$v[$mainLabelCol];
					if(!in_array($v[$secondaryLabelCol],$secondaryLabelList)) {$secondaryLabelList[]=$v[$secondaryLabelCol];}
					if(!isset($data[$v[$mainLabelCol]."_".$v[$secondaryLabelCol]])){
						$data[$v[$mainLabelCol]."_".$v[$secondaryLabelCol]]=array();						
					}	//echo $v[$mainLabelCol]."_".$v[$secondaryLabelCol]." = ".$v[$valueCol]."<br>"; 				
					$data[$v[$mainLabelCol]."_".$v[$secondaryLabelCol]]=$v[$valueCol];
				}else{
					if(!in_array($v[$mainLabelCol],$mainLabelList)) $mainLabelList[]=$v[$mainLabelCol];						
					if(!isset($data[$v[$mainLabelCol]])){
						$data[$v[$mainLabelCol]]=array();						
					}
					$data[$v[$mainLabelCol]]=$v[$valueCol];
				}
			}
		}
		
		/*Background*/		
		if(!isset($sqlJson->bg)){
			     $bg=array("rgb(255, 99, 132)","rgb(255, 159, 64)","rgb(255, 205, 86)","rgb(75, 192, 192)","rgb(54, 162, 235)","rgb(153, 102, 255)","rgb(201, 203, 207)","rgb(138,170,229)","rgb(204,49,61)","rgb(44,95,45)","rgb(30,39,97)","rgb(64,142,198)","rgb(167,190,174)");
			$bghover=array("rgb(255, 120, 132)","rgb(255, 159, 64)","rgb(255, 205, 120)","rgb(95, 192, 192)","rgb(74, 162, 235)","rgb(153, 122, 255)","rgb(201, 223, 207)","rgb(138,171,225)","rgb(204,49,9)","rgb(151,188,98)","rgb(64,142,198)","rgb(30,39,97)","rgb(231,232,209)");
			shuffle($bg);
			shuffle($bghover);
		}else{
			$bg=$sqlJson->bg;
			if(isset($sqlJson->bghover))
				$bghover=$sqlJson->bghover;
			else
				$bghover=array("rgb(255, 120, 132)","rgb(255, 159, 64)","rgb(255, 205, 120)","rgb(95, 192, 192)","rgb(74, 162, 235)","rgb(153, 122, 255)","rgb(201, 223, 207)");
		}
		
		
		/*3. Format data*/
		$datasets=array();
		if(!empty($secondaryLabelCol)){
			$loopcnt=0;
			foreach($secondaryLabelList as $sl){
				$tempdata=json_decode("{}");
				$tempdata->label=$sl;
				//$tempdata->data=array();
				$tempdata->backgroundColor=isset($bg[$loopcnt])?$bg[$loopcnt]:"rgb(255, 99, 132)";
				$tempdata->hoverBackgroundColor=isset($bghover[$loopcnt])?$bghover[$loopcnt]:"rgb(255, 120, 132)";				
				foreach($mainLabelList as $ml){
					if(isset($data[$ml."_".$sl])){
						$tempdata->data[]=$data[$ml."_".$sl];						
					}else
						$tempdata->data[]=0;
				}
				
				
				$tempdata->data=$tempdata->data;
				
				$datasets[]=$tempdata;
				$loopcnt++;
			}
		}else{
			$tempdata=json_decode("{}");
			$tempdata->label="";
			//$tempdata->data=array();			
			$tempdata->backgroundColor=isset($bg[0])?$bg[0]:"rgb(255, 99, 132)";
			$tempdata->hoverBackgroundColor=isset($bghover[0])?$bghover[0]:"rgb(255, 120, 132)";				
			foreach($mainLabelList as $ml){
				if(isset($data[$ml]))
					$tempdata->data[]=$data[$ml];
				else
					$tempdata->data[]=0;
			}
			if(isset($tempdata->data[0])){
				$tempdata->data=$tempdata->data[0];
			}
			$datasets[]=$tempdata;
		}		
		//print_r($datasets);
		/*Format Output*/
		$j=json_decode("{}");
		$j->action="dom";
		$j->fun="showchartnew";
		$j->elem=$sqlJson->output;
		$j->type=$sqlJson->type;
		$j->value=$datasets;		
		$j->title=$sqlJson->title;
		$j->data=json_decode("{}");
		$j->data->labels=$mainLabelList;
		$j->data->datasets=$datasets;		
		setOutput("dom",$GLOBALS["result"],"",$j,true);
		//$j="cmd:dom,fun:showchart;".$sqlJson->output.";".$sqlJson->type.";$val;$lbl;".$sqlJson->title.";".implode(",",$legend);
		//setOutput("dom",$GLOBALS["result"],$j,"",true);		
		
		//setOutput("dom",$result,"",$sqlJson);
	}
	function SetUserInfo($userid){
		if(empty($userid)) $userid="";
		$sql="SELECT id,username,fullname,profileid,lastlogindate,status,image FROM eirene_users WHERE id='$userid' and recordstatus=1";
		$res=$GLOBALS["db"]->FetchRecord($sql,true);
		
		if(!empty($res))
			$GLOBALS["userinfo"]=$GLOBALS["db"]->FetchRecord($sql,true);
		else{
			$GLOBALS["userinfo"]["id"]="";
			$GLOBALS["userinfo"]["username"]="";
			$GLOBALS["userinfo"]["fullname"]="";
			$GLOBALS["userinfo"]["profileid"]="";
			$GLOBALS["userinfo"]["lastlogindate"]="";
			$GLOBALS["userinfo"]["status"]="";
			$GLOBALS["userinfo"]["image"]="";
		}
		$plugin=$GLOBALS["value"]["pluginid"];		
		$sql="SELECT id,pluginname,version,icon,status,maintanence,iswebsite,validationneeded,initialstmt FROM eirene_plugin where (id='$plugin' or pluginname='$plugin')";
		$res=$GLOBALS["db"]->FetchRecord($sql,true);
		
		if(!empty($res)){
			$GLOBALS["plugin"]=$GLOBALS["db"]->FetchRecord($sql,true);
			$GLOBALS["value"]["pluginid"]=$GLOBALS["plugin"]["id"];
			if($GLOBALS["plugin"]["iswebsite"]==1) $GLOBALS["plugin"]["validationneeded"]=0;
		}else{
			$GLOBALS["plugin"]["id"]="";
			$GLOBALS["plugin"]["pluginname"]="";
			$GLOBALS["plugin"]["version"]="";
			$GLOBALS["plugin"]["icon"]="";
			$GLOBALS["plugin"]["status"]="";
			$GLOBALS["plugin"]["maintanence"]="";
			$GLOBALS["plugin"]["iswebsite"]=0;
			$GLOBALS["plugin"]["validationneeded"]=1;
			$GLOBALS["plugin"]["initialstmt"]="";
		}		
	}
	function RunIf(&$json){
		/*Following nodes will be processed and if-object if any will be processed.: outputto,output,action*/
		if(isset($json->outputto)) $json->outputto=GetStringFromJson($json,"outputto"," ");
		if(isset($json->output)) $json->output=GetStringFromJson($json,"output"," ");
		if(isset($json->appendoutput)) $json->appendoutput=GetStringFromJson($json,"appendoutput"," ");
		if(isset($json->action)) $json->action=GetStringFromJson($json,"action"," ");
		//print_r($json);echo "<br>";
		
		if(!isset($json->runif)) return true;				
		$chk=false;
		if(is_string($json->runif)){
			$js=json_decode("{}");
			$js->runif=$json->runif;
			$chk=$this->CheckIfCondition($js,$GLOBALS["value"],$GLOBALS["result"]);			
		}else if(is_array($json->runif)){
			$chk=true;
			foreach($json->runif as $r){
				if(!is_string($r)) continue;
				$js=json_decode("{}");
				$js->runif=$r;
				$chk1=$this->CheckIfCondition($js,$GLOBALS["value"],$GLOBALS["result"]);			
				if(!$chk1){
					$chk=false;
					break;
				}
			}
		}
		return $chk;
	}
	function PrintForm(&$json){
		$json->success=false;
		/*printform will only produce HTML - printing needs to be executed separetely*/
		if(!isset($json->formname)) {$json->error="Formname not set";return false;}
		if(!isset($json->template)) {$json->error="Template not defined";return false;}

		$formname=$json->formname;
		$db=$GLOBALS["db"];
		$sql="SELECT form FROM eirene_plugin WHERE id='".$GLOBALS["value"]["pluginid"]."'";
		$res=$db->GetValue($sql);
		if(!empty($db->error)){
			$json->error=$db->error;
			return false;
		}else
			$json->success=true;

		$res=json_decode($res);
		$form="";
		foreach($res as $r){
			if(is_object($r)){
				if($r->name==$formname){
					$form=$r;
					$res=null;
					break;
				}
			}
		}

		/*get value from db*/
		$resval=$db->FetchRecord($json->sql,true);

		/*build html string*/
		if(!isset($json->parent)) $json->parent="div";
		if(!isset($json->printemptyvalue)) $json->printemptyvalue=1;

		$str="<".$json->parent.">";
		
		/*each element of formval will be array(label,value)*/
		$formval=array();
		foreach($form->field as $f){
			if(isset($f->donotprint)) continue;
			$label=!isset($f->printlabel)?$f->label:$f->printlabel;
			if(isset($resval[$f->name])){
				if(!empty($resval[$f->name]))
					$formval[$f->name]=array($label,$resval[$f->name]);
				else{
					$printempty=true;
					if(isset($json->printemptyvalue)){
						if($json->printemptyvalue==0 || $json->printemptyvalue==false) $printempty=false;
					}
					if($printempty) $formval[$f->name]=array($label,"");
				}
			}
		}
		
		if(!isset($json->structure)){
			foreach($formval as $k=>$f)		
				$str.=str_replace(array("||label||","||value||"),array($f[0],$f[1]),$json->template);							
		}else{
			if(!is_array($json->structure)){
				$json->error="Incorrect structure definition. Array expected.";
				return false;
			}
			
			foreach($json->structure as $s){
				if(is_string($s)){
					if(isset($formval[$s]))
						$str.=str_replace(array("||label||","||value||"),$formval[$s],$json->template);							
				}else if(is_object($s)){
					if(!isset($json->templateforgroup)) continue;
					$label=isset($s->label)?$s->label:"";
					if(isset($s->grp)){
						$value="";
						$temp="";
						if(isset($s->template)){
							$temp1=$s->template;
							if(isset($json->$temp1))
								$temp=$json->$temp1;
							else{
								$json->error="Template $temp1 not defined";
								return false;
							}
						}else
							$temp=$json->templateforgroup;
						foreach($s->grp as $g){
							if(is_string($g)){
								if(isset($formval[$g]))
									$value.=str_replace(array("||label||","||value||"),$formval[$g],$temp);							
							}
						}
						$chk=true;
						if(isset($json->printemptyvalue)){
							if($json->printemptyvalue==0 || $json->printemptyvalue==false){
								if(empty($value)) $chk=false;
							}
						}
						
						/*print only if value is non empty or setting done in printemptyvalue to allow printing empty value*/
						if($chk)
							$str.=str_replace(array("||label||","||value||"),array($label,$value),$json->template);
					}else if(isset($s->label) && isset($s->value)){
						$str.=str_replace(array("||label||","||value||"),array($s->label,$formval[$s][1]),$json->template);
					}
				}
			}
		}
		$str.="</".explode(" ",$json->parent)[0].">";
		$str=str_replace("\n","<br>",$str);
		setOutput("res",$GLOBALS["result"],$str,$json);
	}
	function Loop(&$json){	
		$json->success=false;	
		if(!isset($json->loopsqlid)) return "";
		if(!isset($json->looptype)) $json->looptype="commavalue";/*other options are number. If number is set then startnumber and endnumber are mandatory*/
		
		/*merge values from loopsqlid*/
		$db=$GLOBALS["db"];
		$sql="SELECT sql_statement FROM eirene_sqlstatements WHERE pluginid='".$GLOBALS["value"]["pluginid"]."' and customid='".$json->loopsqlid."'";
		$res1=$db->FetchRecord($sql);							
		$res1=json_encode($res1);
		$res1=json_decode($res1);							
		$res1=json_decode($res1->sql_statement);
		$master=json_decode("{}");
		foreach($res1 as $k=>$v){
			if($k!="output" && $k!="outputto" && $k!="loopvalue" && $k!="loopsqlid" && $k!="looptype")
			$master->$k=$v;
		}
		$master->output=$json->output;
		$master->outputto=$json->outputto;
		$master->appendoutput=true;
		
		$ux=new EireneUx();
		$api=new EireneApi();
		$api1=new EireneCommandHandler();
		$loop=array();
		//if(isset($json->start)) $json->start=$ux->SupplyParamValue($json->start,$sqlJson);
		//if(isset($json->end)) $json->end=$ux->SupplyParamValue($json->end,$sqlJson);
		if(isset($json->loopvalue)) $json->loopvalue=$ux->SupplyParamValue($json->loopvalue,$sqlJson);
		$json->loopvalue=str_replace("  "," ",trim($json->loopvalue));
		if(empty($json->loopvalue)){$json->error="LoopValue is empty";return false;}	
		if($json->looptype=="commavalue"){
			$loop=explode(",",$json->loopvalue);
		}else if($json->looptype=="csv"){
			$loop=str_getcsv($json->loopvalue, "\n");
		}else if($json->looptype=="number"){
			//if(!isset($json->start) || !isset($json->end)) {$json->error="Incomplete definition: start and end is not defined.";return "";}
			$st=explode(" ",$json->loopvalue);
			if(count($st)>=2){
				for($i=$st[0];$i<=$st[1];$i++){
					$loop[]=$i;
				}
			}
		}else if($json->looptype=="date"){
			//if(!isset($json->start) || !isset($json->end)) {$json->error="Incomplete definition: start and end is not defined.";return "";}
			$st=explode(" ",$json->loopvalue);
			if(count($st)>=2){
				$startDate = new DateTime(str_replace(array("-","/","."),"",$st[0]));
				$endDate = new DateTime(str_replace(array("-","/","."),"",$st[1]));
				while ($startDate <= $endDate) {
				  $loop[]=date_format($startDate,'Y-m-d');
				  $startDate=$startDate->add(new DateInterval('P1D'));			  
				}
			}
		}else if($json->looptype="sql" && strpos($json->start,"select") && strpos($json->start,"from") ){
			$loop=$GLOBALS["db"]->GetList1($json->loopvalue,true);
		}
		if(empty($loop)){$json->error="Loop is empty";return false;}		
		
		$mas_copy=$master;
		foreach($loop as $l){
			$GLOBALS["value"]["currentloopvalue"]=$l;			
			//RunFunctions($json,$api,$GLOBALS["value"],$GLOBALS["result"],$ux);
			$master=$mas_copy;	
			if(is_array($l)){
				foreach($l as $lk=>$ll){
					if(!isset($master->$lk)) $master->$lk=$ll;
				}
			}else if(is_string($l)){
				if(!isset($master->currentloopvalue)) $master->currentloopvalue=$l;
			}
			$api1->ProcessRequest($master);
		}
		$json->success=true;
	}
}

class EireneHTMLBuilder{
	private $SupportedAttributes=array();
	function __construct(){
		/*In the below array -> the key will be defined type or can be shorthand and the value will be html attribute to be written exactly the way html expects it*/
		$this->SupportedAttributes["clearDataAfterSave"]="eirene-clearDataAfterSave";
		$this->SupportedAttributes["title"]="title";
		$this->SupportedAttributes["placeholder"]="placeholder";		
		$this->SupportedAttributes["type"]="type";
		$this->SupportedAttributes["class"]="class";
		$this->SupportedAttributes["style"]="style";
		$this->SupportedAttributes["name"]="name";
		$this->SupportedAttributes["nm"]="name";
		$this->SupportedAttributes["id"]="id";
		$this->SupportedAttributes["maxlength"]="maxlength";
		$this->SupportedAttributes["value"]="value";
		$this->SupportedAttributes["href"]="href";
		$this->SupportedAttributes["target"]="target";
		
		/*below attributes are metro4 attributes which uses a dash(-) and is not supported by php object, hence special arrangement is made*/
		$this->SupportedAttributes["dataon"]="data-on";/*used for switch*/
		$this->SupportedAttributes["dataoff"]="data-off";/*used for switch*/
		$this->SupportedAttributes["checkvalue"]="checkvalue";/*used for switch and checkbox*/
		$this->SupportedAttributes["uncheckvalue"]="uncheckvalue";/*used for switch and checkbox*/
		$this->SupportedAttributes["datarole"]="data-role";
		$this->SupportedAttributes["dataappend"]="data-append";/*used for input*/
		$this->SupportedAttributes["dataprepend"]="data-prepend";/*used for input*/
		/*metro panel classes*/
		$this->SupportedAttributes["dataclstitle"]= "data-cls-title";
		$this->SupportedAttributes["dataclstitlecaption"]= "data-cls-title-caption";
		$this->SupportedAttributes["dataclstitleicon"]= "data-cls-title-icon";
		$this->SupportedAttributes["dataclscontent"]= "data-cls-content";
		$this->SupportedAttributes["dataclscollapsetoggle"]="data-cls-collapse-toggle";
		/*metro tag classes*/
		$this->SupportedAttributes["datatagseparator"]="data-tag-separator";
		$this->SupportedAttributes["datatagtrigger"]="data-tag-trigger";
		/*properties*/
		$this->SupportedAttributes["checked"]="";
		$this->SupportedAttributes["selected"]="";
		$this->SupportedAttributes["required"]="";
		$this->SupportedAttributes["readonly"]="";
		
		/*js events*/
		$this->SupportedAttributes["onclick"]="onclick";
		$this->SupportedAttributes["onblur"]="onblur";
		$this->SupportedAttributes["onfocus"]="onfocus";
		$this->SupportedAttributes["ondblclick"]="ondblclick";
		$this->SupportedAttributes["onchange"]="onChange";
		$this->SupportedAttributes["showDialogOnFocus"]="onfocus";
		
		
	}
	function GetHTML($json){
		$result=array();$str="";		
		/*json here will contain json definition of html elements*/		
		/*check*/
		if(!isset($json->elem)) return "";
		if(is_string($json->elem)) return "";
		
		/*
		if(is_object($json->elem)){
			if(!isset($json->elem->runif)){
				$str=$this->ProcessJsonToGetHTML($json->elem);
			}else{
				$api=new EireneApi();
				$chk=$api->CheckIfCondition($json->elem->runif,$GLOBALS["value"],$GLOBALS["result"]);
				if($chk==true) $str=$this->ProcessJsonToGetHTML($json->elem);
			}
		}
		else if(is_array($json->elem)){
		*/
		
		if(is_object($json->elem)) $json->elem=array($json->elem);
		$api=new EireneApi();
		foreach($json->elem as &$e){
			//print_r($e);echo "<br>";
			if(isset($json->sqlid)) $e->sqlid=$json->sqlid;
			/*initialize tempcheck*/
			$tempcheck=true;
			/*check runif condition*/
			if(isset($e->runif)) $tempcheck=$api->CheckIfCondition($e->runif,$GLOBALS["value"],$GLOBALS["result"]);
			/*process elem*/
			if($tempcheck==true) $str=$this->ProcessJsonToGetHTML($e);
			
			/*
			if(!isset($e->runif))
				
			else{
				$chk=
				if($chk==true) $str=$this->ProcessJsonToGetHTML($json->elem);
			}
			*/
		}			
		
		return $str;
	}
	function ProcessJsonToGetHTML($json){		
		/*json here will contain json definition of buttons*/
		/*elem must be an object {"output":"htmlid","parent":"","elem":[]}*/
		/*check*/
		if(!isset($json)) return "";
		if(!is_object($json)) return "";
		$elem="";		
		if(is_object($json)){
			if(isset($json->elem))
				$elem=$json->elem;
			else
				$elem=$json;
		}
		
		/*building html element*/
		$str=array();
		$api=new EireneApi();
		foreach($elem as &$bb){
			if(isset($elem->sqlid)) $bb->sqlid=$elem->sqlid;			
			if(isset($bb->displaynone)) continue;
			if(!isset($bb->name)) continue;			
			if($api->RunIf($bb)==false) continue;
			if(!isset($bb->type))$bb->type="button";			
			if($bb->type!=="dropdown"){
				$str[]=$this->GetHTMLElement($bb);
			}else{
				if(!isset($bb->grp)) continue;
				if(!is_array($bb->grp)) continue;
				$str[]="<div><button id='drp_".str_replace(" ","_",$bb->name)."' class='dropdown-toggle'>".$bb->value."</button><ul class='d-menu' data-role='dropdown' style='display:none' data-toggle-element='#drp_".str_replace(" ","_",$bb->name)."'>";
				foreach($bb->grp as $b){
					$str[]=$this->GetHTMLElement($b);
				}
				$str[]="</ul></div>";
			}			
		}
		$str=implode("",$str);
		
		/*building html element parent*/
		$parent="div";		
		if(isset($json->elem->parent))$parent=$json->elem->parent;		
		if(isset($json->parent))$parent=$json->parent;
		$parent=str_replace(array("<",">"),"",$parent);
		
		$parent="<".$parent." ".$this->SetAttribute($json).">".$str."</".$parent.">";		
		if(!empty($json->output)){
			setOutput("res",$GLOBALS["result"],$parent,$json);
			return "";
		}else
			return $parent;		
	}
	function SetAttribute($json){
		/*$json will contain the json definition for one element and not entire button array and $elem will be info about the HTML element name for e.g. text, textarea, a, select etc.*/
		$attrib=array();
		foreach($json as $k=>$v){
			if(array_key_exists($k, $this->SupportedAttributes)){
				$json->$k=GetStringFromJson($json,$k,"");
				if(substr($k,0,2)=="on"){
					$temp=str_replace("'","&apos;",$json->$k);
					$attrib[]=$this->SupportedAttributes[$k].'="'.$temp.'"';
				}else if($k=="showDialogOnFocus"){					
					/*saving or any other command from the element will be transferred to the dialog except for onfocus*/
					$attrib[]='onfocus="Eirene.currentelem=$(this);let a=$(this).clone();a.removeAttr(&apos;onfocus&apos;);a.css(&apos;height&apos;,&apos;350px&apos;);a.css(&apos;width&apos;,&apos;90%&apos;);a.attr(&apos;onchange&apos;,&apos;Eirene.currentelem.val($(this).val())&apos;);Eirene.temp=a[0].outerHTML;showDialog(&apos;DialogBox&apos;,Eirene.temp,&apos;info&apos;)"';					
				}else if(!empty($this->SupportedAttributes[$k])){
					$attrib[]=$this->SupportedAttributes[$k].'="'.$json->$k.'"';
				}else if(empty($this->SupportedAttributes[$k])){
					$attrib[]=$k;
				}	
			}
		}
		
		$attrib=implode(" ",$attrib);
		return $attrib;		
	}
	function GetHTMLElement($elem){
		/*$elem will be a php object which will contain definition of html element*/
		/*initialize*/
		$type=""; $str="";	
		if(!isset($elem->type))
			$type="button";
		else{
			if(in_string('float,double,varchar',$elem->type) || stripos($elem->type,"char") || stripos($elem->type,"int")){
				$numericvalueofstorage=preg_replace('/\D/','',$elem->type);
				$elem->maxlength=$numericvalueofstorage;
				$elem->type="text";
			}else if(strpos($elem->type,"enum(")){				
				$opt=str_ireplace("enum(","",$elem->type);
				$opt=str_replace("'","",$opt);
				$opt=str_replace(")","",$opt);
				$elem->option=trim($opt);
				$elem->type="select";
			}
			$type=$elem->type;
		}
		
		$ux=new EireneUx();
		if(!empty($elem->value))
			$elem->value=GetStringFromJson($elem,"value");
		else
			$elem->value="";
		
		/*building html based upon specified type*/
		if($type=="button" || $type=="span" || $type=="div" || $type=="label"){			
			$str="<$type ";
			$str.=$this->setAttribute($elem);
			$str.=">";
			$str.=$elem->value;
			$str.="</".$type.">";			
		}if($type=="buttonspan"){			
			$str="<button ";
			$str.=$this->setAttribute($elem);
			$str.="><span ";
			if(isset($elem->icon)) $str.='class="'.$elem->icon.'"';
			$str.=">";
			$str.="</span>";
			$str.=$elem->value;
			$str.="</button>";			
		}if($type=="iconbox"){
			$str="<div ";
			if(isset($elem->class))
				$elem->class.=" icon-box border bd-default";
			else
				$elem->class="icon-box border bd-default";
			$str.=$this->setAttribute($elem).">";
			/*icon*/
			if(!isset($elem->icon))$elem->icon="";
			$str.="<div class='icon'><span class='".$elem->icon."'></span></div>";
			/*content*/
			if(!isset($elem->valueexplaination))$elem->valueexplaination="";
			if(!isset($elem->value))$elem->value="";
			
			$str.="<div class='content p-4'>";
				$str.="<div class='text-upper'>".$elem->valueexplaination."</div>";
				$str.="<div class='text-upper text-bold text-lead'>".$elem->value."</div>";
			$str.="</div></div>";		
								
					
		}else if($type=="checkbox"){
			$str="<input type='checkbox' ";
			$str.=$this->setAttribute($elem);		
			$str.="/>";			
		}else if($type=="switch"){
			$str="<input type='checkbox' data-role='switch' ";			
			if(!isset($elem->dataon) && !isset($elem->dataoff)){
				if(isset($elem->option)){
					$opt=explode(",",$elem->option);
					$optval=array();
					$optdisplay=array();
					if(count($opt)==2){
						if(strpos($opt[1],":")){
							$opt[0]=explode(":",$opt[0]);
							$opt[1]=explode(":",$opt[1]);
							$optdisplay[]=$opt[0][1];
							$optdisplay[]=$opt[1][1];
							$optval[]=$opt[0][0];
							$optval[]=$opt[1][0];
						}else{
							$optdisplay[]=$opt[0];
							$optdisplay[]=$opt[1];
							$optval[]=$opt[0];
							$optval[]=$opt[1];
						}
						$elem->uncheckvalue=$optval[0];
						$elem->checkvalue=$optval[1];
						$str.='data-off="'.$optdisplay[0].'" data-on="'.$optdisplay[1].'"';
					}else{
						$str.='data-on="on" data-off="off"';
						$elem->uncheckvalue=0;
						$elem->checkvalue=1;
					}
				}else{
					$str.='data-on="on" data-off="off"';
					$elem->uncheckvalue=0;
					$elem->checkvalue=1;
				}
			}
			
			$str.=$this->setAttribute($elem);
			//print_r($this->setAttribute($elem));echo "<br><br>";
			$str.="/>";			
		}else if($type=="text"){
			$str="<input type='text' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";		
		}else if($type=="link" || $type=="a"){
			$str="<a ";
			if(!isset($elem->target)) $elem->target="_blank";
			$str.=$this->setAttribute($elem);			
			$str.=">";
			if(!empty($elem->value)) $str.=$elem->value;
			$str.="</a>";		
		}else if($type=="lookup"){			
			if(!isset($elem->id)) $elem->id=$elem->name.rand(1,9999);
			//if(!isset($elem->id)) $elem->id=$elem->name;
			$tempsqlid="";
			$searchelemid="$(&apos;#".$elem->id."searchtext&apos;).val()";
			if(isset($elem->lookup->sqlid)) $tempsqlid=$elem->lookup->sqlid;
			if(!isset($elem->class)) $elem->class="";
			$str="<div><input id='".$elem->id."searchtext' class='lookupsearchtext' type='text'";
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';	
			$str.="onkeyup='if(event.keyCode==13){Eirene.runStmt(&apos;".$tempsqlid."&apos;,{searchtext:".$searchelemid.",var".$tempsqlid."_output:&apos;#".$elem->id."searchdiv&apos;,var".$tempsqlid."_targetelem:&apos;".$elem->id."&apos;})}else{var srctxt=$(this).val().toString().toLowerCase();$(&apos;#".$elem->id."searchdiv select option&apos;).hide();$(&apos;#".$elem->id."searchdiv select option&apos;).each(function(key,value){if(value.text.toLowerCase().indexOf(srctxt)>-1){this.style.display=&apos;&apos;}})}' class='".$elem->class."' placeholder='Enter text here' >";
			
			
			$str.="<span id='".$elem->id."searchbtn' class='mif-search' style='margin-left:-36px;margin-top:10px;z-index:10' onclick='Eirene.runStmt(&apos;".$tempsqlid."&apos;,{searchtext:".$searchelemid.",var".$tempsqlid."_output:&apos;#".$elem->id."searchdiv&apos;,var".$tempsqlid."_targetelem:&apos;".$elem->id."&apos;})'></span>";
			$str.="<input type='text' style='display:none' ";
			if(!empty($elem->class)) 
				$elem->class.=" lookup";
			else
				$elem->class="lookup";
			$str.=$this->setAttribute($elem);			
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';		
			$str.="/>";
			$str.="<div id='".$elem->id."searchdiv' style='width:92%'></div></div>";			
		}else if($type=="textarea"){
			$str="<textarea ";
			$str.=$this->setAttribute($elem);			
			$str.=">";
			if(!empty($elem->value)) $str.=$elem->value;
			$str.="</textarea>";
		}else if($type=="texteditor"){
			$str="<textarea ";
			if(!isset($elem->class))
				$elem->class="texteditor";
			else
				$elem->class.=" texteditor";
			$str.=$this->setAttribute($elem);			
			$str.=">";
			if(!empty($elem->value)) $str.=$elem->value;
			$str.="</textarea>";
			$str.="<script>convertToTextEditor($('textarea[name=".$elem->name."]'))</script>";
		}else if($type=="number" || $type=="numeric"|| $type=="int"|| $type=="tinyint"){
			$str="<input type='number' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";		
		}else if($type=="hidden"){
			$str="<input type='hidden' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";		
		}else if($type=="password"){
			$str="<input type='password' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";		
		}else if($type=="datepicker" || $type=="date"){
			$str="<input type='date' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";			
		}else if($type=="datetime"){
			$str="<input type='datetime-local' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.str_replace(" ","T",$elem->value).'"';
			$str.="/>";
		}else if($type=="time"){
			$str="<input type='time' ";
			$str.=$this->setAttribute($elem);
			if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			$str.="/>";
		}else if($type=="return"){
			$str=$elem->value;
		}else if($type=="select"){
			$str="<select ";
			$str.=$this->setAttribute($elem);
			//if(!empty($elem->value)) $str.=' value="'.$elem->value.'"';
			if(!empty($elem->values)) $str.=' values="'.$elem->values.'"';/*values and value are used for converting comma seperated item to option html structure*/
			if(!empty($elem->option)) $elem->option=GetStringFromJson($elem,"option");
			if(empty($elem->values) && !empty($elem->option)){								
				if(stripos($elem->option,"SELECT")!==false && stripos($elem->option,"FROM")!==false){
					
				}else{
					$str.=' values="'.$elem->option.'"';
				}
			}
			$str.=">";			
			if(!empty($elem->option)){
				$val="";				
				if(isset($elem->selectedvalue)){
					$val=$ux->SupplyParamValue($elem->selectedvalue,$sqlJson);
					if(stripos($val,"SELECT")!==false && stripos($val,"FROM")!==false){
						$val=$GLOBALS["db"]->GetValue($val);
					}					
				}
				if(isset($elem->option)) $elem->option=$ux->SupplyParamValue($elem->option,$sqlJson);
				$commaseperatedvalue=false;
				if(isset($elem->commaseperatedvalue)) $commaseperatedvalue=true;
				if(!isset($elem->addemptyvalue))
					$str.=FillDropdownHTML($elem->option,$val,$commaseperatedvalue);
				else
					$str.=FillDropdownHTML($elem->option,$val,$commaseperatedvalue,true);
			}
			$str.="</select>";
		}else if($type=="card"){
			if(!isset($elem->image)) $elem->image="";
			if(!isset($elem->imageTitle)) $elem->imageTitle="";
			$str="<div class='card image-header ";
			$str.=str_replace('class="','class="card image-header ',$this->setAttribute($elem));
			$str.=">";			
			/*card image*/
			if(!empty($elem->image)){
					if(!isset($elem->imageTitle)) $elem->imageTitle="";
					$str.="<div class='card-header fg-white ";
					if(isset($elem->imageClass)) $str.=$elem->imageClass;
					$str.="'";
					$str.=" style='background-image: url(".$elem->image.");";
					if(isset($elem->imageStyle)) $str.=$elem->imageStyle;
					$str.="'>".$elem->imageTitle."</div>";
			}
			
			/*card content*/
			if(isset($elem->content))
				$str.=	"<div class='card-content p-2'>".$elem->content."</div>";
			
			/*card footer*/
			if(isset($elem->footer))
				$str.="<div class='card-footer'>".$elem->footer."</div>";
											
			$str.="</div>";
		}else if($type=="accordian"){
			if(isset($elem->frame)){
				$str="<div  data-role='accordion' data-one-frame='true' data-show-active='true' ";
				$str.=$this->setAttribute($elem);
				$str.=">";
				foreach($elem->frame as $f){
					$str.="<div class='frame";
					if(!empty($f->active)) $str.=" active";
					$str.="'>";
					$str.="<div class='heading'>".$f->heading."</div>";					
					if(is_object($f->content)){
						if(isset($f->content->type)){
							$f->content=$this->GetHTMLElement($f->content);							
						}
					}else if(is_array($f->content)){
						foreach($f->content as $k=>$ff){
							if(is_object($ff)){
								if(isset($ff->type)){
									$f->content[$k]=$this->GetHTMLElement($ff);		
								}
							}
						}
					}
					$f->content=GetStringFromJson($f,"content");
					$str.="<div class='content'>".$f->content."</div>";
					$str.="</div>";
				}					
				$str.="</div>";
			}
		}else if($type=="tab"){
			$str.="<ul data-role='tabs' data-expand-point='md' ";
			$str.=$this->setAttribute($elem);
			$content="";$cnt=1;
			$num=rand(1,999);
			$str.=">";
				if(isset($elem->tabs)){
					if(is_array($elem->tabs)){
						foreach($elem->tabs as $t){
						    $str.="<li><a href='#target".$num."_".$cnt."'>".$t->heading."</a></li>";
							$content.="<div id='target".$num."_".$cnt."'>";							
							if(is_object($t->content)){
								if(isset($t->content->type)){
									$t->content=$this->GetHTMLElement($t->content);							
								}
							}else if(is_array($t->content)){
								foreach($t->content as $k=>$tt){
									if(is_object($tt)){
										if(isset($tt->type)){
											$t->content[$k]=$this->GetHTMLElement($tt);		
										}
									}
								}
							}
							$t->content=GetStringFromJson($t,"content");							
							$content.=$t->content."</div>";
							$cnt++;
						}
					}
				}
			$str.="</ul>";
			$str.="<div class='border bd-default no-border-top p-2'>".$content;			
			$str.="</div>";
		}else if ($type=="hmenu"){
			if(!isset($elem->menu)) return "";
			if(!is_array($elem->menu)) return "";
			/*syntax "menu":[{"Beneficiary":{"text":"onclick","text1":"onclick1"}},"staff":{"text":"onclick","text1":"onclick1"}}]*/
			$str.="<ul class='h-menu'>";
			foreach($elem->menu as $k=>$m){
				foreach($m as $menutext=>$menuobject){
					$str.="<li><a href='#' class='dropdown-toggle'>".$menutext."</a>";
					$str.="<ul class='d-menu' data-role='dropdown'>";
					foreach($menuobject as $text=>$onclick){					
						$onclick=str_replace("'","&apos;",$onclick);
						$str.="<li onclick='".$onclick."'><a href='#' >".str_replace("_"," ",$text)."</a></li>";
					}
					$str.="</ul>";
				}
			}
			$str.="</ul>";							
		}else if($type=="script"){
			$str.="<script>";
			$str.=$elem->value;
			$str.="</script>";
		}else if($type=="panel"){
			$str.="<div data-role='panel'";
			$str.="data-title-caption='";
			if(isset($elem->title))
				$str.=$elem->title;
			else
				$str.="Panel Title";
			$str.="'";
			$str.="data-title-icon='<span class=&apos;mif-apps&apos;></span>'";
			$str.="data-collapsed='true' data-collapsible='true'";
			$str.=">";
			if(isset($elem->value)) $str.=$elem->value;
			$str.="</div>";
		}
		
		/*label*/
		if(!empty($elem->label)){
			$lblclass=empty($elem->labelclass)?"mr-1":$elem->labelclass;
			$lblvalue=$elem->label;
			if(isset($elem->required)) $lblvalue.="*";
			$str="<label class='".$lblclass."'>".$elem->label."</label>".$str;
		}
		/*appendhtml*/
		if(!empty($elem->append)) $str.=GetStringFromJson($elem,"append");
		/*parent*/
		if(isset($elem->parent)){
			$elem->parent=trim($elem->parent);
			$tempelem=explode(" ",$elem->parent)[0];
			$elem->parent=str_replace(array("<",">"),"",$elem->parent);
			$str="<".$elem->parent.">".$str."</".$tempelem.">";
		}
		return $str;
	}	
}

class EireneCommandHandler{
	private $api;
	private $ux;
	private $htmlbuilder;
	public $Sql;
	function __construct(){
		$this->api=new EireneApi();
		$this->ux=new EireneUx();
		$this->Sql=new EireneSql();
		$this->htmlbuilder=new EireneHTMLBuilder();
	}
	function ProcessRequest($sqlJson){	
		$json=array();
		if(is_array($sqlJson)){
			$json=$sqlJson;
		}else if($sqlJson->action=="MULTISTMT"){
			foreach($sqlJson->multistmt as $j){
				if(isset($sqlJson->sqlid)) $j->sqlid=$sqlJson->sqlid;
				$json[]=$j;
			}			
		}else
			$json=array($sqlJson);
		
		return $this->ExecuteCommand($json); /*this will return additional stmt which then needs to be processsed*/
	}
	
	function ExecuteCommand($json){
		/*$json here will be array of commands*/		
		$additionalstmt=array();
		
		foreach($json as $j){
			if(isset($json->sqlid)) $j->sqlid=$json->sqlid;			
			/*Step 1: Run If*/			
			if(isset($j->runif)){
				//print_r($j->runif);echo "<br>";
				if(!$this->api->CheckIfCondition($j->runif,$GLOBALS["value"],$GLOBALS["result"])){
					//print_r($j->runif);echo "<br><br>";
					$j->success=false;
					if(isset($j->onfailure)){						
						//run on failure commands as defined.
						$add=$this->GetNewStmt($j);
						if(!empty($add))$additionalstmt[]=$add;
					}
					continue;
				}
			}
			
			/*Step 2: merge sql nodes if defined*/
			$this->ux->MergeSqlCommand($j);
			
			/*Step 3: Execute Pre Process*/
			if(isset($j->prerun)){
				/*prerun must be object or array of object*/
				GetStringFromJson($j,"prerun");
			}					
			
			/*step 4: Set Global Value if any "globalvalue":{"var1":"b","var2":"||c||"}*/
			if(isset($j->globalvalue)){
				if(is_object($j->globalvalue)){
					foreach($j->globalvalue as $k=>$v){						
						$v=GetStringFromJson($j,$k,"");
						if($v!="") $GLOBALS["value"][$k]=$v;						
					}
				}				
			}		
			
			/*Step 5: Get SQL Statement */
			//$j->sql=$this->ux->GetSql($j);			
			$j->sql=$this->Sql->ProcessSqlJson($j);			
			//echo $j->sql;	
			
			/*Step 6: Run Command and set success status*/
			$this->RunFunctions($j,$this->Sql);			
			$GLOBALS["success"]=!isset($j->success)?false:$j->success;
			if(isset($j->onsuccess)) $j->onsuccess=GetStringFromJson($j,"onsuccess","-",true);
			if(isset($j->onfailure)) $j->onfailure=GetStringFromJson($j,"onfailure","-",true);
			//print_r($j->onsuccess);
			/*Step 7: Set html element*/
			$this->htmlbuilder->GetHTML($j);			
			
			/*Step 8: Run onsucces/onfailure commands*/
			$add=$this->GetNewStmt($j);			
			if(!empty($add))$additionalstmt[]=$add;
			
			/*Step 9: Execute Post Process*/
			if(isset($j->postrun)){
				/*postrun must be object or array of object*/
				GetStringFromJson($j,"postrun");
			}
			
			/*Step 10: Run php functions*/			
			if(isset($j->phpfunction)){
				$jchk=false;
				if(!isset($j->runonlyonsuccess)) 
					$jchk=true;
				else{
					if($j->success) $jchk=true;
				}
				
				$success=isset($json->success)?$json->success:"";
				if($jchk)
					$this->api->RunPHPFunction($j);
				$j->success=$success;
			}
			
			/*Step 11: Run js functions*/
			if(isset($j->jsfunction)){
				$j->jsfunction=GetStringFromJson($j,"jsfunction",",");
				setOutput("function",$GLOBALS["result"],$j->jsfunction,"",true);				
			}
			
			/*Step 12: Check for error if any*/
			if(!empty($j->error)){
				if(!isset($j->sqlid)) {$j->sqlid="";}
				setOutput("error",$GLOBALS["result"],$j->sqlid.": ".$j->error,"");
				//echo "<textarea>".$j->sql."</textarea><br>";
				break;
			}
			
			/*step 13: Sql statement*/
			if(!empty($j->sqlstatement)){
				$this->api->RunSqlStatement($j);
				/*format "sqlstatement":{"a":"statement1","b":"statement2"}*/				
			}
			
			/*step 14: Eval*/
			if(isset($j->evaluate)){
				$j->evaluate=GetStringFromJson($j,"evaluate");
				eval($j->evaluate);				
			}			
		}
		return implode("-",$additionalstmt);		
	}	
	function GetNewStmt(&$json){
		$new_stmt="";		
		if(isset($json->success)){		
			if($json->success){			
				/*not supported ifsaved ifrows ifdeleted ifuploaded*/
				if(!empty($json->onsuccess)){
					$new_stmt=$json->onsuccess;
					//$new_stmt=GetStringFromJson($json,"onsuccess","-",true);										
				}else if(isset($json->then) || isset($json->if) || $json->action=="IF"){					
					$new_stmt=$json->then;
					//$new_stmt=GetStringFromJson($json,"then","-",true);
					//$new_stmt=$json->then;											
				}
			}else{			
				/*not supported ifnotsaved ifnorows ifnotdeleted ifnotuploaded*/
				if(!empty($json->onfailure)){
					$new_stmt=$json->onfailure;
					//$new_stmt=GetStringFromJson($json,"onfailure","-",true);
					//$new_stmt=implode("-",$json->onfailure);						
				}else if($json->action=="IF"){					
					if(isset($json->else)){
						$new_stmt=$json->else;
					}
						//$new_stmt=GetStringFromJson($json,"else","-",true);
						//$new_stmt=$json->else;
				}
			}
		}
		
		$new_stmt=$this->setAdditionalStmt($new_stmt);
		
		if(!empty($new_stmt)) $json->additionalstmt=empty($json->additionalstmt)?$new_stmt:$json->additionalstmt."-".$new_stmt;
		return $new_stmt;
	}
	function setAdditionalStmt($new_stmt){
		$new="";
		if(is_string($new_stmt)) $new_stmt=explode("-",$new_stmt);
		if(!empty($new_stmt)){			
			$new=array();
			$ux=$this->ux;
			$len=count($new_stmt);
			$tempjs=json_decode("{}");
			for($iii=0;$iii<$len;$iii++){
				$n=trim($new_stmt[$iii]);
				if(is_object($n)){
					$tempjson=json_decode("{}");
					$tempjson->temp=$n;
					$n=GetStringFromJson($tempjson,"temp"," ",true);
				}
				$n=$ux->SupplyParamValue($n,$tempjs,true);
				if(substr($n,-2)=="()"){
					setOutput("function",$GLOBALS["result"],$n,"",true);
				}else if(strpos(substr($n,0,10),"cmd:dom")!==false){					
					$nn=explode(",",$n);			
					$new_nn=array();
					foreach($nn as $nnn){
						if(strpos($nnn,":")!==false){
							$i=explode(":",$nnn);
							if(count($i)>=1){
								$cmddef="";
								for($ij=1;$ij<count($i);$ij++){
									$cmddef.=$i[$ij];
								}
								$new_nn[$i[0]]=$cmddef;
							}
						}
					}
					
					if(isset($new_nn["cmd"])){					
						if($new_nn["cmd"]=="dom" && isset($new_nn["fun"])){
							//if(!isset($new_nn["fun"])) print_r($new_nn);
							$new_nn["fun"]=trim($new_nn["fun"]);						
							$j=json_decode("{}");
							$j->action="dom";							
							$j->fun=$ux->SupplyParamValue($new_nn["fun"],$j,true);							
							setOutput("dom",$GLOBALS["result"],"",$j,true);							
						}
					}
				}else{
					$new[]=$n;
				}
			}
			//print_r($new);
			$new_stmt="";
			if(!empty($new))
				$new=implode("-",$new);
			else
				$new="";
				
		}
		return $new;
	}

	function RunFunctions(&$sqlJson,$sqlClass){		
		$ux=new EireneUx();
		$api=new EireneApi();
		$api->sqlClass=$sqlClass;
		$value=$GLOBALS["value"];		
		if(!isset($sqlJson->action)) return false;		
		$action=$sqlJson->action;
		if(!isset($sqlJson->outputto)) $sqlJson->outputto="html";
		if($action=="Run"){				
			$res=$api->RunStatement($sqlJson,$value,$GLOBALS["result"]);					
		}else if($action=="Install"){
			SetUp($sqlJson,$value);				
		}else if($action=="Loop"){
			$api->Loop($sqlJson);			
		}elseif($action=="Get HTML"){		
			$res=$api->GetHTMLViaTemplate($sqlJson,$value,$GLOBALS["result"]);					
		}elseif($action=="Get HTML1"){				
			$res=$api->GetTemplate1($sqlJson,$value,$GLOBALS["result"]);				
		}elseif($action=="Get Record" || $action=="Get Rows"){				
			$res=$api->GetRows($sqlJson,$value,$GLOBALS["result"]);
		}elseif($action=="Get Column"){				
			$res=$api->GetColumn($sqlJson,$value,$GLOBALS["result"]);
		}elseif($action=="Get Chart"){				
			$api->GetChart($sqlJson,$value,$GLOBALS["result"]);				
		}elseif($action=="Get Chart New"){				
			$api->GetChartNew($sqlJson);			
		}elseif($action=="Get Row" || $action=="Edit"){			
			$res=$api->GetRow($sqlJson,$value,$GLOBALS["result"]);
		}elseif($action=="Get Value"){			
			$res=$api->GetValue($sqlJson,$value,$GLOBALS["result"]);					
		}else if($action=="GetForm"){			
			if(!empty($GLOBALS["value"]["formname"]))
				$form=$api->GetForm($GLOBALS["value"]["formname"],$sqlJson);			
			else if(isset($sqlJson->formname))
				$form=$api->GetForm($sqlJson->formname,$sqlJson);			
			//setOutput("form",$GLOBALS["result"],$form,$sqlJson);
		}else if($action=="Get Form"){
			$form=$api->GetUserForm($sqlJson,$value,$GLOBALS["result"]);			
			//setOutput("form",$GLOBALS["result"],$form,$sqlJson);
		}elseif($action=="Print Form"){			
			$api->PrintForm($sqlJson);					
		}elseif($action=="Save Table"||$action=="Save Row"||$action=="Save"){
			if(isset($sqlJson)==false){
				$sqlJson='{"outputto":"php","output":"res"}';
				$sqlJson=json_decode($sqlJson);
			}
			$res=$api->SaveRow($sqlJson,$value,$GLOBALS["result"]);			
		}elseif($action=="SaveNew"){			
			$res=$api->SaveNew($sqlJson,$value,$GLOBALS["result"]);			
		}elseif($action=="Save Row From CSV"){			
			$res=$api->SaveRowsFromCSV($sqlJson);			
		}elseif($action=="Delete Row"){
			if(isset($sqlJson)==false){
				$sqlJson='{"outputto":"html","output":"res"}';
				$sqlJson=json_decode($sqlJson);
			}
			$res=$api->DeleteRow($sqlJson,$value,$GLOBALS["result"],false);
		}elseif($action=="Delete Row Permanently"){
			if(isset($sqlJson)==false){
				$sqlJson='{"outputto":"html","output":"res"}';
				$sqlJson=json_decode($sqlJson);
			}
			$res=$api->DeleteRow($sqlJson,$value,$GLOBALS["result"],true);
		}else if($action=="PHPFUN"){
			$api->RunPHPFunction($sqlJson);
		}elseif($action=="Get Table"){
			$api->GetTable($sqlJson,$value,$GLOBALS["result"]);			
		}elseif($action=="Get Matrix"){
			$api->GetMatrix($sqlJson,$value,$GLOBALS["result"]);				
		}elseif($action=="Get JSON For Dropdown"){
			$sql=$value["sql"];			
			$res=$api->GetJSONForDropdown($sql);
			setOutput("res",$GLOBALS["result"],$res,$sqlJson);					
		}elseif($action=="Install Plugin"){
			$pluginid1=$value["pluginid1"];
			$json=$value["json"];
			$installer=new EireneInstaller();
			$res= $installer->InstallPlugin($pluginid1,$json,$GLOBALS["userinfo"]["id"]);
			$sqlJson->success=true;
			//$res= InstallPlugin($pluginid1,$json,$GLOBALS["userinfo"]["id"]);
			$GLOBALS["result"]["installreport"]=$res;				
		}elseif($action=="Export Plugin"){
			$pluginid=$value["id"];			
			$installer=new EireneInstaller();
			$sqlJson->success= $installer->ExportPlugin($pluginid);
			$GLOBALS["globalphp"]["exportreport"]="Plugin Export successful. Please <a target='_blank' href='export.json'>click here to download</a> file.";			
		}elseif($action=="Upload File"){				
			$file=$_FILES["uploadfile"];				
			$uploadres=$api->UploadFile($sqlJson,$value,$GLOBALS["result"],$file);
		}elseif($action=="Download Assignment"){
			$lessonid=$value["lessonid"];			
			$res=$api->DownloadAssignment($lessonid);
			setOutput("res",$GLOBALS["result"],$res,$sqlJson);
		}elseif($action=="Remove File"){
			$api->DeleteFile($sqlJson,$value,$GLOBALS["result"]);				
		}elseif($action=="Bulk File Upload"){
			$docpermissible_size=$sqlJson->maxsize;
			$createdby=$value["userid"];				
			$docpath=trim(GetStringFromJson($sqlJson,"path"," "));
			$docpermissible_filetype=$sqlJson->filetype;
			$directory=$sqlJson->directory;
			$files=$_FILES["uploadfile"];
			$files=ArrangeFilesArrayForBuldUpload($files);
			$successcnt=0;
			$failurecnt=0;
			$db=$GLOBALS['db'];
			$msg="";
			$saveindb=isset($sqlJson->saveindb)?$sqlJson->saveindb:false;
			
			if($saveindb==true){					
				if(isset($sqlJson->tbl)==false) $sqlJson->tbl="";
				if(isset($sqlJson->fld)==false) $sqlJson->fld="";
				if(isset($sqlJson->fldtype)==false) $sqlJson->fldtype="";				
				foreach($files as $file) {
					if(isset($sqlJson->value))
						$val=$sqlJson->value;
					else
						$val="";
					$res=$api->UploadFile($sqlJson,$value,$GLOBALS["result"],$file);			
					if(strpos($GLOBALS["result"]["res"],chr(1))){
						$res=explode(chr(1),$GLOBALS["result"]["res"]);							
						$successcnt++;
						if(!isset($sqlJson->saveindb)) $sqlJson->saveindb=false;
						$val=$sqlJson->value;
						if($sqlJson->saveindb){
							$sqlJson->tbl=$ux->SupplyParamValue($sqlJson->tbl,$sqlJson,true);
							$sqlJson->fld=$ux->SupplyParamValue($sqlJson->fld,$sqlJson,true);
							$sqlJson->fldtype=$ux->SupplyParamValue($sqlJson->fldtype,$sqlJson,true);
							$val=$ux->SupplyParamValue($sqlJson->value,$sqlJson,true);
							$val=str_replace("||FILENAME||",str_replace(",","",basename($file["name"])),$val);
							$val=str_replace("||LINK||",$res[1],$val);
							$val=explode(",",$val);
							$val=implode(chr(1),$val);
							$db->SaveTable("insert",$sqlJson->tbl,$sqlJson->fld,$sqlJson->fldtype,$val);
							if($db->error)
								$GLOBALS["result"]["error"]=$db->error;
						}							
					}else
						$failurecnt++;
				}
			}
			$res= $successcnt.chr(1).$failurecnt.chr(1).$msg;
			setOutput("res",$GLOBALS["result"],$res,$sqlJson);
		}else if($action=="Get Carousel"){
			$form=$api->GetCarousel($sqlJson,$value,$GLOBALS["result"]);		
		}else if($action=="Initialize"){				
			$res=$api->GetRow($sqlJson,$value,$GLOBALS["result"]);	
			$sqlJson->output="form";			
			$form=$api->GetUserForm($sqlJson,$value,$GLOBALS["result"]);
			
			//setOutput("form",$GLOBALS["result"],$form,$sqlJson);
		}else if($action=="IF"){			
			$res=$api->CheckIfCondition($sqlJson,$value,$GLOBALS["result"]);			
			//if($res==false) print_r($sqlJson);
		}else if($action=="Switch"){
			$res=$api->CheckSwitchCondition($sqlJson,$value,$GLOBALS["result"]);			
			//if($res==false) print_r($sqlJson);
		}else if($action=="CASE"){
			$res=$api->GetCaseVariableValue($sqlJson,$value,$GLOBALS["result"]);
			$sqlJson->success=true;
			$sqlJson->then=$res;
		}else if($action=="Return"){			
			try{
				if(!isset($sqlJson->value)) $sqlJson->value="";
				if(!isset($sqlJson->outputtype))
					$value1=GetStringFromJson($sqlJson,"value"," ");
				else if(strtolower($sqlJson->outputtype)=="string")
					$value1=GetStringFromJson($sqlJson,"value"," ");
				else
					$value1=$sqlJson->value;			
				setOutput("",$GLOBALS["result"],$value1,$sqlJson,true);				
				$sqlJson->success=true;				
			}catch(Exception $ex){
				$sqlJson->success=false;
			}
		}else if($action=="Send Email"){
			$res=$api->SendEmail($sqlJson,$value,$GLOBALS["result"]);
		}else if($action=="Get Dropdown"){
			if(!isset($sqlJson->default)) $sqlJson->default="";
			$res="";
			$commaseperatedvalue=false;
			if(isset($sqlJson->commaseperatedvalue)) $commaseperatedvalue=true;
			if(isset($sqlJson->sql))$res=FillDropdownHTML($sqlJson->sql,$sqlJson->default,$commaseperatedvalue);
			if(isset($sqlJson->data))$res=FillDropdownHTML($sqlJson->data,$sqlJson->default,$commaseperatedvalue);
			setOutput("",$GLOBALS["result"],$res,$sqlJson);
		}
	}
}


?>