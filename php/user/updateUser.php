<?php
session_start();
include ('../../vendor/php/utils.php');
if (empty ($_SESSION['session_name'])) {
    // from http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
    ajaxLoginRedirect();
}
else { 
    include('../../connectionString.php');
    
    $dbconn = pg_connect($connectionString);
    try {
        pg_query("BEGIN") or die("Could not start transaction\n");
        
        //error_log (print_r ($_POST, true));
        
        $isSuperUser = isSuperUser ($dbconn, $_SESSION['user_id']);
        
        // replace empty strings with nulls
        foreach($_POST as $key => $value){
            if ($value === "") {
                $_POST[$key] = null;
            }
        }
            
        if ($isSuperUser) {
            //$preparedStr = "UPDATE users SET super_user = $2, can_add_search = $3, see_all = $4, email = $5 WHERE id = $1";
            //$updateUser = pg_prepare($dbconn, "updateUser", $preparedStr);
            //$qParams = [$_POST["id"], $_POST["super_user"], $_POST["can_add_search"], $_POST["see_all"], $_POST["email"]];
            $preparedStr = "UPDATE users SET email = $2 WHERE id = $1";
            $updateUser = pg_prepare($dbconn, "updateUser", $preparedStr);
            $qParams = [$_POST["id"], $_POST["email"]];
            $result = pg_execute($dbconn, "updateUser", $qParams);
            $returnRow = pg_fetch_assoc ($result); // return the inserted row (or selected parts thereof)
            
            $preparedStr = "DELETE FROM user_in_group WHERE user_id = $1";
            $updateUser = pg_prepare($dbconn, "deleteUserGroups", $preparedStr);
            $qParams = [$_POST["id"]];
            $result = pg_execute($dbconn, "deleteUserGroups", $qParams);
            
            $groupVals = $_POST["user_group"];
            $preparedStr = "INSERT INTO user_in_group (user_id, group_id) VALUES ($1, $2)";
            $updateUser = pg_prepare($dbconn, "restoreUserGroups", $preparedStr);
            $qParams = [$_POST["id"], "?"];
            for ($i=0; $i<count($groupVals); $i++) {
                $qParams[1] = $groupVals[$i];
                $result = pg_execute($dbconn, "restoreUserGroups", $qParams);
            }
            
        } else {
            // only change if session id equals post id (stops client sending spurious post ids to try and change other people's details)
            if ($_POST['id'] === $_SESSION['user_id']) {
                $preparedStr = "UPDATE users SET email = $2 WHERE id = $1";
                $qParams = [$_POST["id"], $_POST["email"]];
                $updateUser = pg_prepare($dbconn, "updateUser2", $preparedStr);
                $result = pg_execute($dbconn, "updateUser2", $qParams);
                $returnRow = pg_fetch_assoc ($result); // return the inserted row (or selected parts thereof)
            } else {
                throw new Exception (getTextString("updatePermissionError"));
            }
        }

         pg_query("COMMIT");
         echo (json_encode(array ("status"=>"success", "updatedUser"=>$returnRow)));
    } catch (Exception $e) {
         pg_query("ROLLBACK");
         $date = date("d-M-Y H:i:s");
         $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString("userDatabaseUpdateCatchall");
         echo (json_encode(array ("status"=>"fail", "error"=> $msg."<br>".$date)));
    }

    //close connection
    pg_close($dbconn);
}
?>