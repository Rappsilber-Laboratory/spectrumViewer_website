<?php
include ('../../connectionString.php');
include ('../../../xi_ini/emailInfo.php');
include ('../../vendor/php/utils.php');

try {
    //error_log (print_r ($_POST, true));   // This printed passwords in plain text to the php log. Lol.

    $config = json_decode (file_get_contents ("../json/config.json"), true);

    $captcha = validatePostVar ("g-recaptcha-response", '/.{1,}/', false, "recaptchaWidget");
    $email = validatePostVar ("email", $config["emailRegex"], true);
    // $username = validatePostVar ("username", '/^[a-zA-Z0-9-_.]{4,16}/');
    // $pword = validatePostVar ("pass", '/.{6,}/');

    // validate captcha
    validateCaptcha ($captcha);

    $dbconn = pg_connect($connectionString);
    try {
        /* test if username already exists */
        // pg_prepare ($dbconn, "doesUserExist", "SELECT COUNT(user_name) FROM users WHERE user_name = $1;");
        // $result = pg_execute($dbconn, "doesUserExist", [$username]);
        // $returnRow = pg_fetch_assoc ($result);
        // if (intval($returnRow["count"]) > 0) {
        //     echo (json_encode(array ("status"=>"fail", "field"=> "username", "msg"=>getTextString("newUserUniqueNameError", [$username]), "revalidate"=> true)));
        //     exit;
        // }

        /* test if email already exists */
        pg_prepare ($dbconn, "doesEmailExist", "SELECT gdpr_token FROM users WHERE email = $1 AND gdpr_timestamp IS NULL;");
        $result = pg_execute($dbconn, "doesEmailExist", [$email]);
        $returnRow = pg_fetch_assoc ($result);
        $gdpr_token = $returnRow["gdpr_token"];
        if ($gdpr_token) {
            require_once    ('../../vendor/php/PHPMailer-master/src/PHPMailer.php');
    		require_once    ('../../vendor/php/PHPMailer-master/src/SMTP.php');

    		$url = $urlRoot."userGUI/GDPRacceptance.html?gdpr_token=".$gdpr_token;
    		$mail = makePHPMailerObj ($mailInfo, $email, "RESEND", getTextString("confirmationResendEmailHeader"));
    		$mail->MsgHTML(getTextString("confirmationResendEmailBody", [$url]));
        }

        // database has 20 character limit on user_name field, throws sql error if bigger
        // limit use of existing user_name to generate new name, as if it's 20 chars the new name would be identical and this will throw an error too
        // pg_query("BEGIN") or die("Could not start transaction\n");
        // $hash = $pword ? password_hash ($pword, PASSWORD_BCRYPT) : "";
		// $gdpr_token = chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 );
        // $newUser = pg_prepare($dbconn, "newUser", "INSERT INTO users (user_name, password, email, max_aas, max_spectra, gdpr_token) VALUES($1, $3, $2, 100000000, 100000, $4) RETURNING id, user_name");
        // $result = pg_execute($dbconn, "newUser", [$username, $email, $hash, $gdpr_token]);
        // $returnRow = pg_fetch_assoc ($result); // return the inserted row (or selected parts thereof)
        // $returnedID = $returnRow["id"];
        //
        // $addUserToGroup = pg_prepare($dbconn, "addUserToGroup", "INSERT INTO user_in_group (user_id, group_id) VALUES($1, $2)");
        // $result = pg_execute($dbconn, "addUserToGroup", [$returnedID, "12"]);


		if(!$mail->Send()) {
			error_log (print_r ("failsend", true));
			pg_query("ROLLBACK");
			echo json_encode (array ("status"=>"fail", "msg"=>getTextString("newUserEmailError", [$mail->ErrorInfo]), "revalidate"=> true));
		}
		else {
			pg_query("COMMIT");	// only commit if email to user was successful, otherwise we get a user in the database but no way for real person to interact with account
			$json = json_encode(array ("status"=>"success", "msg"=> getTextString("confirmationResendEmailInfo", [$username]), "username"=>$username));
			echo ($json);
		}

    } catch (Exception $e) {
         pg_query("ROLLBACK");
         $date = date("d-M-Y H:i:s");
         $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString("newUserErrorCatchall");
		 error_log (print_r ($msg, true));
         echo (json_encode(array ("status"=>"fail", "msg"=> $msg."<br>".$date, "revalidate"=> true)));
    }

    //close connection
    pg_close($dbconn);

} catch (Exception $e) {
     $msg = ($e->getMessage()) ? ($e->getMessage()) : getTextString("newUserErrorCatchall");
     error_log (print_r ($msg, true));
     echo (json_encode(array ("status"=>"fail", "msg"=> $msg."<br>".$date)));
}

?>
