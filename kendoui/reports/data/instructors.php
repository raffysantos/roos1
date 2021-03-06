<?php
/* Including neccessary libraries */
include_once("../../../boot.php");
include_once("classes/instructors.class.php");
include_once("classes/skills.class.php");
include_once("classes/locations.class.php");

/* Inititalising objects */
$skills = new Skills($db);
$locations = new Locations($db);

/* Create the Application */
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();
JPluginHelper::importPlugin('system');
$mainframe->triggerEvent('onAfterInitialise');

// add the header line to specify that the content type is JSON
header("Content-type: application/json");

// determine the request type
$verb = $_SERVER["REQUEST_METHOD"];

if (count($user->locations['company'] == 1)) { // If a normal user (i.e. only 1 company assigned)
    $instructors = new Instructors($db, $user);

    // handle a GET
    if ($verb == "GET") {
        $instructorsArr = $instructors->getListOfInstructors("name", "ASC");

        $i=0;
        $results = array();
        
        if (!empty($instructorsArr)) {
            foreach ($instructorsArr as $instructor) {
                $results[$i] = $instructor;


                /* Formatting SKILLSET string */
                if (!empty($instructor['skills']) && $instructor['skills'] != "null") {
                    $skillset = explode(",", $instructor['skills']);
                    $skillsStr = "";
                    foreach ($skillset as $skill) {
                        $skillsStr .= $skills->getSkillNameByID($skill).",";
                    }
                    $skillsStr = rtrim($skillsStr, ",");
                }
                else
                    $skillsStr = "No skills set";

                /* Formatting LOCATIONS string */
                if (!empty($instructor['locations']) && $instructor['locations'] != "-1") {
                    $locationsArr = explode(",", $instructor['locations']);
                    $locationsStr = "";
                    foreach ($locationsArr as $loc) {
                        try {
                            $locationsStr .= $locations->getLocationNameByID($loc).", ";
                        }
                        catch (Exception $e) {
                            
                        }
                    }
                    $locationsStr = rtrim($locationsStr, ", ");
                }
                else
                    $locationsStr = "No locations set";

                $results[$i]["skills"] = $skillsStr;
                $results[$i]["locations"] = $locationsStr;

                $results[$i]["edit_link"] = "{$instructor['id']}";
                $i++;
            }
        }
        echo "{\"data\":" .json_encode($results). "}";	
    }
}
// handle a POST
if ($verb == "POST") {
        $id = $db->escape($_POST["id"]);
        $updateFields['name'] = $db->escape($_POST["name"]);
        $updateFields['email'] = $db->escape($_POST["email"]);
        $updateFields['mobile'] = $db->escape($_POST["mobile"]);
        $updateFields['locations'] = $db->escape($_POST["locations"]);
        $updateFields['skills'] = $db->escape($_POST["skills"]);
        $updateFields['companies']  = $locations->getCompaniesFromLocations($_POST["locations"], "str");
        
        $result = $instructors->updateInstructorFields($id, $updateFields);
        
        echo json_encode("success");

}

/*
$link = mysql_pconnect($mainframe->getCfg('host'), $mainframe->getCfg('user'), $mainframe->getCfg('password')) or die("Unable To Connect To Database Server");

mysql_select_db($mainframe->getCfg('db')) or die("Unable To Connect To DB");

// add the header line to specify that the content type is JSON
header("Content-type: application/json");

// determine the request type
$verb = $_SERVER["REQUEST_METHOD"];

// handle a GET
if ($verb == "GET") {
       
	$arr = array();
	$rs = mysql_query("
        SELECT u.id, u.name, cfvm.value as mobile, email, cfvs.value as skills,cfvp.value as permcov, rosl.`name` as locations 
        FROM pr_users u
        LEFT JOIN pr_community_fields_values cfvm on u.id=cfvm.user_id AND cfvm.field_id=6 
        LEFT JOIN pr_community_fields_values cfvs on u.id=cfvs.user_id AND cfvs.field_id=19 
        LEFT JOIN pr_community_fields_values cfvp on u.id=cfvp.user_id AND cfvp.field_id=21 
        LEFT JOIN pr_community_fields_values cfvl on u.id=cfvl.user_id AND cfvl.field_id=22 
        LEFT JOIN rooster.locations rosl on cfvl.value = rosl.nodeID
        ORDER BY u.name        
        ");
	while($obj = mysql_fetch_object($rs)) {
		$arr[] = $obj;
	}
	
	echo "{\"data\":" .json_encode($arr). "}";	
}

// handle a POST
if ($verb == "POST") {
	$name = mysql_real_escape_string($_POST["name"]);
	$mobile = mysql_real_escape_string($_POST["mobile"]);

//$username = mysql_real_escape_string($_POST["username"]);
	$email = mysql_real_escape_string($_POST["email"]);
	$id = mysql_real_escape_string($_POST["id"]);

	$rs = mysql_query("UPDATE pr_users SET name= '" .$name ."',email= '".$email."' WHERE id = " .$id);
	$rs = mysql_query("UPDATE pr_community_fields_values SET value= '" .$mobile."' WHERE field_id=6 AND user_id = " .$id);
//	$rs = mysql_query("UPDATE pr_community_fields_values SET value= '" .$mobile."' WHERE field_id=6 AND user_id = " .$id);


	if ($rs) {
		echo json_encode($rs);
	}
	else {
		header("HTTP/1.1 500 Internal Server Error");
		echo "Update failed for ID: " .$id;
	}
	

}
*/

$db->close(); // Closing DB connection
?>