<!DOCTYPE HTML>
<html lang="en">

<head>
    <title>xiVIEW | Create Account</title>

    <?php
    require("./botdetect.php");
    include("head.php");
    ?>

    <link rel="stylesheet" href="./css/example.wink.css"/>
    <link rel="stylesheet" href="./css/common.css"/>
    <link rel="stylesheet" href="./css/login.css"/>

<!--    <script src="../vendor/js/jquery-3.4.1.js"></script>-->
<!--    <script type="text/javascript" src="./vendor/jquery.easyModal.js"></script>-->
<!--    <script src="../vendor/js/hideShowPassword.js"></script>-->
<!--    <script src="../vendor/js/spin.js"></script>-->
<!--    <script src="../userGUI/js/forms.js"></script>-->
</head>

<body>
<!-- Sidebar -->
<?php include("navigation.php"); ?>

<!-- Main -->
<div id="main">
    <div class="container">
        <h1 class="page-header">Create Account</h1>
        <p><a href="https://player.vimeo.com/video/298348200" target="_blank">Account creation video tutorial</a></p>

        <div class="login">

            <form id="register_form" name="register_form" method="post" action="../userGUI/php/registerNewUser.php"
                  class="login-form">

                <div class="control-group">
                    <label for="email">Pick Username</label>
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
                    <label for="pass1">Pick Password</label>
                    <input type="password" value="" placeholder="Password" id="pass" name="pass" pattern=".{6,}"
                           oninput="this.setCustomValidity('')" required/>
                    <span class="error" id="pass-errorMsg"></span>
                    <input type="checkbox" id="show-password">
                    <label for="show-password">Show password</label>
                </div>

                <!--                <div id="recaptchaWidget" data-sitekey="getFromConfig.json"></div>-->
                <!--                <span class="error">&lt; Please check the captcha form</span>-->
                <!--                <br/>-->

                <?php // Adding BotDetect Captcha to the page
                $ExampleCaptcha = new Captcha("ExampleCaptcha");
                $ExampleCaptcha->UserInputID = "CaptchaCode";
                echo $ExampleCaptcha->Html();
                ?>

                <input name="CaptchaCode" id="CaptchaCode" type="text" />

                <input name="Submit" value="Create My Xi Account" type="submit" class="btn btn-1a"/>

            </form>

            <div id="spinBox"></div>

            <div id="msgModal" role="dialog" class="modal" style="display: none;">
                <div id="msg"></div>
                <button onclick="location.href='./login.php'" class="btn-2" type="button">OK</button>
            </div>

            <script type="text/javascript">
                website.createAccountForm();
                // $("#msgModal").easyModal({
                //     overlayClose: false,
                //     closeOnEscape: false
                // });
                // //$(document).ready(function(e) {
                // var onloadCallback = function () {
                //     $.when(
                //         $.getJSON("../userGUI/json/config.json"),
                //         $.getJSON("../userGUI/json/msgs.json")
                //     ).done(function (configxhr, msgsxhr) {
                //
                //         var config = configxhr[0];
                //         var msgs = msgsxhr[0];
                //         CLMSUI.loginForms.msgs = msgs;
                //         // CLMSUI.loginForms.makeFooter();
                //         // CLMSUI.loginForms.makeHelpButton();
                //         // CLMSUI.loginForms.finaliseRecaptcha (config.googleRecaptchaPublicKey);
                //         var spinner = CLMSUI.loginForms.getSpinner();
                //
                //         var splitRegex = config.emailRegex.split("/");
                //         $("#email").attr("pattern", splitRegex[1]);
                //
                //         var nameValidationMsg = CLMSUI.loginForms.getMsg("clientNameValidationInfo");
                //         $("#username").attr("oninvalid", "this.setCustomValidity('" + nameValidationMsg + "')");
                //         $("#username-errorMsg").text("< " + nameValidationMsg);
                //
                //         var passwordValidationMsg = CLMSUI.loginForms.getMsg("clientPasswordValidationInfo");
                //         $("#pass").attr("oninvalid", "this.setCustomValidity('" + passwordValidationMsg + "')");
                //         $("#pass-errorMsg").text("< " + passwordValidationMsg);
                //
                //         var emailValidationMsg = CLMSUI.loginForms.getMsg("clientEmailValidationInfo");
                //         $("#email-errorMsg").text("< " + emailValidationMsg);
                //
                //         // Example 3:
                //         // - When checkbox changes, toggle password
                //         //   visibility based on its 'checked' property
                //         $('#show-password').change(function () {
                //             $('#pass').hideShowPassword($(this).prop('checked'));
                //         });
                //
                //
                //         // divert form submit action to this javascript function
                //         $("#register_form").submit(function (e) {
                //             $(".error2").css("display", "none");
                //             spinner.spin(document.getElementById("spinBox"));
                //             CLMSUI.loginForms.ajaxPost(e.target, {}, function () {
                //                 spinner.stop();
                //             });
                //             e.preventDefault();
                //         });
                //     });
                // }
                // //});
            </script>

            <!--            <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>-->
        </div>


    </div>
    <!-- CONTAINER -->
</div>
<!-- MAIN -->

</body>

</html>
