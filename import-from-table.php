<?php
/*
 * Import Data from one table to another in the same Database
 * @author el_ade
 */


// Database Connection Details
$dbhost = 'localhost'; //addres of your host
$database = 'database'; //Database Name
$dbuser = 'root'; //Database User
$dbpassword = ''; //Database User Password

$sourcetable = "source_table";
$sourcecomparefield = "field1";
$sourcereplacefield = "field2";

$destintable = "destination_table";
$destincomparefield = "field3";
$destinreplacefield = "field4";


try{
	$db = new PDO('mysql:host='.$dbhost.';dbname='.$database, $dbuser, $dbpassword);
} catch (PDOException $e){
	echo $e->getMessage();	
}

$result = $db->prepare("SELECT $sourcecomparefield, $sourcereplacefield FROM $sourcetable");
$result->execute();
$i=0;
foreach ($result->fetchAll() as $row){
	$sql = "SELECT $destinreplacefield FROM $destintable WHERE $destincomparefield = '$row[$sourcecomparefield]'";
	$result2 = $db->prepare($sql);
	$result2->execute();
	if ($result2->rowCount() > 0){
		$db->query("UPDATE ".$destintable." SET $destinreplacefield='$row[$sourcereplacefield]' WHERE $destincomparefield = '$row[$sourcecomparefield]");
		echo "<br>$row[$sourcecomparefield] => $row[$sourcereplacefield]";
		$i++;
	}
	
}
echo "<br>$i Replacements";
?>
   
 
