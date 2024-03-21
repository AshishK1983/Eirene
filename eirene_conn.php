<?PHP
try {
	$conn=new PDO("mysql:dbname=eirene;host=localhost","root","");
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	//required classes - core classes	
	require_once 'eirenedb.php';
	$db=new Db();
	$db->conn=$conn;
    
} catch(PDOException $e){ 
	echo "Error in creating connection: ". $e->getMessage()."<br/>";
}
?>