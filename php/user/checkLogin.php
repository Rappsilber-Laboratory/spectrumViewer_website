<?php
include('../../connectionString.php');
// Connect to server
$dbconn = pg_connect($connectionString)or die("cannot connect");
$date = date("d-M-Y H:i:s");

try {
    //error_log (print_r ($_POST, true));
    // Define $myusername and $mypass
	if (isset($_POST['login-name'], $_POST['login-pass'])) {
		$myusername=$_POST['login-name'];
		$mypass=$_POST['login-pass'];

		// To protect MySQL injection
		$myusername = stripslashes($myusername);
		$mypass = stripslashes($mypass);

		$get = pg_prepare($dbconn, "getUserData", "SELECT password, id, gdpr_timestamp FROM users WHERE user_name=$1");
		$result = pg_execute($dbconn, "getUserData", [$myusername]);
		$line = pg_fetch_assoc($result);
		$hash = $line["password"];
		$user_id = $line["id"];

		// Mysql_num_row is counting table row
		$count = pg_numrows($result);
		$date = date("d-M-Y H:i:s");

		// if no username match
		if ($count === 0) {
			echo (json_encode(array ("status"=>"fail", "msg"=>"< Incorrect Password / Username combination", "field"=>"login-name")));
		}
        // if no gdpr_token
		else if (!isset($line['gdpr_timestamp'])) {
			    $redirectTo = "../xiNET_website/confirmationReminder.php";
                //error_log (print_r ($redirectTo, true));
                echo (json_encode(array ("status"=>"email confirmation required", "redirect"=> $redirectTo)));    
	    }
		// If result matched $myusername and $mypass, table row must be 1 row
		else if ($count === 1 && password_verify ($mypass, $hash)) {
        	session_start();
			$_SESSION['session_name'] = $myusername;
			$_SESSION['user_id'] = $user_id;
			//$redirectTo = empty($_POST["redirect"]) ? "../history/history.html" : $_POST["redirect"];
			$redirectTo = "../history/history.html";
			//error_log (print_r ($redirectTo, true));
			echo (json_encode(array ("status"=>"success", "redirect"=> $redirectTo)));
		} else {
			echo (json_encode(array ("status"=>"fail", "msg"=>"< Incorrect Password / Username combination", "field"=>"login-name")));
		}
	} else {
		echo (json_encode(array ("status"=>"fail", "msg"=>"< One or both of Password / Username is missing", "field"=>"login-name")));
	}
} catch (Exception $e) {
    $msg = ($e->getMessage()) ? ($e->getMessage()) : "An error occurred when attempting to log in.";
    echo (json_encode(array ("status"=>"fail", "error"=> $msg."<br>".$date)));
}

//close connection
pg_close($dbconn);
?>
