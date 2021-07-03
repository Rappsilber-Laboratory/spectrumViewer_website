<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Xi Password Reset via Email</title>
      <?php
        session_start();
        include("head.php");
        ?>
  </head>

  <body>

	<header>
        <span class="topRight"><button id="helpButton"></button></span>
        <h1>Xi Password Reset via EMail</h1>
	</header>

    <div class="login">

        <form id="password_retrieve_form" name="password_retrieve_form" method="post" action="./php/user/passwordRetrieve.php" class="login-form">
            <div class="control-group">
              <label for="password-retrieve">Username or Email</label>
              <input type="text" value="" placeholder="Username or email" id="password-retrieve" name="password-retrieve" pattern=".{4,}" required autofocus/>
              <span class="error2"></span>
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
            <span class="error2">&lt; Please check the captcha form</span>
            <br/>

            <input name="Submit" value="Get Email with reset password link" type="submit" class="btn btn-1a"/>
        </form>

        <div id="spinBox"></div>
        <p id="msg"></p>

        <!--
            The script that retrieves the recaptcha key is a callback that is called by the recaptcha api script.
            Otherwise 1) grecaptcha isn't instantiated or
            2) calling recaptcha in head often means it loads before the captcha element is instantiated, so no captcha is made leading to error on submission
        -->
        <script type="text/javascript">
            website.userRequestPassword();
            // //$(document).ready(function(e) {
            // var onloadCallback = function () {
            //
            //     $.when (
            //         $.getJSON("./json/config.json"),
            //         $.getJSON("./json/msgs.json")
            //     ).done (function (configxhr, msgsxhr) {
            //         var config = configxhr[0];
            //         var msgs = msgsxhr[0];
            //         window.msgs = msgs;
            //         CLMSUI.loginForms.makeFooter();
            //         CLMSUI.loginForms.makeHelpButton();
            //         CLMSUI.loginForms.finaliseRecaptcha (config.googleRecaptchaPublicKey);
            //         var spinner = CLMSUI.loginForms.getSpinner();
            //
            //         // divert form submit action to this javascript function
            //         $("#password_retrieve_form").submit (function(e) {
            //             $(".error2").css("display", "none");
            //             spinner.spin (document.getElementById ("spinBox"));
            //             CLMSUI.loginForms.ajaxPost (e.target, {"g-recaptcha-response": grecaptcha.getResponse()}, function() { spinner.stop(); });
            //             e.preventDefault();
            //         });
            //     });
            // }
            // //});
        </script>
<!--         <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>-->
    </div>

  </body>
</html>
