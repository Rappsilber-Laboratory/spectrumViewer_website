<?php
include ('../../connectionString.php');
include ('../../vendor/php/utils.php');

try {   
    $token = validatePostVar ("token", '/.{28,}/', false, null, getTextString("tokenShortError"));
	
    error_log (print_r ($token, true));
	
    $dbconn = pg_connect($connectionString);
    try {
        /* test if token exists */
        pg_prepare ($dbconn, "doesTokenExist", "SELECT id FROM users WHERE gdpr_token = $1 AND gdpr_token <> ''");
        $result = pg_execute($dbconn, "doesTokenExist", [$token]);
        $count=intval(pg_numrows($result));
        
        if ($count == 1) {
            $returnRow = pg_fetch_assoc ($result);
            $id = $returnRow['id'];
			
			error_log (print_r ($returnRow, true));

			pg_query("BEGIN") or die("Could not start transaction\n");
			pg_prepare ($dbconn, "setGDPRacceptance", "UPDATE users SET gdpr_token = '', gdpr_timestamp = NOW() WHERE id = $1");
			$result = pg_execute($dbconn, "setGDPRacceptance", [$id]);
			pg_query("COMMIT");

			echo (json_encode(array ("status"=>"success", "msg"=> getTextString("passwordResetSuccess"))));

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