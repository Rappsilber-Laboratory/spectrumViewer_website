import * as $ from 'jquery';

require('cookieconsent');

export function cookieconsent(){
    // alert("here");

    window.addEventListener("load", function () {
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    "background": "#333333",
                    "text": "#999999"
                },
                "button": {
                    "background": "#750000",
                    // "background": "#091D42",
                    "text": "#ffffff"
                }
            },
            "theme": "classic",
            "position": "bottom-right",
            "content": {
                "href": "cookiepolicy.php"
            }
        })
    });
}

export function accordian(){
    $('.accordionHead').click(function(){
        if($(this).next('.accordionContent').is(":visible")){
            $(this).find(".fa-minus-square").removeClass("fa-minus-square").addClass("fa-plus-square");
        }
        else{
            $(this).find(".fa-plus-square").removeClass("fa-plus-square").addClass("fa-minus-square");
        }
        $(this).next('.accordionContent').slideToggle();
    });
}

export function createAccountForm(){
    // $("#msgModal").easyModal({
    //     overlayClose: false,
    //     closeOnEscape: false
    // });
    //$(document).ready(function(e) {
    var onloadCallback = function () {
        $.when(
            $.getJSON("../userGUI/json/config.json"),
            $.getJSON("../userGUI/json/msgs.json")
        ).done(function (configxhr, msgsxhr) {

            var config = configxhr[0];
            var msgs = msgsxhr[0];
            CLMSUI.loginForms.msgs = msgs;
            // CLMSUI.loginForms.makeFooter();
            // CLMSUI.loginForms.makeHelpButton();
            // CLMSUI.loginForms.finaliseRecaptcha (config.googleRecaptchaPublicKey);
            var spinner = CLMSUI.loginForms.getSpinner();

            var splitRegex = config.emailRegex.split("/");
            $("#email").attr("pattern", splitRegex[1]);

            var nameValidationMsg = CLMSUI.loginForms.getMsg("clientNameValidationInfo");
            $("#username").attr("oninvalid", "this.setCustomValidity('" + nameValidationMsg + "')");
            $("#username-errorMsg").text("< " + nameValidationMsg);

            var passwordValidationMsg = CLMSUI.loginForms.getMsg("clientPasswordValidationInfo");
            $("#pass").attr("oninvalid", "this.setCustomValidity('" + passwordValidationMsg + "')");
            $("#pass-errorMsg").text("< " + passwordValidationMsg);

            var emailValidationMsg = CLMSUI.loginForms.getMsg("clientEmailValidationInfo");
            $("#email-errorMsg").text("< " + emailValidationMsg);

            // Example 3:
            // - When checkbox changes, toggle password
            //   visibility based on its 'checked' property
            $('#show-password').change(function () {
                $('#pass').hideShowPassword($(this).prop('checked'));
            });


            // divert form submit action to this javascript function
            $("#register_form").submit(function (e) {
                $(".error2").css("display", "none");
                spinner.spin(document.getElementById("spinBox"));
                CLMSUI.loginForms.ajaxPost(e.target, {}, function () {
                    spinner.stop();
                });
                e.preventDefault();
            });
        });
    }
    //});
}