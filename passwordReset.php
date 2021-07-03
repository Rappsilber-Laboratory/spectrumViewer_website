<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Xi User Password Reset</title>
      <?php
      include("head.php");
      ?>
  </head>

  <body>

	<header>
        <span class="topRight"><button id="helpButton"></button></span>
        <h1>Xi User Password Reset</h1>
	</header>

    <div class="login">

          <form id="password_reset_form" name="password_reset_form" method="post" action="./php/user/passwordUpdate.php" class="login-form">

            <div class="control-group">
                <label for="new-login-pass">Enter New Password</label>
                <input type="password" value="" placeholder="Password" id="new-login-pass" name="new-login-pass" pattern=".{6,}" oninput="this.setCustomValidity('')" required autofocus/>
                <i id="icon" onclick="website.hideShowPassword('new-login-pass');" class="fa fa-xi fa-eye"></i>
                <span class="error error2" id="pass-errorMsg"></span>

            </div>

            <input name="Submit" value="Set New Password" type="submit" class="btn btn-1a"/>
          </form>

        <p id="msg"></p>
        <a href="./userRequestPassword.php" class="revealOnFailure">Request new password reset email</a>

        <script type="text/javascript">
            website.pwreset();
            // $(document).ready (function() {
            //     $.getJSON("./json/msgs.json", function (msgs) {
            //         window.msgs = msgs;
            //         CLMSUI.loginForms.makeFooter();
            //         CLMSUI.loginForms.makeHelpButton();
            //
            //         var passwordValidationMsg = CLMSUI.loginForms.getMsg("clientPasswordValidationInfo");
            //         $("#new-login-pass").attr("oninvalid", "this.setCustomValidity('"+passwordValidationMsg+"')");
            //         $("#pass-errorMsg").text("< "+passwordValidationMsg);
            //
            //         // https://css-tricks.com/snippets/javascript/get-url-variables/
            //         function getQueryVariable (variable)  {
            //            var query = window.location.search.substring(1);
            //            var vars = query.split("&");
            //            for (var i=0;i<vars.length;i++) {
            //                 var pair = vars[i].split("=");
            //                 if(pair[0] == variable){return pair[1];}
            //            }
            //            return false;
            //         }
            //
            //         // Example 3:
            //         // - When checkbox changes, toggle password
            //         //   visibility based on its 'checked' property
            //         $('#show-password').change(function(){
            //           $('#new-login-pass').hideShowPassword($(this).prop('checked'));
            //         });
            //
            //         $("#password_reset_form").submit (function(e) {
            //             $(".error").css("display", "none");
            //             CLMSUI.loginForms.ajaxPost (e.target, {token: getQueryVariable("ptoken")});
            //             e.preventDefault();
            //         });
            //     });
            // });

        </script>
    </div>

  </body>
</html>
