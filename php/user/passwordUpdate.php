<?php
include ('../../connectionString.php');
include ('../../vendor/php/utils.php');

try {   
    $ptoken = validatePostVar ("token", '/.{28,}/', false, null, getTextString("tokenShortError"));
    $pword = validatePostVar ("new-login-pass", '/.{6,}/');
        
    $dbconn = pg_connect($connectionString);
    try {
        //$baseDir = $_SESSION["baseDir"];

        /* test if token exists */
        pg_prepare ($dbconn, "doesTokenExist", "SELECT id, extract(epoch from (now()::timestamp - ptoken_timestamp::timestamp)) AS diffseconds FROM users WHERE ptoken = $1 AND ptoken <> ''");
        $result = pg_execute($dbconn, "doesTokenExist", [$ptoken]);
        $count=intval(pg_numrows($result));
        
        if ($count == 1) {
            $returnRow = pg_fetch_assoc ($result);
            $id = $returnRow['id'];
            $diff = $returnRow['diffseconds'];
            //error_log (print_r ($returnRow, true));
            
            if (isset($diff) && intval ($diff) < 7200) {
                $hash = password_hash ($pword, PASSWORD_BCRYPT);
                
                pg_query("BEGIN") or die("Could not start transaction\n");
                pg_prepare ($dbconn, "setNewPassword", "UPDATE users SET password = $2, ptoken = '', ptoken_timestamp = NULL, hidden = false WHERE id = $1");
                $result = pg_execute($dbconn, "setNewPassword", [$id, $hash]);
                pg_query("COMMIT");
                
                echo (json_encode(array ("status"=>"success", "msg"=> getTextString("passwordResetSuccess"))));
            }
            else {
                echo (json_encode(array ("status"=>"fail", "msg"=> getTextString("tokenExpiredError"))));
            }
        } else {
            echo (json_encode(array ("status"=>"fail", "msg"=> getTextString("tokenMismatchError"))));
        }

    } catch (Exception $e) {
         pg_query("ROLLBACK");
         $date = date("d-M-Y H:i:s");
         $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString("passwordResetCatchall");
        //error_log (print_r ($msg, true));
         echo (json_encode(array ("status"=>"fail", "msg"=> $msg."<br>".$date)));
    }

    //close connection
    pg_close($dbconn);

} catch (Exception $e) {
     $date = date("d-M-Y H:i:s");
     $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString("databaseConnectError");
     //error_log (print_r ($msg, true));
     echo (json_encode(array ("status"=>"fail", "msg"=> $msg."<br>".$date)));
}

?>