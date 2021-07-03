<!DOCTYPE HTML>
<html lang="en">

<head>
    <title>xiVIEW | Create Account</title>
    <?php
    session_start();
    include("head.php");
    ?>
    <link rel="stylesheet" href="./css/common.css"/>
    <link rel="stylesheet" href="./css/login.css"/>
    <link type="text/css" rel="Stylesheet" href="css/font-awesome.min.css">
</head>

<body>
<!-- Sidebar -->
<?php include("navigation.php"); ?>


<!-- Main -->
<div id="main">
    <div class="container">
        <h1 class="page-header">Create Account</h1>
        <p><a href="https://player.vimeo.com/video/298348200" target="_blank">Account creation video tutorial</a></p>
        <br/>
        <br/>

        <div class="login">

            <form id="register_form" name="register_form" method="post" action="./php/user/registerNewUser.php"
                  class="login-form">

                <div class="control-group">
                    <label for="username">Pick Username</label>
                    <input type="text" value="" placeholder="Username" id="username" name="username"
                           pattern="^[a-zA-Z0-9-_.]{4,16}" oninput="this.setCustomValidity('')" required autofocus/>
                    <span class="error" id="username-errorMsg"></span>
                    <span class="error2"></span>
                </div>

                <div class="control-group">
                    <label for="email">Email Address</label>
                    <input type="email" value="" placeholder="email@address" id="email" name="email" required/>
                    <span class="error" id="email-errorMsg"></span>
                    <span class="error2"></span>
                </div>

                <div class="control-group">
                    <label for="pass">Pick Password</label>
                    <input type="password" value="" placeholder="Password" id="pass" name="pass" pattern=".{6,}"
                           oninput="this.setCustomValidity('')" required/>
                    <i id="icon" onclick="website.hideShowPassword('pass');" class="fa fa-xi fa-eye"></i>
                    <span class="error" id="pass-errorMsg"></span>
                </div>

                <div class="control-group">
                    <label for="CaptchaCode">Answer a random question:
                    </label>
                    <?php
                    $url = 'http://api.textcaptcha.com/example.json';
                    $captcha = json_decode(file_get_contents($url),true);
                    error_log (print_r ($captcha, true));

                    if (!$captcha) {
                        $captcha = array( // fallback challenge
                            'q'=>'Is ice hot or cold?',
                            'a'=>array(md5('cold'))
                        );
                        // + capture error info & log
                    }

                    // display question to user as part of form
                    echo htmlentities($captcha['q']);

                    // store answers in session
                    $_SESSION['captcha_ans'] = $captcha['a'];
                    ?>
                 <input name="CaptchaCode" id="CaptchaCode" type="text" />
                </div>


                <input name="Submit" value="Create My Xi Account" type="submit" class="btn btn-1a"/>

            </form>

            <div id="spinBox"></div>

            <div id="msgModal" role="dialog" class="modal" style="display: none;">
                <div id="msg"></div>
                <button id="modal-close" class="btn-2" type="button">OK</button>
            </div>

        </div>

    </div> <!-- CONTAINER -->
</div> <!-- MAIN -->

<script type="text/javascript">
    // function hideShowPassword(e) {
    //     const x = document.getElementById("pass");
    //     const icon = document.getElementById("icon");
    //     if (x.type === "password") {
    //         x.type = "text";
    //         icon.className = "fa fa-xi fa-eye-slash";
    //     } else {
    //         x.type = "password";
    //         icon.className = "fa fa-xi fa-eye";
    //     }
    // }
    website.createAccountForm();
</script>
</body>

</html>
