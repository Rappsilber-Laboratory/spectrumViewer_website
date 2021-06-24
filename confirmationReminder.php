<!DOCTYPE HTML>
<html lang="en">

    <head>
        <?php
        $pageName = "Email confirmation reminder";
        include("head.php");
        ?>

            <link rel="stylesheet" href="../vendor/css/example.wink.css" />
            <link rel="stylesheet" href="../vendor/css/common.css" />
            <link rel="stylesheet" href="../userGUI/css/login.css" />

            <script src="../vendor/js/jquery-3.4.1.js"></script>
            <script src="../vendor/js/hideShowPassword.js"></script>
            <script src="../vendor/js/spin.js"></script>
            <script src="../userGUI/js/forms.js"></script>

    </head>

    <body>
        <!-- Sidebar -->
        <?php include("navigation.php");?>

        <!-- Main -->
        <div id="main">
            <div class="container">
                <h1 class="page-header">Email confirmation required</h1>
                <ul>
                    <li>An email was sent to the address you used to register for xiVIEW.</li>
                    <li>You must click the link contained in that email before you can sign
                        in.</li>
                    <li>By doing so, you are giving us permission to store your data in accordance
                        with our <a href="./privacy.php"> Privacy Statment</a>.</li>
                    <li>The privacy statement is also contained in the email you received.</li>
                </ul>
                <br/>

                <div class="login">

                    <form id="register_form" name="register_form" method="post" action="../userGUI/php/resendConfirmationEmail.php"
                        class="login-form">

                        <div class="control-group">
                            <label for="email">Email Address</label>
                            <input type="email" value="" placeholder="email@address" id="email" name="email" required/>
                            <span class="error" id="email-errorMsg"></span>
                            <span class="error2"></span>
                        </div>

                        <div id="recaptchaWidget" data-sitekey="getFromConfig.json"></div>
                        <span class="error">&lt; Please check the captcha form</span>
                        <br/>

                        <input name="Submit" value="Resend confirmation email" type="submit" class="btn btn-1a"
                        />

                    </form>

                    <div id="spinBox"></div>
                    <div id="msg"></div>

                    <script type="text/javascript">
                        //$(document).ready(function(e) {
                        var onloadCallback = function()
                        {
                            $.when(
                                $.getJSON("../userGUI/json/config.json"),
                                $.getJSON("../userGUI/json/msgs.json")
                            ).done(function(configxhr, msgsxhr)
                            {

                                var config = configxhr[0];
                                var msgs = msgsxhr[0];
                                CLMSUI.loginForms.msgs = msgs;
                                CLMSUI.loginForms.makeFooter();
                                CLMSUI.loginForms.makeHelpButton();
                                // CLMSUI.loginForms.finaliseRecaptcha(
                                //     config.googleRecaptchaPublicKey);
                                var spinner = CLMSUI.loginForms.getSpinner();

                                var splitRegex = config.emailRegex.split(
                                    "/");
                                $("#email").attr("pattern", splitRegex[1]);

                                // var nameValidationMsg = CLMSUI.loginForms.getMsg("clientNameValidationInfo");
                                // $("#username").attr("oninvalid", "this.setCustomValidity('"+nameValidationMsg+"')");
                                // $("#username-errorMsg").text("< "+nameValidationMsg);
                                //
                                // var passwordValidationMsg = CLMSUI.loginForms.getMsg("clientPasswordValidationInfo");
                                // $("#pass").attr("oninvalid", "this.setCustomValidity('"+passwordValidationMsg+"')");
                                // $("#pass-errorMsg").text("< "+passwordValidationMsg);

                                var emailValidationMsg = CLMSUI.loginForms
                                    .getMsg("clientEmailValidationInfo");
                                $("#email-errorMsg").text("< " +
                                    emailValidationMsg);

                                // Example 3:
                                // - When checkbox changes, toggle password
                                //   visibility based on its 'checked' property
                                $('#show-password').change(function()
                                {
                                    $('#pass').hideShowPassword(
                                        $(this).prop(
                                            'checked'));
                                });


                                // divert form submit action to this javascript function
                                $("#register_form").submit(function(e)
                                {
                                    $(".error2").css("display",
                                        "none");
                                    spinner.spin(document.getElementById(
                                        "spinBox"));
                                    CLMSUI.loginForms.ajaxPost(e
                                        .target,
                                        {
                                            // "g-recaptcha-response": grecaptcha
                                            //     .getResponse()
                                        },
                                        function()
                                        {
                                            spinner.stop();
                                        });
                                    e.preventDefault();
                                });
                            });
                        }
                        //});

                    </script>

<!--                    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"-->
<!--                        async defer></script>-->
                </div>

            </div>
            <!-- CONTAINER -->
        </div>
        <!-- MAIN -->

    </body>

</html>
