<!DOCTYPE HTML>
<html lang="en">

    <head>
        <?php
        $pageName = "Sign In";
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
                <h1 class="page-header"><?php echo($pageName); ?></h1>
                <div class="login">

                    <form id="login_form" name="login_form" method="post" action="../userGUI/php/checkLogin.php" class="login-form">
                        <div class="control-group">
                            <label for="login-name">Username</label>
                            <input type="text" value="" placeholder="Username" id="login-name" name="login-name" pattern="^[a-zA-Z0-9-_.]{4,16}" oninput="this.setCustomValidity('')" required autofocus/>
                            <span class="error" id="login-name-errorMsg"></span>
                            <span class="error2"></span>
                        </div>

                        <div class="control-group">
                            <label for="login-pass">Password</label>
                            <input type="password" value="" placeholder="Password" id="login-pass" name="login-pass" required/>
                            <span class="error2"></span>
                        </div>

                        <input name="Submit" value="Login To Xi" type="submit" class="btn btn-1a"/>
                    </form>

                    <hr class="wideDivider">
                    <h3>Forgotten Password?</h3>
                    <form id="reset_password_form" name="reset_password_form" action="../userGUI/userRequestPassword.html">
                        <input name="Submit" value="Reset Password" type="submit" class="btn btn-1a"/>
                    </form>

                    <div class="newUserSection">
                        <hr class="wideDivider">
                        <h3>New User?</h3>
                        <form id="new_user_form" name="new_user_form" action="./createAccount.php">
                            <input name="Submit" value="Create New Account" type="submit" class="btn btn-1a"/>
                        </form>
                    </div>

                    <script type="text/javascript">
                        $(document).ready(function(e) {
                            $.when (
                                $.getJSON("../userGUI/json/config.json"),
                                $.getJSON("../userGUI/json/msgs.json")
                            ).done (function (configxhr, msgsxhr) {
                                var msgs = msgsxhr[0];
                                var config = configxhr[0];
                                CLMSUI.loginForms.msgs = msgs;
                                // CLMSUI.loginForms.makeFooter();
                                // CLMSUI.loginForms.makeHelpButton();

                                // function checkConnection (url) {
                                //     $.ajax({
                                //         url: url,
                                //         cache: false,
                                //         timeout:1000,
                                //         error: function (jqXHR, textStatus) {
                                //             //alert("Request failed: " + textStatus );
                                //         },
                                //         success: function () {
                                $(".newUserSection").css("display", "block");
                                //        }
                                //    });
                                // }
                                // checkConnection("./createAccount.php"); // show new user section if reg page is reachable

                                var nameValidationMsg = CLMSUI.loginForms.getMsg("clientNameValidationInfo");
                                $("#login-name").attr("oninvalid", "this.setCustomValidity('"+nameValidationMsg+"')");
                                $("#login-name-errorMsg").text("< "+nameValidationMsg);

                                // divert form submit action to this javascript function
                                $("#login_form").submit (function(e) {
                                    $(".error2").css("display", "none");
                                    CLMSUI.loginForms.ajaxPost (e.target);
                                    e.preventDefault();
                                });
                            });
                        });
                    </script>
                </div>

                <div class="newUserSection">
                    <hr class="wideDivider">
                    <h3>Citation:</h3>
                    <a target="_blank" href="http://biorxiv.org/cgi/content/short/561829v1">
                        Graham, M., Combe, C. W., Kolbowski, L. &amp; Rappsilber, J. xiView: A common platform for the downstream analysis of
                        Crosslinking Mass Spectrometry data. <i>doi: 10.1101/561829 </i>.
                    </a>
                </div>
            </div>
            <!-- CONTAINER -->
        </div>
        <!-- MAIN -->

    </body>

</html>
