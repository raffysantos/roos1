<?php

define( '_JEXEC', 1 );
define( '_VALID_MOS', 1 );
define('JPATH_BASE', '../../../');
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
/* Create the Application */
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
JPluginHelper::importPlugin('system');
$mainframe->triggerEvent('onAfterInitialise');
/* Make sure we are logged in at all. */
if (JFactory::getUser()->id == 0)
   die("You have to be logged in.");



$link = mysql_pconnect($mainframe->getCfg('host'), $mainframe->getCfg('user'), $mainframe->getCfg('password')) or die("Unable To Connect To Database Server");

mysql_select_db($mainframe->getCfg('db')) or die("Unable To Connect To DB");

// add the header line to specify that the content type is JSON
header("Content-type: application/json");

// determine the request type
$verb = $_SERVER["REQUEST_METHOD"];

// handle a GET
if ($verb == "GET") {

        //echo  $_GET["datefrom"] + " -  " + $_GET["dateto"];
       //die("You have to be logged in.");
       
         $datefrom= mysql_real_escape_string($_GET["datefrom"]);
         if($datefrom=="")
         { $datefrom = date("Y-m-d"); };
         
         $dateto= mysql_real_escape_string($_GET["dateto"]);
         if($dateto=="")
         { $dateto = date("Y-m-d"); };   
               


	$arr = array();
	$rs = mysql_query("SELECT ce.id, title as ClassName, LocationName as Location, StartDate, MID(TIME(`startdate`),1,5) AS StartTime, EndDate, instructorID, u.Name as InstructorName, HourlyRate,
	                      (hour(TIMEDIFF(  `enddate` ,  `startdate` ))*60)  + (Minute(TIMEDIFF(  `enddate` ,  `startdate` )))   AS Minutes, 
                              ((hour(TIMEDIFF(  `enddate` ,  `startdate` )))   + (Minute(TIMEDIFF(  `enddate` ,  `startdate` ))/60 ) )* HourlyRate  as TotalPayable, 
                              CASE ApprovedByManager WHEN 0 THEN 'false' ELSE 'true' END AS ApprovedByManager, AttendeeNumber, CASE Paid WHEN 0 THEN 'false' ELSE 'true' END AS Paid, BankTransactionID AS Participation FROM pr_community_events
                              ce Inner Join pr_users u on ce.InstructorID=u.Id inner join pr_locations loc
                              on ce.location=loc.locid
                              Where published=1 AND parent<>0 AND CatID=5 AND StartDate >='" .  $datefrom   .  "'  AND  StartDate <='" .  $dateto   .  "' Order by StartDate, LocationName, u.Name, AttendeeNumber,ApprovedByManager LIMIT 0,500");


	//WHERE ApprovedByManager=0  AND  StartDate <='" .  $dateto   .  "'
	while($obj = mysql_fetch_object($rs)) {
		$arr[] = $obj;
	}
	
	echo "{\"data\":" .json_encode($arr). "}";	
}

// handle a POST
if ($verb == "POST") {
	//$location = mysql_real_escape_string($_POST["location"]);
	//$HourlyRate = mysql_real_escape_string($_POST["HourlyRate"]);
	//$AtendeeNumber= mysql_real_escape_string($_POST["AtendeeNumber"]);
	//$ApprovedByManager= mysql_real_escape_string($_POST["ApprovedByManager"]);
	
	//$/id = mysql_real_escape_string($_POST["id"]);
	
	///$rs = mysql_query("UPDATE pr_community_events SET AtendeeNumber= " .$AtendeeNumber .", HourlyRate = " .$HourlyRate .", ApprovedByManager= " .$ApprovedByManager ." WHERE id = " .$id);

	//if ($rs) {
	//	echo json_encode($rs);
	//}
	//else {
//		header("HTTP/1.1 500 Internal Server Error");
//		echo "Update failed for EmployeeID: " .$id;
//	}
}

?>