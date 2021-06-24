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

    // database connection needs to be open and user logged in for these functions to work
    function validateID_RandID($dbconn, $sid) {
            //SQL injection defense
            $pattern = '/[^0-9,\-]/';
            if (preg_match($pattern, $sid)){
                exit();
            }

            $dashSeperated = explode("-" , $sid);
            $randId = implode('-' , array_slice($dashSeperated, 1 , 4));
            $id = $dashSeperated[0];

            $searchDataQuery = "SELECT s.id, s.random_id
        		FROM uploads s
        		WHERE s.id = '".$id."' AND s.random_id = '".$randId."';";

            $res = pg_query($searchDataQuery) or die('Query failed: ' . pg_last_error());
        	if (pg_num_rows ($res) != 1) {
        	    pg_free_result($res);
                return -1;
        	} else {
                pg_free_result($res);
                return $id;
            }
            return -1;
    }

    function isSuperUser($dbconn, $userID) {
        $rights = getUserRights ($dbconn, $userID);
        return $rights["isSuperUser"];
    }

    // ask if user has rights to see an individual search - used for searchsubmit 'base new' functionality
    function canUserAccessSearch ($dbconn, $userID, $searchID) {
        $userRights = getUserRights ($dbconn, $userID);
        if ($userRights["isSuperUser"]) {
            return true;
        }
        
        pg_prepare($dbconn, "", "SELECT uploadedby, private, hidden FROM search WHERE id = $1");
        $result = pg_execute ($dbconn, "", [$searchID]);
        $row = pg_fetch_assoc ($result);
        
        return ($row["uploadedby"] === $userID) || ($userRights["canSeeAll"] && !isTrue($row["private"]) && !isTrue($row["hidden"]));
    }

    function getUserRights ($dbconn, $userID) {
        pg_prepare($dbconn, "", "SELECT * FROM users WHERE id = $1");
        $result = pg_execute ($dbconn, "", [$userID]);
        $row = pg_fetch_assoc ($result);

        $canSeeAll = (isset($row["see_all"]) && isTrue($row["see_all"]));  // 1 if see_all flag is present and true
        $canAddNewSearch = (isset($row["can_add_search"]) && isTrue($row["can_add_search"]));  // 1 if can_add_search flag is present and true
        $isSuperUser = (isset($row["super_user"]) && isTrue($row["super_user"]));  // 1 if super_user flag is present AND true
        $maxAAs = 0; //isset($row["max_aas"]) ? (int)$row["max_aas"] : 0;   // max aas and spectra now decided by user groups table
        $maxSpectra = 0; //isset($row["max_spectra"]) ? (int)$row["max_spectra"] : 0;
        $maxSearchCount = 10000;
        $maxSearchLifetime = 1000;
        $maxSearchesPerDay = 100;
        $searchDenyReason = $canAddNewSearch ? "" : "Your user role is not allowed to add new searches.";

        if (doesColumnExist ($dbconn, "user_groups", "max_aas")) {
            pg_prepare($dbconn, "", "SELECT max(user_groups.max_search_count) as max_search_count, max(user_groups.max_spectra) as max_spectra, max(user_groups.max_aas) as max_aas, max(user_groups.search_lifetime_days) as max_search_lifetime, max(user_groups.max_searches_per_day) as max_searches_per_day,
            BOOL_OR(user_groups.see_all)::int AS see_all,
            BOOL_OR(user_groups.super_user)::int AS super_user,
            BOOL_OR(user_groups.can_add_search)::int AS can_add_search
            FROM user_groups
            JOIN user_in_group ON user_in_group.group_id = user_groups.id
            JOIN users ON users.id = user_in_group.user_id
            WHERE users.id = $1");
            $result = pg_execute ($dbconn, "", [$userID]);
            $row = pg_fetch_assoc ($result);

            $maxSearchCount = (int)$row["max_search_count"];
            $maxSearchLifetime = (int)$row["max_search_lifetime"];
            $maxSearchesPerDay = (int)$row["max_searches_per_day"];
            $maxAAs = max($maxAAs, (int)$row["max_aas"]);
            $maxSpectra = max($maxSpectra, (int)$row["max_spectra"]);
            $canSeeAll = $canSeeAll || (!isset($row["see_all"]) || isTrue($row["see_all"]));
            $canAddNewSearch = $canAddNewSearch || (!isset($row["can_add_search"]) || isTrue($row["can_add_search"]));
            $isSuperUser = $isSuperUser || (isset($row["super_user"]) && isTrue($row["super_user"]));

            if ($canAddNewSearch) {
                $userSearches = countUserSearches ($dbconn, $userID);
                if ($maxSearchCount !== null && (int)$userSearches["alltime"] >= $maxSearchCount) {
                    $canAddNewSearch = false;
                    $searchDenyReason = "You already have ".$maxSearchCount." or more active searches. Consider hiding some of them to allow new searches.";
                }

                if ($maxSearchesPerDay !== null && (int)$userSearches["today"] >= $maxSearchesPerDay) {
                    $canAddNewSearch = false;
                    $searchDenyReason = "Limit met. Your user profile is restricted to ".$maxSearchesPerDay." new search(es) per day.";
                }
            }
        } else {
            $maxAAs = max($maxAAs, 1000);
            $maxSpectra = max($maxSpectra, 1000000);
        }

        // Test if searchSubmit exists as a sibling project
        $doesSearchSubmitExist = file_exists ("../../searchSubmit/");
        if ($doesSearchSubmitExist === false) {
            $canAddNewSearch = false;
        }

        // Test if userGUI exists as a sibling project
        $doesUserGUIExist = file_exists ("../../userGUI/");

        $userRights = array ("canSeeAll"=>$canSeeAll, "canAddNewSearch"=>$canAddNewSearch, "isSuperUser"=>$isSuperUser, "maxAAs"=>$maxAAs, "maxSpectra"=>$maxSpectra, "maxSearchLifetime"=>$maxSearchLifetime, "maxUserSearches"=>$maxSearchCount, "maxUserSearchesToday"=>$maxSearchesPerDay, "searchDenyReason"=>$searchDenyReason, "doesUserGUIExist"=>$doesUserGUIExist);

		return $userRights;
    }

    // Number of searches by a particular user (in fields 'alltime' and 'today')
    function countUserSearches ($dbconn, $userID) {
        pg_prepare ($dbconn, "", "SELECT COUNT(*) as alltime, coalesce(SUM(case when submit_date::date = now()::date then 1 end), 0) as today FROM search WHERE uploadedby = $1 AND (hidden ISNULL or hidden = 'f')");
        $result = pg_execute ($dbconn, "", [$userID]);
        return pg_fetch_assoc ($result);
    }

    function doesColumnExist ($dbconn, $tableName, $columnName) {
        pg_prepare($dbconn, "", "SELECT COUNT(column_name) FROM information_schema.columns WHERE table_name=$1 AND column_name=$2");
        $result = pg_execute ($dbconn, "", [$tableName, $columnName]);
        $row = pg_fetch_assoc ($result);
        return isTrue($row["count"]);
    }


	function refuseAcqSeqPermission ($dbconn, $userID, $table, $uploadIDArray, $isSuperUser = null) {
		if ($isSuperUser === null) {
			$isSuperUser = isSuperUser ($dbconn, $userID);
			//error_log (print_r ("getting su status", true));
		}
		$isSuperUser = $isSuperUser ? "t" : "f";
		$arrString = "{".join(",", $uploadIDArray)."}";
		//error_log (print_r ("params ".$userID.", ".$isSuperUser.", ".$arrString, true));

		// table name can't be parameterised - https://stackoverflow.com/questions/11312737
		pg_prepare ($dbconn, "", "SELECT id,private,private and not($2 or uploadedby=$1) as refused from ".$table." where id = ANY($3)");
        $result = pg_execute ($dbconn, "", [$userID, $isSuperUser, $arrString]);

		return resultsAsArray ($result);
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

 	function checkSufficientDiskSpace () {
        return disk_free_space (".");
    }
    function ajaxLoginRedirect () {
        // from http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
         echo (json_encode (array ("redirect" => "../xi3/login.html")));
    }
    function ajaxHistoryRedirect ($why) {
        // from http://stackoverflow.com/questions/199099/how-to-manage-a-redirect-request-after-a-jquery-ajax-call
         echo (json_encode (array ("redirect" => "../history/history.html", "why" => $why."<br>Press the button below to go to the Xi history page.")));
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

    function validateCaptcha ($captcha) {
        include ('../../../xi_ini/emailInfo.php');

        $ip = $_SERVER['REMOTE_ADDR'];
        $response=file_get_contents("https://www.recaptcha.net/recaptcha/api/siteverify?secret=".$secretRecaptchaKey."&response=".$captcha."&remoteip=".$ip);
        $responseKeys = json_decode($response,true);
        //error_log (print_r ($responseKeys, true));
        if (intval($responseKeys["success"]) !== 1) {
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

        $mail->SetFrom($myMailInfo["account"], 'Xi');
        $mail->Subject    = $subject;
        $mail->AddAddress($toEmail, $userName);

        // $mail->AddAttachment("images/phpmailer.gif");        // attachment
        return $mail;
    }

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
                        $url = $urlRoot."userGUI/passwordReset.html?ptoken=".$ptoken;
                        $mail->MsgHTML (getTextString ("resetPasswordEmailBody", [$userName, $url]));
                        //error_log (print_r ($ptoken, true));
                        //error_log (print_r ($id, true));

                        pg_query("COMMIT");

                        if(!$mail->Send()) {
                            //error_log (print_r ("failsend", true));
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
            $_strings = json_decode (file_get_contents ("../json/msgs.json"), true);
        }
        $lang = "en";
        $str = $_strings[$lang][$key];
        if ($vars) {
            $str = preg_replace(array('/\$1/', '/\$2/', '/\$3/', '/\$4/', '/\$5/', '/\$6/', '/\$7/', '/\$8/', '/\$9/'), $vars, $str);
        }
        //error_log (print_r ($str, true));
        return $str;
    }

    function sendGithubIssue ($issueName, $issueText) {
        include ('../../../xi_ini/emailInfo.php');  // $private $gitHub values
        
        //$ch = curl_init('https://api.github.com/repos/Rappsilber-Laboratory/xiView_container/issues');
        $ch = curl_init('https://api.github.com/repos/'.$gitHubRepo.'/issues');

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            //'Accept: application/vnd.github.v3+json',
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: token '.$gitHubIssueToken,
            'User-Agent: '.$gitHubIssueUser
        ]);
        
        curl_setopt ($ch,CURLOPT_POST, 1);
        $payload = array ("title" => normalizeString2($issueName), "body" => normalizeString2($issueText));
        curl_setopt ($ch,CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_USERNAME, $gitHubIssueUser);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);

        $json = curl_exec($ch);
        
        curl_close($ch);
        
        return $json;
    }
?>
