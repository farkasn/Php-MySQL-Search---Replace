<?php
/*
 * Search Whole MySQL Database and replace or do something.
 * @author el_ade
 */

// Pick up the form data and assign it to variables

// Search and Replace variables
 $search = $_POST['searchFor'];
 $replace = $_POST['replaceWith'];

// SAMPLE SEARCH FOR Latin Characters to Latin1 charset
//$search = 'á'; $replace = chr(225);
//$search = 'é'; $replace = chr(233);
//$search = 'í'; $replace = chr(237);
//$search = 'ó'; $replace = chr(243);
//$search = 'ú'; $replace = chr(250);
//$search = 'ü'; $replace = chr(252);
//$search = 'ñ'; $replace = chr(241);
//$search = 'Ñ'; $replace = chr(209);
//$search = '¿'; $replace = chr(191);
//$search = '¡'; $replace = chr(161);

// Query Type: 'search' or 'replace'
//$queryType = $_POST['queryType'];
$queryType = 'replace';

// Database Connection Details
$dbhost = 'localhost'; //addres of your host
$database = 'database'; //Database Name
$dbuser = 'user'; //Database User
$dbpassword = 'password'; //Database User Password


try{
	$db = new PDO('mysql:host='.$dbhost.';dbname='.$database, $dbuser, $dbpassword);
} catch (PDOException $e){
	echo $e->getMessage();	
}

//Prepare the output parameters
$rowHeading = ($queryType=='replace') ?
'Replacing \''.$search.'\' with \''.$replace.'\' in \''.$database."'\n\nSTATUS    |    ROWS AFFECTED    |    TABLE/FIELD  \n"
: 'Searching for \''.$search.'\' in \''.$database."'\n\nSTATUS    |    ROWS CONTAINING    |    TABLE/FIELD  \n";

$output = $rowHeading;
$summary = '';


// Get list of tables
$table_sql = 'SHOW TABLES';
$table_q = $db->prepare($table_sql);
$table_q->execute();
$tables_r = $table_q->fetchAll(PDO::FETCH_ASSOC);


foreach ($tables_r as $row){
	$table_name = $row['Tables_in_'.strtolower($database)];
	$field_sql = 'SHOW FIELDS FROM '.$table_name;
	$field_q = $db->prepare($field_sql);
	$field_q->execute();
	$field_r = $field_q->fetchAll(PDO::FETCH_ASSOC);
	foreach ($field_r as $row2){
		$field = $row2['Field'];
		$type = $row2['Type'];
		$key = $row2['Key'];
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
			// create unique handle for update_sql array
			$handle = $table_name.'_'.$field;
			if($queryType=='replace') {
				$sql[$handle]['sql'] = 'UPDATE '.$table_name.' SET '.$field.' = REPLACE('.$field.',\''.$search.'\',\''.$replace.'\')';
			} else {
				$sql[$handle]['sql'] = 'SELECT * FROM '.$table_name.' WHERE '.$field.' REGEXP(\''.$search.'\')';
			}
			
			// execute SQL
			$query = $db->prepare($sql[$handle]['sql']);
			$query->execute();
			$row_count = $query->rowCount();
			
			// store the output (just in case)
			$sql[$handle]['result'] = $query;
			$sql[$handle]['affected'] = $row_count;
			
			// Write out Results into $output
			$output .= ($query) ? 'OK        ' : '--        ';
			$output .= ($row_count>0) ? '<strong>'.$row_count.'</strong>            ' : '<span style="color:#CCC">'.$row_count.'</span>            ';
			$fieldName = '`'.$table_name.'`.`'.$field.'`';
			$output .= $fieldName;
			
			
			$output .= "\n";
											
			
		}	
		
	}
	

}

// write the output out to the page
echo '<pre>';
echo $output."\n";
echo '<pre>';
?>
   
 
