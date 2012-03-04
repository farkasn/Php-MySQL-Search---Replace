<?php
/*
 * Search Whole MySQL Database and replace or do something.
 * @author el_ade
 */
$dbhost = 'localhost'; //addres of your host
$dbdatabase = 'database'; //Database Name
$dbuser = 'user'; //Database User
$dbpassword = 'password'; //Database User Password


try{
	$db = new PDO('mysql:host='.$dbhost.';dbname='.$dbdatabase, $dbuser, $dbpassword);
} catch (PDOException $e){
	echo $e->getMessage();	
}


// Get list of tables
$table_sql = 'SHOW TABLES';
$table_q = $db->prepare($table_sql);
$table_q->execute();
$tables_r = $table_q->fetchAll(PDO::FETCH_ASSOC);

foreach ($tables_r as $row){
	$table_name = $row['Tables_in_'.strtolower($dbdatabase)];
	echo "<br><b>$table_name</b>";
	$field_sql = 'SHOW FIELDS FROM '.$table_name;
	$field_q = $db->prepare($field_sql);
	$field_q->execute();
	$field_r = $field_q->fetchAll(PDO::FETCH_ASSOC);
	foreach ($field_r as $row2){
		$field = $row2['Field'];
		$type = $row2['Type'];
		$key = $row2['Key'];
		echo "<li>$field $type";
		switch(true) {
			// set which column types can be replaced/searched
			case stristr ( strtolower ( $type ), 'char' ) :
				$typeOK = true;
				break;
			case stristr ( strtolower ( $type ), 'text' ) :
				$typeOK = true;
				break;
			case stristr ( strtolower ( $type ), 'blob' ) :
				$typeOK = true;
				break;
			case stristr ( strtolower ( $key ), 'pri' ) : // do not replace in index keys
				$typeOK = false;
				break; 
				                                                                     
			default :
				$typeOK = false;
				break;
		}
		if ($typeOK){
			// In this case I want to remove all corrupted text in my database, but you can change this variable
			$update_sql = 
		   "update $table_name set $field = replace($field, 'Ã±', 'ñ');
			update $table_name set $field = replace($field, 'Ã¡', 'á');
			update $table_name set $field = replace($field, 'Ã³', 'ó');
			update $table_name set $field = replace($field, 'Ã', 'í');
			update $table_name set $field = replace($field, 'íº', 'ú');
			update $table_name set $field = replace($field, 'í‘', 'Ñ');
			update $table_name set $field = replace($field, 'Ãº', 'ú');
			update $table_name set $field = replace($field, 'í©', 'é');
			update $table_name set $field = replace($field, 'â€“', '–');";
			
			
			$update_q = $db->prepare($update_sql);
			$update_q->execute();
			$afected_rows = $update_q->rowCount();
			if ($afected_rows > 0){
				echo " => $afected_rows afected rows.";
			}			
		}
		echo "</li>";
		
	}
	

}


?>
   
 
