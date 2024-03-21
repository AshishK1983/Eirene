<?PHP
	class Db{
		public $TableName = "";
		private $Fields = "";
		private $Where = "";
		private $GroupBy = "";
		private $OrderBy = "";
		private $HTMLTableHeaders = "";
		private $HTMLTableHiddenFieldNo = "";
		private $VerticalSumColumns = "";
		private $FormatDateColumns = "";
		private $IndianCurrencyFields = "";
		private $HTMLTableFormatBooleanFields = false;
		public $Sql="";
		public $conn;
		public $error="";
		public $rowsAffected;	
		public $lastInsertId="";
		
		Public Function GetConn(){
			try{
				$conn=new PDO("mysql:dbname=eirene;host=localhost","root","");
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$this->conn=$conn;
			} catch(PDOException $e){ 
				echo "Error in creating connection: ". $e->getMessage()."<br/>";
			}
		}
				
		/* New function starts here */
		public function TableExists($TableName=""){
			if($TableName == "") return false;
			if( $TableName != "" ) $this->TableName = $TableName;
			$sql = "SELECT id from " .$this->TableName." limit 1";
			$this->Sql = $sql;
			$res=$this->fetchSubProcess($this->Sql,false,false);
			if(is_string($res)==true)
				return false;
			else
				return true;			
		}
	
		public function TableName($value){
			$this->TableName = $value;
			$this->Fields = "";
			$this->Where = "";
			$this->GroupBy = "";
			$this->OrderBy = "";
			$this->HTMLTableHeaders = "";
			$this->HTMLTableHiddenFieldNo = "";
			$this->VerticalSumColumns = "";
			$this->FormatDateColumns = "";
			$this->IndianCurrencyFields = "";
			$this->HTMLTableFormatBooleanFields = False;
			$this->error="";
		}		
		public function BuildQuery(){
			$this->error="";
			$whr="";$grp="";$srt="";
			if ($this->Where != "") $whr = " WHERE " . $this->Where;
			if ($this->GroupBy != "") $grp = " GROUP BY " . $this->GroupBy;
			if ($this->OrderBy != "") $srt = " ORDER BY " . $this->OrderBy;

			$sql = "SELECT " . $this->Fields . " FROM " . $this->TableName . $whr . $grp . $srt;
			$sql = $this->TreatSQLStatement($sql);
			$this->Sql=$sql;
			return $sql;
		}
		public function getSQL(){
			Return $this->Sql;
		}
		public function fetchSubProcess($query,$fetchAll=true,$key=false){
				try{
					$stmt=$this->conn->prepare($query);
					//echo $query;
					//echo "<textarea>".$query."</textarea>";
					$stmt->execute();
					if($fetchAll!=true){
						if($key==false)
							$res=$stmt->fetch(PDO::FETCH_NUM);
						else
							$res=$stmt->fetch(PDO::FETCH_ASSOC);
						
					}else{
						if($key==false)
							$res=$stmt->fetchAll(PDO::FETCH_NUM);
						else
							$res=$stmt->fetchAll(PDO::FETCH_ASSOC);
						
					}

				} catch(PDOException $ex) {
					//$conn=null;
					$res=false;
					//echo $ex->getMessage().'<br>'.$query;
					$this->error=$ex->getMessage().$this->Sql;
				}
				return $res;
			}
		public function TreatSQLStatement($sql){
			if(isset($sql)==false)
				return false;
			/*format the $sql command to accept current data*/
			$sql = str_replace("||TODAY||", date("Ymd"),$sql);					
			$sql = str_replace("||TOMORROW||", date('Ymd', strtotime(date("Ymd"). ' + 1 days')),$sql);
			$sql = str_replace("||DAY_AFTER_TOMORROW||", strtotime(date("Ymd"). ' + 2 days'),$sql);
			$sql = str_replace("||YESTERDAY||", strtotime(date("Ymd"). ' - 1 days'),$sql);
			$sql = str_replace("||DAY_BEFORE_YESTERDAY||", strtotime(date("Ymd"). ' - 2 days'),$sql);
			$sql = str_replace("||TODAY + 90||", strtotime(date("Ymd"). ' + 90 days'),$sql);
			$sql = str_replace("||TODAY - 90||", strtotime(date("Ymd"). ' - 90 days'),$sql);
			
			$sql = str_replace("||TIMENOW||", date("H:i:s"),$sql);
			$sql = str_replace("||TIMENOW + 30||", date("H:i:s", strtotime("+30 minutes")),$sql);
			$sql = str_replace("||TIMENOW - 30||", date("H:i:s", strtotime("-30 minutes")),$sql);
			
			/*format the $sql command to accept current year,month,dd*/
			$sql = str_replace("||YEAR||", date("Y"),$sql);
			$sql = str_replace("||YEAR + 1||", intval(date("Y"))+1,$sql);
			$sql = str_replace("||NEXTYEAR||", intval(date("Y"))+1,$sql);
			$sql = str_replace("||YEAR - 1||", intval(date("Y"))-1,$sql);
			$sql = str_replace("||PREVIOUSYEAR||", intval(date("Y"))-1,$sql);
			$sql = str_replace("||MONTH||", date("m"),$sql);
			$sql = str_replace("||FULL_MONTH||", date("F"),$sql);
			$sql = str_replace("||SHORT_MONTH||", date("M"),$sql);
			$sql = str_replace("||DATE||", date("d"),$sql);			
			return $sql;
		}
		public function FetchRecord($sql,$key=true){	
			if(isset($sql)==false)
				$sql="";
			$sql=trim($sql);
			
			if ($sql=="")
				$sql=$this->BuildQuery();
			$this->Sql=$sql;
			$this->Sql=$this->TreatSQLStatement($this->Sql);
			
			$res=$this->fetchSubProcess($this->Sql,false,$key);
			return $res;
		}
		public function GetList1($sql,$key=false){	
			$this->error="";
			if(isset($sql)==false)
				$sql="";
			$sql=trim($sql);
			
			if ($sql=="")
				$sql=$this->BuildQuery();
			$this->Sql=$sql;
			$this->Sql=$this->TreatSQLStatement($this->Sql);
			$res=$this->fetchSubProcess($this->Sql,true,$key);
			return $res;
		}
		
		public function GetValue($sql){
			$res=$this->FetchRecord($sql,false);
			if(isset($res[0]))
				return $res[0];
			else
				return "";
		}
		public function HasRows($sql= ""){
			$res=$this->FetchRecord($sql,false);
			if(isset($res[0]))
				return true;
			else
				return false;
		}
		public function GetTable(&$json=""){			
			/*initialize*/
			/*The json that will be supplied will be an object containing at least sql. Further attributes that are supported in json are vsum,hsum,class,style,id,name etc.*/
			if(empty($json)){
				$json="{}";
				$json=json_decode($json);
			}
			$this->error="";			
			/*$hsum - Horizontal sum & $vsum-Vertical sum*/			
			$hsum="";$vsum="";$hiddencols="";$br2nl=array();$convertSelect=array();
			
			/*fetch records from database*/
			$json->rowscount=0;
			if(isset($json->sql)){
				$list=$this->GetList1($json->sql,true);
				//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$json->sql).'</textarea><br>';
				//print_r($GLOBALS["value"]);
				//print_r($json);
				//print_r($sql);
				$json->rowscount=count($list);				
			}else if(isset($json->data)){
				if(is_array($json->data)){
					$list=$json->data;
					$json->rowscount=count($json->data);
				}else
					return "";
			}else
				return "";
			if($json->rowscount<=0){
				$str="<div><hr/>No Records Found!<hr/></div>";
				if(isset($json->OnNoRecords)){
					GetStringFromJson($json,"OnNoRecords");
				}
				return $str;
			}
			

			/*screen width*/
			$screenwidth=1000;
			if(!isset($json->responsive)) $json->responsive=true;
			
			if($json->responsive==true)			
				if(isset($_POST["screenwidth"])) $screenwidth=$_POST["screenwidth"];
			
				
			$str = "<table ";			
			/*table attributes*/
			if(isset($json->class)) $str.=" class='".$json->class."'";
			if(isset($json->style)) $str.=" style='".$json->style."'";
			if(isset($json->name)) $str.=" name='".$json->name."'";
			if(isset($json->hsum)) $hsum=explode(",",$json->hsum);
			if(isset($json->vsum)) $vsum=explode(",",$json->vsum);
			if(isset($json->hiddencols)) $hiddencols=explode(",",$json->hiddencols);
			if(isset($json->br2nl)) $br2nl=explode(",",$json->br2nl);
			if(isset($json->convertSelect)) $convertSelect=explode(",",$json->convertSelect);
			if(isset($json->id)){
				$str.=" id='".$json->id."'";
				$json->tableid=id;
			}else if(isset($json->tableid)){
				$str.=" id='".$json->tableid."'";
			}else{
				$tempid="Table".rand(1,9999);
				$str.=" id='".$tempid."'";
				$json->tableid=$tempid;
			}
			
			$str.=">";
			
			/*table width*/
			$width=array();
			if(isset($json->width))
				$width=explode(",",$json->width);
			
			
			/*table header*/
			$str.="<thead><tr>";
			
			$len=0;
			$header=array();
			$header1=array();
			$rows=array();
			
			if(isset($json->header)){
				if(is_string($json->header))
					$header = explode("," , $json->header);
				else if(is_array($header))
					$header=$json->header;
			}
			if(!empty($list[0]))			
				$len=count($list[0]);
			else
				$len=count($header);
			
			if($screenwidth>=770){
				if(isset($json->serialno)){					
					$str.="<th>S.No.</th>";					
				}
				
				for($i=0;$i<$len;$i++){
					if(!empty($hiddencols)){
						if(in_array($i+1,$hiddencols))
							continue;
					}
					$str .= "<th ";
					if($i==0 && isset($json->stickycolumn)){
						$str.="style='position:sticky; left:0px;z-index:9;";
						if(isset($width[$i])) $str.=$width[$i];
						$str.=";'";
					}else{
						if(isset($width[$i])) $str.=" style='".$width[$i]."'";
					}
					$str.=">";
					if(isset($header[$i])) $str.=$header[$i];				
					$str .= "</th>";
				}
				if(!empty($hsum)) $str.="<th>Total</th>";
			}
			else{				
				for($i=0;$i<$len;$i++){
					if(!empty($hiddencols)){
						if(in_array($i+1,$hiddencols))
							continue;
					}
					if(!isset($header[$i])) $header[$i]="";
					$header1[]= $header[$i];					
				}
				if(!empty($hsum)) $header1[]= "Total";				
				$str.="<th></th>";
			}
			$str .= "</tr></thead>";
			
			/*$vsum initialize*/
			if(!empty($vsum)){
				$vsum1=array();
				foreach($vsum as $v){
					$vsum1[$v]=0;
				}				
			}
			//echo "<textarea>".$sql."</textarea>";
			//print_r($list);
			
			if(isset($list)){
				$str.="<tbody>";
				$row=1;
				foreach($list as $r){
					$row1=array();
					/*table row creation*/
					$col=0;$hsum1=0;
					if($screenwidth>=770){
						$str.="<tr";
						if(isset($json->trclass)) $str.=" class='".$json->trclass."'"; 
						if(isset($json->trstyle)) $str.=" style='".$json->trstyle."'";
						$str.=">";
						if(isset($json->serialno)){
							$str.="<td";
							if(isset($json->tdclass)) $str.=" class='".$json->tdclass."'";
							if(isset($json->tdstyle)) $str.=" style='".$json->tdstyle."'";
							$str.=">".$row."</td>";
						}
					}else
						if(isset($json->serialno)) $row1[]="<div class='row'><div class='cell'>S.No.</div><div class='cell'>$row</div></div>";
					$row++;
					
					foreach($r as $k=>$c){
						$col++;
						/*hidden cols*/
						if(!empty($hiddencols)){
							if(in_array($col,$hiddencols))
								continue;
						}
						
						/*br2nl cols*/											
						if(in_array($col,$br2nl)){							
							$c=str_ireplace("<br>","\n",$c);
						}else{							
							$c=str_replace("\n","<br>",$c);
						}
						
						
						/*ConvertSelect cols*/											
						if(in_array($col,$convertSelect)){							
							if(stripos($c,"<select")!==false){
								//libxml_use_internal_errors(true);
							   $dom = new domDocument;	
							   
								$dom->loadHTML("<div>".$c."</div>");	
								//$errors = libxml_get_errors();
								
							   
							   $c1=str_replace("</select>","",$c);
								$select = $dom->getElementsByTagName('select')[0];
								$values=$select->getAttribute("values");
								$value=$select->getAttribute("value");
								$c1.=FillDropdownHTML($values,$value)."</select>";
								$c=$c1;
							}
						}						
						
						/*computation for horizontal sum*/
						if(!empty($hsum)){
							if(in_array($col,$hsum)){
								$crep=preg_replace('/[^0123456789-]+/', '', $c);
								$hsum1+=intVal($crep);								
							}
						}
						
						/*computation for vertical sum*/
						if(!empty($vsum)){
							if(in_array($col,$vsum)){
								$crep=preg_replace('/[^0123456789-]+/', '', $c);
								$vsum1[$col]+=intVal($crep);								
							}
						}
						if($screenwidth>=770){
							$str.="<td";
							if(isset($json->tdclass)) $str.=" class='".$json->tdclass."'";
							if(isset($json->tdstyle) && isset($json->stickycolumn)) 
								$str.=" style='".$json->tdstyle.";position:sticky; left:0px;z-index:9;'";
							else if(!isset($json->tdstyle) && isset($json->stickycolumn)) 
								$str.=" style='position:sticky; left:0px;z-index:9;'";
							else if(isset($json->tdstyle) && !isset($json->stickycolumn)) 
								$str.=" style='".$json->tdstyle."'";
							$str.=">".$c."</td>";
						}else{
							if(!isset($header1[$col-1])) $header1[$col-1]="";
							$row1[]="<div class='row'><div class='cell'>".$header1[$col-1]."</div><div class='cell'>".$c."</div></div>";
						}
					}					
					
					/*horizontal sum cell*/
					if(!empty($hsum)){
						$col++;
						if($screenwidth>=770){
							$str.="<td";
							if(isset($json->tdclass)) $str.=" class='".$json->tdclass."'";
							if(isset($json->tdstyle)) $str.=" style='".$json->tdstyle."'";
							$str.="><b>".$hsum1."</b></td>";
						}else{
							if(!isset($header1[$col-1])) $header1[$col-1]="";
							$row1[]="<div class='row'><div class='cell'>".$header1[$col-1]."</div><div class='cell'>".$hsum1."</div></div>";
						}
						/*computation for vertical sum*/
						if(!empty($vsum)){
							if(in_array($col,$vsum)){
								$vsum1[$col]+=intVal($hsum1);								
							}
						}
					}
					if($screenwidth<770){
						$str.="<td><div class='grid'>".implode("",$row1)."</div></td>";
					}
					$str.="</tr>";
				}
				/*vertical sum row*/
				if(!empty($vsum)){
					$str.="<tr>";
					$row1=array();
					if($screenwidth>=770)
						if(isset($json->serialno)) $str.="<td></td>";
					else
						if(isset($json->serialno)) $row1[]="<div class='row'><div class='cell'></div><div class='cell'></div></div>";
					$i=0;
					for($i=0;$i<$len;$i++){
						if(!empty($hiddencols)){
							if(in_array($i+1,$hiddencols))
								continue;
						}
						if(in_array($i+1,$vsum)){
							if($screenwidth>=770)
								$str.="<td><b>".$vsum1[$i+1]."</b></td>";
							else{
								if(!isset($header1[$i])) $header1[$i]="";
								$row1[]="<div class='row'><div class='cell'>".$header1[$i]."</div><div class='cell'><b>".$vsum1[$i+1]."</b></div></div>";
							}
						}else{
							if($screenwidth>=770)
								$str.="<td></td>";
							else
								$row1[]="<div class='row'><div class='cell'></div><div class='cell'></div></div>";
						}
					}
					if(!empty($hsum)){
						if(in_array($i+1,$vsum)){
							if($screenwidth>=770)
								$str.="<td><b>".$vsum1[$i+1]."</b></td>";
							else{
								if(!isset($header1[$i])) $header1[$i]="";
								$row1[]="<div class='row'><div class='cell'>".$header1[$i]."</div><div class='cell'><b>".$vsum1[$i+1]."</b></div></div>";
							}
						}else{
							if($screenwidth>=770)
								$str.="<td></td>";
							else
								$row1[]="<div class='row'><div class='cell'></div><div class='cell'></div></div>";
						}
					}
					if($screenwidth<770){
						$str.="<td><div class='grid'>".implode("",$row1)."</div></td>";
					}
					$str.="</tr>";
				}
				$str.="</tbody>";
			}
			$str.="</table>";        
			return $str;
		}
		public function Parse($sql){
			if (isset($sql) ==false)
				return false;
			if(trim($sql)=="")
				return false;
			$sql=$this->TreatSQLStatement($sql);
			$sql = str_replace($sql,"  ", " ");
			$SelPos = strpos(strtolower($sql),"select ");
			$FromPos= strpos(strtolower($sql),"from ");
			$WherePos = strpos(strtolower($sql),"where ");
			$OrderPos = strpos(strtolower($sql),"order by");
			$GroupPos = strpos(strtolower($sql),"group by");

			/*constructing tablename*/
			if($WherePos !==false)
				$this->TableName = substr($sql,$FromPos + 5, $WherePos - ($FromPos + 6));
			
			/*constructing where*/
			if($WherePos !==false){
				if($GroupPos !==false){
					$this->Where = substr($sql,$WherePos + 6, $GroupPos - ($WherePos + 7));
				}elseif($OrderPos !==false){
					$this->Where = substr($sql,$WherePos + 6, $OrderPos - ($WherePos + 7));
				}else{
					$this->Where = substr($sql,$WherePos + 6);
				}
			}
			/*constructing group by*/
			if($GroupPos !==false){
				if($OrderPos !==false){
					$this->GroupBy = substr($sql,$GroupPos + 9, $OrderPos - ($GroupPos + 10));
				}else{
					$this->GroupBy = substr($sql,$GroupPos + 9);
				}
			}
			/*constructing order by*/
			if($OrderPos !==false){
				$this->OrderBy = substr($sql,OrderPos + 9);
			}
			$this->Fields = substr($sql,$SelPos + 7, $FromPos - ($SelPos + 8));
		}
		public function Execute($sql,&$list="",$key=false){			
			$sql=trim($sql);
			$this->Sql=$sql;
			//echo "<textarea>$sql</textarea>";
			//$this->Sql = str_ireplace("&COMMA&", ",",$this->Sql);	
			$this->error="";
			
			if(isset($sql)==false)
				$sql = "";
			if(substr(strtoupper(trim($sql)),0, strpos(strtoupper(trim($sql)),"SHOW"))=="SELECT" || substr(strtoupper(trim($sql)),0, strpos(strtoupper(trim($sql)),"SHOW")) == "SHOW"){				
				$sql = $this->TreatSQLStatement($this->Sql);
				$list = $this->GetList1($this->Sql,$key);
				
				//print_r($list);
				if(count($list) > 0)
					return true;
				else
					return false;
				
			}else{
				try{					
					$stmt=$this->conn->prepare($this->Sql);
					$stmt->execute();
					$rowsAffected=$stmt->rowCount();
					$this->rowsAffected=$rowsAffected;
					if($rowsAffected>0)
						return true;
					else
						return false;
				}catch(PDOException $ex) {
					//$conn=null;
					$this->error= "An Error occured! ".$ex->getMessage().'<br>';
					return false;
				}	
			}		
		}
		public function SaveTable($Command,$TableName,$Fields,$FieldTypes,$Values,$Whr = ""){	
			if(isset($value)){
				$value = str_replace("\n","<br/>",$value);
			}
			$Values=$this->TreatSQLStatement($Values);
			if(is_string($Fields))
				$flds = explode(",",$Fields);
			else if(is_array($Fields))
				$flds=$Fields;
			
			if(is_string($Values)) 
				$vls = explode(chr(1),$Values);
			else if(is_array($Values))
				$vls=$Values;
			
			if(is_string($FieldTypes))
				$fldstyp = explode(",",$FieldTypes);
			else if(is_array($FieldTypes))
				$fldstyp=$FieldTypes;
			
			if((count($flds)!= count($vls))  || count($flds) != count($fldstyp)){				
				$this->error="DB: $TableName Field(".count($flds)." ".$Fields."), Field Types(".count($fldstyp)." ".$FieldTypes.") or Values(".count($vls)." ".$Values.") are not matching";
				return false;
			}
			$cnt= count($flds);
			$id= "";
			$dict=array();
			$res="";
			//for($i=0;$i<$cnt;$i++){
			foreach($flds as $i=>$v){
				if($flds[$i] == "id")
					$id = $vls[$i];
				if(substr($vls[$i],-2)=="()"){
					$dict[$flds[$i]]=$vls[$i];
				}else if($fldstyp[$i]== "s")
					if($vls[$i]!="null")
						$dict[$flds[$i]]="'".str_replace("'","''",$vls[$i])."'";
					else
						$dict[$flds[$i]]="null";
				elseif($fldstyp[$i]== "d"){
					if(!empty($vls[$i])){						
						$dict[$flds[$i]]="'" . str_replace("-","",$vls[$i]) . "'";						
					}else
						$dict[$flds[$i]]="null";
				}elseif($fldstyp[$i]== "dt"){
					if(!empty($vls[$i])){
						$dict[$flds[$i]]="'".$vls[$i]."'";						
					}else
						$dict[$flds[$i]]="null";
				}elseif($fldstyp[$i]== "g"){
					/*g stands for googlelink*/
					if(!empty($vls[$i])){
						$newv=str_replace("https://drive.google.com/open?id=","",$vls[$i]);						
						$newv=str_replace("https://docs.google.com/spreadsheets/d/","",$newv);
						$newv=str_replace("https://docs.google.com/document/d/","",$newv);
						$newv=str_replace("https://docs.google.com/forms/d/","",$newv);
						$newv=str_replace("/edit#gid=0","",$newv);
						$newv=str_replace("/edit","",$newv);
						$newv=explode("&",$newv)[0];
						$dict[$flds[$i]]="'".$newv."'";
					}else
						$dict[$flds[$i]]="null";
				}elseif($fldstyp[$i] == "u"){
					if(strlen($vls[$i]) < 36)
						$dict[$flds[$i]]="null";
					else
						$dict[$flds[$i]]="'".$vls[$i]."'";
				}else if($fldstyp[$i]=="n"){
					if(!isset($vls[$i]))$vls[$i]="";
					if(!empty($vls[$i]))
						$dict[$flds[$i]]=$vls[$i];  
					else if($vls[$i]=="0")
						$dict[$flds[$i]]=0;
					else
						$dict[$flds[$i]]="null";
				}else
					$dict[$flds[$i]]=$vls[$i];            
			}
			$this->TableName=$TableName;
			
			//print_r($dict);echo "<br>";
			if(strtolower($Command)== "insert"){				
				if(isset($dict["id"])){
					unset($dict["id"]);
				}
				$res = $this->Save("", $dict);
			}elseif(strtolower($Command) == "update"){
					if(isset($dict["id"])) $this->lastInsertId=$dict["id"];
					if(isset($_POST["ID"])) $this->lastInsertId=$_POST["ID"];
					unset($dict["id"]);					
					if($Whr != ""){
						$this->Where = $Whr;
						$res = $this->Update($dict);
					}else{
						$res = $this->Save($id, $dict);
					}
			}elseif(strtolower($Command)== "insertorupdate"){
				if(empty($Whr)){
					$sql= "SELECT id FROM " . $TableName . " WHERE id='" . $id . "'";					
					if($this->HasRows($sql) && $id != ""){
						$res = $this->Save($id, $dict);
					}else{
						if(isset($dict["id"])){
							unset($dict["id"]);
						}
						$res = $this->Save("", $dict);
					}
				}else{
					$sql= "SELECT id FROM " . $TableName . " WHERE ".$Whr;	
					
					if($this->HasRows($sql)){
						$this->Where = $Whr;
						$res = $this->Update($dict);
					}else{
						if(isset($dict["id"])){
							unset($dict["id"]);
						}
						$res = $this->Save("", $dict);
					}
				}
			}			
			return $res;
		}
		
		public function Save($recordid, $Values,$whr="",$tablename=""){			
			//$Values = $this->PreTreatValueBeforeSaving($Values);			
			$uuid="";
			$insert=false;
			/*check if rowexists*/
			if(empty($recordid) && empty($whr)){
				$insert=true;
			}else{
				$tempsql="SELECT count(*) FROM ";
				if(empty($tablename))
					$tempsql.=$this->TableName;
				else
					$tempsql.=$tablename;
				if(!empty($recordid)) 
					$tempsql.=" WHERE id='$recordid'";
				else if(!empty($whr))
					$tempsql.=" WHERE $whr";
				else
					$insert=true;
				
				if(!$insert){
					if($this->GetValue($tempsql)<=0) $insert=true;					
				}
			}			
			if($insert){
				/*insert*/
				$sql="SET @V1=UUID()";				
				$this->Execute($sql);
				$uuid=$this->GetValue("SELECT @V1");
				$sql = "INSERT INTO ";
				if(empty($tablename))
					$sql.=$this->TableName;
				else
					$sql.=$tablename;
				$sql.=" (id," . implode(",",array_keys($Values)) . ") VALUES (@V1," . implode(",",array_values($Values)) . ")";
				$this->Sql = $sql;
			}else{
				/*update*/
				$this->Sql= "UPDATE ";
				if(empty($tablename))
					$this->Sql.=$this->TableName;
				else
					$this->Sql.=$tablename;
				$this->Sql.=" SET ";
				$aa= " ";
				foreach($Values as $key=>$val){
					$aa.= $key . "=" . $val . ",";
				}
				$aa = substr($aa,0,strlen($aa)-1);
				$this->Sql.= $aa. " WHERE ";
				if(!empty($recordid)) 
					$this->Sql.=" id='$recordid'";
				else if(!empty($whr))
					$this->Sql.=" $whr";
				else 
					return false;
				if(!empty($recordid)) $uuid=$recordid;				
			}			
			//echo '<textarea>'.str_replace(array('&apos;','<','>'),array('&apos','&lt','&gt'),$this->Sql).'</textarea><br>';
			$res=$this->Execute($this->Sql);			
			if($res)
				$this->lastInsertId=$uuid;
			else
				$this->lastInsertId="";			
			return $res;
		}		
		
		public function Update($values){
			//print_r($values);
			$whr = "";
			if($this->Where != "")
			$whr = " WHERE " .$this->Where;
			$dict=array();
			$this->Sql= "UPDATE " . $this->TableName . " SET ";
			$ubt="";
			foreach($values as $key=>$val){
				if($ubt == "")
					$ubt = $key . "=" . $val;
				else
					$ubt .= "," . $key . "=" . $val;
				   
			}
			$this->Sql.= $ubt . " " .$whr;
			$this->Sql=$this->TreatSQLStatement($this->Sql);
			
			$res=$this->Execute($this->Sql);
			//echo $this->Sql."<br>";
			//if($res)
			//	$this->lastInsertId="";
			return $res;
		}
		private function PreTreatValueBeforeSaving($dict){
			$dict1=array();
			foreach($dict as $key=>$val){
				if($val != ""){
					$str1 = "";
					$str1 = substr($val,0,1);
					if($str1 == "'"){
						/*escape single quotes while saving*/						
						$dict1[$key]= "'" . str_replace("'","''",substr($val,1,strlen($val)-2))."'";
					}else
						$dict1[$key]=$val;
					
				}else{
					$dict1[$key]="0";
				}
			}
			return $dict1;
		 }
		public function GetJSONData($sql){
			/*for single record line*/
			$reslist = $this->FetchRecord($sql,true);
			$reslist=json_encode($reslist);
			return $reslist;
		}
		public function GetJSONData1($sql= "",$key=true){
			/*for multiple record lines*/
			$list=$this->GetList1($sql,$key);
			$list=json_encode($list);			
			return $list;
		}
		public function GetJSONDataForDropdown($sql= ""){
			$list = $this->GetList1($sql,false);
			$res = "[";
			foreach($list as $l){
				$res .= chr(123);
				$res .= '"key":"' . $l[0] . '",';
				if(count($l)>=2)
					$res .= '"value1":"' . $l[1] . '"';
				else
					$res .= '"value1":"' . $l[0] . '"';
				
				$res.= "},";
			}
			if ($res != "[")
				$res = substr($res,0, strlen($res)-1);
			$res .= "]";
			return $res;
		}		
	}	
?>