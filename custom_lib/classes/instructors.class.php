<?php 

/**
 * @author mpak
 */
class Instructors {
    private $db;
    private $companyID;
    private $roles;
    private $mappingID = array(
        "mobile" => 6,
        "address" => 8,
        "state" => 9,
        "suburb" => 10,
        "abn" => 17,
        "gst" => 18,
        "skills" => 19,
        "hourly" => 20,
        "permOrCover" => 21,
        "locations" => 22,
        "companies" => 23
    );
    
    public function __construct($db=null, $user=null) {
        if ($db != null) {
            $this->db = $db;
            $this->user = $user;
            $this->schema = $this->db->schema;
        }
        else
            throw new Exception("DB connection required.");
    }
    
    
    /**
     * Returns list of instructors. 
     *
     * @param string    $order      Column to order results with.
     * @param direction $direction  ASC for ascending or DESC for descending. DESC by default
     * 
     * @return array associative array containing the results
     *
     */
    public function getListOfInstructors($order="", $direction = "DESC") {
        if ($this->user != null) {
            $whereSQL = "WHERE ";
            
            # Fixes exception thrown 
            if ($this->user->userGroups == null)
                $this->user->userGroups = array();
            
            # Returning list of instructors allowed to accounts payable and company system admin
            if ( in_array("Accounts Payable", $this->user->userGroups) || 
                 in_array("Administrator", $this->user->userGroups) 
               ) {
                 # Parsing parent companies to create sql WHERE statement
                foreach ($this->user->locations['company'] as $id => $info) {
                    $whereSQL .= '(cfvc.value LIKE "%'.$id.',%" OR cfvc.value LIKE "%,'.$id.'%" OR cfvc.value LIKE "'.$id.'") OR ';
                }
                
                $whereSQL = substr($whereSQL, 0, strrpos($whereSQL, "OR "));
            }
            # Returning list of instructors allowed to Group Ex Coordinators and Managers
            elseif ( in_array("Group Ex Coordinators", $this->user->userGroups) || 
                 in_array("Group Ex Managers", $this->user->userGroups)
               ) { 
                
                # Parsing physical clubs (a.k.a. locations) to create sql WHERE statement
                foreach ($this->user->locations['location'] as $id => $info) {
                    $whereSQL .= '(cfvl.value LIKE "%'.$id.',%" OR cfvl.value LIKE "%,'.$id.'%" OR cfvl.value LIKE "'.$id.'") OR ';
                }
                $whereSQL = substr($whereSQL, 0, strrpos($whereSQL, "OR "));
            }
            elseif (in_array("Super Users", $this->user->userGroups))
                $whereSQL .= "1";
            else
                return;
            
            //$whereSQL .= " 1 ";
            
            $sql = " 
                SELECT u.id, u.name, cfvm.value as mobile, email, cfvs.value as skills,cfvp.value as permcov, COALESCE(cfvl.value, -1) as locations 
                FROM {$this->schema}.pr_users u
                LEFT JOIN {$this->schema}.pr_community_fields_values cfvm on u.id=cfvm.user_id AND cfvm.field_id=6 
                LEFT JOIN {$this->schema}.pr_community_fields_values cfvs on u.id=cfvs.user_id AND cfvs.field_id=19 
                LEFT JOIN {$this->schema}.pr_community_fields_values cfvp on u.id=cfvp.user_id AND cfvp.field_id=21 
                LEFT JOIN {$this->schema}.pr_community_fields_values cfvl on u.id=cfvl.user_id AND cfvl.field_id=22 
                LEFT JOIN {$this->schema}.pr_community_fields_values cfvc on u.id=cfvl.user_id AND cfvc.field_id=24
                $whereSQL
            ";

            if (!empty($order)) {
                $sql .= " ORDER BY u.$order $direction";
            }
            error_log($sql."\n");
            //echo $sql;
            $instructors = $this->db->getMultiDimensionalArray($sql);

            return $instructors;
        }
        else {
            throw new Exception("Missing user roles");
        }
    }
     
    /**
     * Updates values for an instructor. 
     *
     * @param integer    $instructorID      id of instructor
     * @param array      $fields            Fields to be updated as [key] => [value] array
     * 
     * @return bool     true on success
     *
     */
    public function updateInstructorFields($instructorID, $fields) {
        $userTableConditionSQL = "";
        
        foreach ($fields as $field => $value) {
            if (empty($value))
                continue;
            
            switch ($field) {

            case "name":
            case "email":
                $userTableConditionSQL .= " `$field`='$value',";
                break;
                
            case "mobile":
            case "address":
            case "suburb":
            case "abn":
            case "gst":
            case "skills":
            case "hourly":
            case "permOrCover":
            case "locations":
            case "companies":
            
                try {
                    $idSQL = "SELECT `id` FROM {$this->schema}.pr_community_fields_values WHERE `user_id`=$instructorID AND `field_id`={$this->mappingID[$field]}";
                    $id = $this->db->getSingleValue($idSQL);
                    $value = str_replace("null", "", $value);
                    $value = trim($value, ",");
                    $this->db->update("UPDATE {$this->schema}.pr_community_fields_values SET `value` = \"$value\" WHERE `id` = $id");
                }
                catch (Exception $e) {
                    //echo $e->getMessage();
                    $valuesTableSQL = "INSERT INTO {$this->schema}.pr_community_fields_values (`user_id`, `field_id`, `value`) VALUES ($instructorID, {$this->mappingID[$field]}, \"$value\")";
                    $this->db->insert($valuesTableSQL);
                }
                //echo $valuesTableSQL."\n";
                break;
                
            default:
                break;
            }
        }
        
        if (!empty($userTableConditionSQL)) {
            $userTableConditionSQL = rtrim($userTableConditionSQL, ",");
            $sql = "UPDATE {$this->schema}.pr_users SET $userTableConditionSQL WHERE `id`=$instructorID";
            $this->db->update($sql);
        }
        
        return true;
        
    }

    /**
     * Searches for instructorID value by full name. 
     *
     * @param string    $name      Full name of instructor
     * 
     * @return  int     instructorID or false on failure
     *
     */
    public function getIDbyName($name) {
        $sql = "SELECT id FROM {$this->schema}.pr_users WHERE `name` = \"$name\" ";
        
        try {
            return $this->db->getSingleValue($sql);
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Searches for instructors name by instructorID. 
     *
     * @param string    $instructorID       ID of the instructor in the database.     
     * 
     * @return  string  $name   instructors full name
     *
     */
    public function getNameByID($id) {
        $sql = "SELECT name FROM {$this->schema}.pr_users WHERE `id` = $id ";
        
        try {
            return $this->db->getSingleValue($sql);
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Searches for instructors email by instructorID. 
     *
     * @param string    $instructorID       ID of the instructor in the database.     
     * 
     * @return  string  $email   instructors full name
     *
     */
    public function getEmailByID($id) {
        $sql = "SELECT email FROM {$this->schema}.pr_users WHERE `id` = $id ";
        
        try {
            return $this->db->getSingleValue($sql);
        }
        catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Returns instructor's details by instructorID.
     *
     * @param   int     $id      InstructorID
     * 
     * @return  array   $result[0]  Array with instructor's details
     *
     */
    public function getDetailsByInstructorID($id) {
        #mapping of community_fields table 
        $fields = array (
            "Mobile number" => 6,
            "Address" => 8,
            "Suburb" => 10,
            "State" => 9,
            "Hourly rate" => 20,
            "ABN" => 17,
            "GST" => 18,
            "permOrCover" => 21,
            "Locations" => 22,
            "Skillset" => 19
        );
        
        $sql = "
            SELECT `field_id`, `value` 
            FROM `pr_community_fields_values`
            WHERE `user_id` = $id 
        ";
        
        try {
            $resultArr = $this->db->getMultiDimensionalArray($sql);
            
            $detailsArr = array();
            foreach ($resultArr as $value) {
                $detailsArr[$value['field_id']] = $value['value'];
            }

            $userDetails = array();
            $userDetails['Full name'] = $this->getNameByID($id);
            $userDetails['Email'] = $this->getEmailByID($id);
            foreach ($fields as $key => $fieldID) {
                if(isset($detailsArr[$fieldID]))
                    $userDetails[$key] = $detailsArr[$fieldID];
                else
                    $userDetails[$key] = "";
            }
            
            return $userDetails;
        }
        catch (Exception $e) {
            return false;
        }
        
    }
}
?>
