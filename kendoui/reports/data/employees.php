<?php
$link = mysql_pconnect("localhost", "root", "root") or die("Unable To Connect To Database Server");
mysql_select_db("northwind") or die("Unable To Connect To Northwind");

// add the header line to specify that the content type is JSON
header("Content-type: application/json");

// determine the request type
$verb = $_SERVER["REQUEST_METHOD"];

// handle a GET
if ($verb == "GET") {
	$arr = array();
	$rs = mysql_query("SELECT EmployeeID, LastName, FirstName FROM Employees");
	
	while($obj = mysql_fetch_object($rs)) {
		$arr[] = $obj;
	}
	
	echo "{\"data\":" .json_encode($arr). "}";	
}

// handle a POST
if ($verb == "POST") {
	$lastName = mysql_real_escape_string($_POST["LastName"]);
	$employeeId = mysql_real_escape_string($_POST["EmployeeID"]);
	
	$rs = mysql_query("UPDATE Employees SET LastName = '" .$lastName ."' WHERE EmployeeID = " .$employeeId);

	if ($rs) {
		echo json_encode($rs);
	}
	else {
		header("HTTP/1.1 500 Internal Server Error");
		echo "Update failed for EmployeeID: " .$employeeId;
	}
}

?>