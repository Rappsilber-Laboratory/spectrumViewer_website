<?php
session_start();
include ('../../vendor/php/utils.php');
if (empty ($_SESSION['session_name'])) {
    // from http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
    ajaxLoginRedirect();
}
else { 
    include('../../connectionString.php');
    include ('../../../xi_ini/emailInfo.php');
    
    $dbconn = pg_connect($connectionString);
    try {
        //$baseDir = $_SESSION["baseDir"];
        pg_query("BEGIN") or die("Could not start transaction\n");
        
        $isSuperUser = isSuperUser ($dbconn, $_SESSION['user_id']);
        $email = null;
        $username = null;

        if ($isSuperUser || ($_SESSION['user_id'] === $_POST["id"])) {  // Only superuser or account owner can delete user account
            // Hide option
            /*
            $hideSearches = pg_prepare ($dbconn, "hideAllUsersSearches", "UPDATE search SET hidden = true WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersSearches", [$_POST["id"]]);
            $hideAcqs = pg_prepare ($dbconn, "hideAllUsersAcqs", "UPDATE acquisition SET private = true WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersAcqs", [$_POST["id"]]);
            $hideSequences = pg_prepare ($dbconn, "hideAllUsersSeqs", "UPDATE sequence_file SET private = true WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersSeqs", [$_POST["id"]]);
            // need to grab current email address for delete notification before overwriting it
            $disableUser = pg_prepare($dbconn, "getCurrentUserDetails", "SELECT user_name,email FROM users WHERE id = $1"); 
            $result = pg_execute($dbconn, "getCurrentUserDetails", [$_POST["id"]]);
            */
            
            // Better hide option - set hidden = true in user table
            // need to grab current email address for delete notification before overwriting it
            $disableUser = pg_prepare($dbconn, "getCurrentUserDetails", "SELECT user_name,email FROM users WHERE id = $1"); 
            $result = pg_execute($dbconn, "getCurrentUserDetails", [$_POST["id"]]);
            
            // Nuclear Option
            /*
            $deleteSearches = pg_prepare ($dbconn, "hideAllUsersSearches", "DELETE FROM search WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersSearches", [$_POST["id"]]);
            $deleteAcqs = pg_prepare ($dbconn, "hideAllUsersAcqs", "DELETE FROM acquisition WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersAcqs", [$_POST["id"]]);
            $deleteSequences = pg_prepare ($dbconn, "hideAllUsersSeqs", "DELETE FROM sequence_file WHERE uploadedby = $1");
            $result = pg_execute($dbconn, "hideAllUsersSeqs", [$_POST["id"]]);
            $deleteUserInGroup = pg_prepare($dbconn, "deleteUserInGroup", "DELETE FROM user_in_group WHERE user_id = $1");
            $result = pg_execute($dbconn, "deleteUserInGroup", [$_POST["id"]]);
            $deleteUser = pg_prepare($dbconn, "deleteUser", "DELETE FROM users WHERE id = $1 RETURNING user_name, email");
            $result = pg_execute($dbconn, "deleteUser", [$_POST["id"]]);
            */
            
            $returnRow = pg_fetch_assoc ($result); // return the deleted/updated row (or selected parts thereof)
            $email = $returnRow["email"];
            $username = $returnRow["user_name"];
            
            $disableUser = pg_prepare($dbconn, "disableUser", "UPDATE users SET email = '', password = '', ptoken = '', hidden = true WHERE id = $1"); // They can't log in anymore, nor get a password reset request
            $result = pg_execute($dbconn, "disableUser", [$_POST["id"]]);
            //error_log (print_r ($returnRow, true));
        } else {
            throw new Exception (getTextString("deletePermissionError"));
        }

         pg_query("COMMIT");
        
        if (filter_var ($email, FILTER_VALIDATE_EMAIL)) {                     
            require_once ('../vendor/php/PHPMailer-master/src/PHPMailer.php');
            require_once ('../vendor/php/PHPMailer-master/src/SMTP.php');

            $mail = makePHPMailerObj ($mailInfo, $email, $username, getTextString("deleteUserEmailHeader"));
            $mail->MsgHTML(getTextString("deleteUserEmailBody"));

            if(!$mail->Send()) {
                throw new Exception (getTextString ("deletedFailedEmailWarning"));
            } 
        } else {
            throw new Exception (getTextString ("deletedMissingEmailWarning"));
        }

         echo (json_encode(array ("status"=>"success", "deletedUser"=>$returnRow)));
    } catch (Exception $e) {
         pg_query("ROLLBACK");
         $date = date("d-M-Y H:i:s");
         $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString ("deleteCatchallError");
         echo (json_encode(array ("status"=>"fail", "error"=> $msg."<br>".$date)));
    }

    //close connection
    pg_close($dbconn);
}
?>