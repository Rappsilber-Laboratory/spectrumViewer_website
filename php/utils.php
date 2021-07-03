<?php

    use PHPMailer\PHPMailer\PHPMailer;	// https://github.com/PHPMailer/PHPMailer/blob/master/UPGRADING.md#namespace

    // from http://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
    function normalizeString ($str = '') {
        $str = normalizeString1 ($str);
        $str = rawurlencode($str);
        $str = str_replace('%', '-', $str);
        return $str;
    }

    function normalizeString1 ($str = '') {
        $str = normalizeString2 ($str);
        $str = str_replace(' ', '-', $str);
        return $str;
    }

	function normalizeString2 ($str = '') {
        $str = filter_var ($str, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        $str = preg_replace('/[\"\/\<\>\?\'\|]+/', ' ', $str);
        $str = html_entity_decode ($str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        return $str;
    }

    function getNiceDate () {
        return date("d-M-Y H:i:s");
    }



    function doesColumnExist ($dbconn, $tableName, $columnName) {
        pg_prepare($dbconn, "", "SELECT COUNT(column_name) FROM information_schema.columns WHERE table_name=$1 AND column_name=$2");
        $result = pg_execute ($dbconn, "", [$tableName, $columnName]);
        $row = pg_fetch_assoc ($result);
        return isTrue($row["count"]);
    }



    // Turn result set into array of objects and free result
    function resultsAsArray($result) {
        $arr = pg_fetch_all ($result);
        
        // free resultset
        pg_free_result($result);
        return $arr;
    }

	function isTrue ($pgBooleanReturn) {
		$trueArray = array ("TRUE", "true", "t", "yes", "y", "1", 1);
		return in_array ($pgBooleanReturn, $trueArray);
	}

    function ajaxLoginRedirect () {
        // from http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
         echo (json_encode (array ("redirect" => "../xi3/login.html")));
    }

function ajaxBootOut () {
        if (!isset($_SESSION['session_name'])) {
            // https://github.com/Rappsilber-Laboratory/xi3-issue-tracker/issues/94
            // Within an ajax call, calling php header() just returns the contents of login.html, not redirect to it.
            // And since we're usually requesting a json object returning html will cause an error anyways.
            // Thus we return a simple json object with a redirect field for the ajax javascript call to handle
            echo json_encode ((object) ['redirect' => '../xiNET_website']);
            //header("location:../../xi3/login.html");
            exit();
        }
    }

  function validatePostVar ($varName, $regexp, $isEmail=false, $altFormFieldID=null, $msg=null) {
        $a = "";
        if (isset($_POST[$varName])){
            $a = $_POST[$varName];
        }
        //error_log (print_r ($a, true));
        if (!$a || ($isEmail && !filter_var ($a, FILTER_VALIDATE_EMAIL)) || !filter_var ($a, FILTER_VALIDATE_REGEXP, array ('options' => array ('regexp' => $regexp)))) {
            if (isset($msg)) {
                echo (json_encode(array ("status"=>"fail", "msg"=> $msg, "error"=> $msg)));
            } else {
                echo (json_encode(array ("status"=>"fail", "field"=> (isset($altFormFieldID) ? $altFormFieldID: $varName), "error"=> "Input validation failed")));
            }
            exit;
        }
        return $a;
    }

    function validateCaptcha (){//$captcha) {
//        include ('../../../xi_ini/emailInfo.php');
//
//        $ip = $_SERVER['REMOTE_ADDR'];
//        $response=file_get_contents("https://www.recaptcha.net/recaptcha/api/siteverify?secret=".$secretRecaptchaKey."&response=".$captcha."&remoteip=".$ip);
//        $responseKeys = json_decode($response,true);
//        //error_log (print_r ($responseKeys, true));
//        if (intval($responseKeys["success"]) !== 1) {
//            echo (json_encode(array ("status"=>"fail", "msg"=> getTextString("captchaError"), "revalidate"=> true)));
//            exit;
//        }



//        error_log ("COL!!");
//        $captcha = $_POST["CaptchaCode"];
//        error_log (print_r ($captcha, true));
//
//
//        $ExampleCaptcha = new Captcha("ExampleCaptcha");
//        $ExampleCaptcha->UserInputID = "CaptchaCode";
//        $isHuman = $ExampleCaptcha->Validate();
//        error_log ("!!");
//        error_log (print_r ($isHuman, true));
//        if (!$isHuman) {
//            echo (json_encode(array ("status"=>"fail", "msg"=> getTextString("captchaError"), "revalidate"=> true)));
//            exit;
//        }

        $ans = $_POST['CaptchaCode']; // .. or whatever!
        $checksum = md5(strtolower(trim($ans)));
        if (in_array($checksum,$_SESSION['captcha_ans'])) {
            // passed..!
        } else {
            // error: redisplay form, etc.
            echo (json_encode(array ("status"=>"fail", "msg"=> getTextString("captchaError"), "revalidate"=> true)));
            exit;
        }

    }

    function makePhpMailerObj ($myMailInfo, $toEmail, $userName="User Name", $subject="Test Send Mails") {
        $mail               = new PHPMailer();
        $mail->IsSMTP();                                        // telling the class to use SMTP
        $mail->SMTPDebug    = 0;                                // 1 enables SMTP debug information (for testing) - but farts it out to echo, knackering json
        $mail->SMTPAuth     = true;                             // enable SMTP authentication
        $mail->SMTPSecure   = "tls";                            // sets the prefix to the servier
        $mail->Host         = $myMailInfo["host"];                 // sets GMAIL as the SMTP server
        $mail->Port         = $myMailInfo["port"];                              // set the SMTP port for the GMAIL server

        $mail->Username     = $myMailInfo["account"];     // MAIL username
        $mail->Password     = $myMailInfo["password"];    // MAIL password

        try {
            $mail->SetFrom($myMailInfo["account"], 'Xi');
        } catch (\PHPMailer\PHPMailer\Exception $e) {
        }
        $mail->Subject    = $subject;
        $mail->AddAddress($toEmail, $userName);

        // $mail->AddAttachment("images/phpmailer.gif");        // attachment
        return $mail;
    }

/**
 * @throws \PHPMailer\PHPMailer\Exception
 */
/**
 */
/**
 * @throws \PHPMailer\PHPMailer\Exception
 */
/**
 * @throws Exception
 * @throws Exception
 * @throws Exception
 * @throws Exception
 */
function sendPasswordResetMail ($email, $id, $userName, $count, $dbconn) {
        include ('../../../xi_ini/emailInfo.php');
        require_once    ('../../vendor/php/PHPMailer-master/src/PHPMailer.php');
        require_once    ('../../vendor/php/PHPMailer-master/src/SMTP.php');

        //error_log (print_r ($email, true));

        if (strlen($email) > 2) {
            if (filter_var ($email, FILTER_VALIDATE_EMAIL)) {

                if ($count == 1) {
                    //error_log (print_r ($count, true));
                    $mail = makePHPMailerObj ($mailInfo, $email, $userName, getTextString("resetPasswordEmailHeader"));
                    $ptoken = chr( mt_rand( 97 ,122 ) ) .substr( md5( time( ) ) ,1 );
                    pg_prepare ($dbconn, "setToken", "UPDATE users SET ptoken = $2, ptoken_timestamp = now() WHERE id = $1");
                    $result = pg_execute ($dbconn, "setToken", [$id, $ptoken]);
                    //error_log (print_r (pg_fetch_assoc ($result), true));

                    if ($result) {
                        $url = $urlRoot."xiNET_website/passwordReset.php?ptoken=".$ptoken;
                        $mail->MsgHTML (getTextString ("resetPasswordEmailBody", [$userName, $url]));
                        //error_log (print_r ($ptoken, true));
                        //error_log (print_r ($id, true));

                        pg_query("COMMIT");

                        try {
                            if (!$mail->Send()) {
                                //error_log (print_r ("failsend", true));
                            }
                        } catch (\PHPMailer\PHPMailer\Exception $e) {
                        }
                    } else {
                        throw new Exception (getTextString("genDatabaseError"));
                    }
                } else {
                    throw new Exception (getTextString("emailTaken"));
                }
            } else {
                throw new Exception (getTextString("emailInvalid"));
            }
        } else {
            throw new Exception (getTextString("emailEmpty"));
        }
    }

    function getTextString ($key, $vars = NULL) {
        if (!isset($_strings)) {
            //error_log ("init strings");
            $_strings = json_decode (file_get_contents ("../../js/msgs.json"), true);
        }
        $lang = "en";

        $str = $_strings[$lang][$key];
        if ($vars) {
            $str = preg_replace(array('/\$1/', '/\$2/', '/\$3/', '/\$4/', '/\$5/', '/\$6/', '/\$7/', '/\$8/', '/\$9/'), $vars, $str);
        }
//        error_log (print_r ($str, true));
        return $str;
    }

?>
