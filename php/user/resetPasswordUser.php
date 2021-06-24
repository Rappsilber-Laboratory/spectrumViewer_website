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
        
        if ($_POST['id'] === $_SESSION['user_id'] || isSuperUser ($dbconn, $_SESSION['user_id'])) {
            pg_prepare ($dbconn, "getUserEmail", "SELECT user_name, email FROM users WHERE id = $1");
            $result = pg_execute($dbconn, "getUserEmail", [$_POST['id']]);
            $returnRow = pg_fetch_assoc ($result); // return the inserted row (or selected parts thereof)
            
            if (isset($returnRow['email']) && strlen($returnRow['email']) > 2) {
                $_POST['email'] = $returnRow['email'];
                 $config = json_decode (file_get_contents ("../json/config.json"), true);
                
                if (validatePostVar ("email", $config["emailRegex"], true, null, "Invalid email address")) {
                    sendPasswordResetMail ($returnRow['email'], $_POST['id'], $returnRow['user_name'], 1, $dbconn);
                } else {
                    throw new Exception (getTextString("emailInvalid"));
                }
            } else {
                throw new Exception (getTextString("emailEmpty"));
            }
        } 

         pg_query("COMMIT");
         echo (json_encode(array ("status"=>"success", "updatedUser"=>$returnRow)));
    } catch (Exception $e) {
         pg_query("ROLLBACK");
         $date = date("d-M-Y H:i:s");
         $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString ("userDatabaseError");
         echo (json_encode(array ("status"=>"fail", "error"=> $msg."<br>".$date)));
    }

    //close connection
    pg_close($dbconn);
}
?>