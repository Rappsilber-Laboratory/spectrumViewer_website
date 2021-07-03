import * as $ from 'jquery';
import {uploadForm} from "./upload";
import {ajaxPost, getMsg, getSpinner, makeFooter, makeHelpButton} from "./forms";
import {Spinner} from "spin.js";

require('cookieconsent');
require('./jquery.easyModal.js');

export function cookieconsent() {
    window.addEventListener("load", function () {
        window.cookieconsent.initialise({
            "palette": {
                "popup": {
                    "background": "#333333",
                    "text": "#ffffff"
                },
                "button": {
                    "background": "#091D42",
                    "text": "#ffffff"
                }
            },
            "theme": "classic",
            "position": "bottom-right",
            "content": {
                "href": "cookiepolicy.php" // todo update this doc,
            }
        })
    });
}

export function accordian() {
    $('.accordionHead').click(function () {
        if ($(this).next('.accordionContent').is(":visible")) {
            $(this).find(".fa-minus-square").removeClass("fa-minus-square").addClass("fa-plus-square");
        } else {
            $(this).find(".fa-plus-square").removeClass("fa-plus-square").addClass("fa-minus-square");
        }
        $(this).next('.accordionContent').slideToggle();
    });
}

export function hideShowPassword(elementId){
    const x = document.getElementById(elementId);
    const icon = document.getElementById("icon");
    if (x.type === "password") {
        x.type = "text";
        icon.className = "fa fa-xi fa-eye-slash";
    } else {
        x.type = "password";
        icon.className = "fa fa-xi fa-eye";
    }
}

export function createAccountForm() {
    $(document).ready(function (e) {
        $("#msgModal").easyModal();

        $('#modal-close').click(function (e) {
            $('#msgModal').trigger('closeModal');
        });

        const nameValidationMsg = getMsg("clientNameValidationInfo");
        $("#username").attr("oninvalid", "this.setCustomValidity('" + nameValidationMsg + "')");
        $("#username-errorMsg").text("< " + nameValidationMsg);

        const passwordValidationMsg = getMsg("clientPasswordValidationInfo");
        $("#pass").attr("oninvalid", "this.setCustomValidity('" + passwordValidationMsg + "')");
        $("#pass-errorMsg").text("< " + passwordValidationMsg);

        const emailValidationMsg = getMsg("clientEmailValidationInfo");
        $("#email-errorMsg").text("< " + emailValidationMsg);

        const spinner = new Spinner();//getSpinner();
        // divert form submit action to this javascript function
        $("#register_form").submit(function (e) {
            $(".error2").css("display", "none");
            spinner.spin(document.getElementById("spinBox"));
            ajaxPost(e.target, {}, function () {
                spinner.stop();
            });
            e.preventDefault();
        });
    });
}

export function gdprAcceptance() {
    $(document).ready(function () {
        // $.getJSON("./json/msgs.json", function (msgs) {
        //     window.msgs = msgs;
        //     makeFooter();
        //     makeHelpButton();

            // const passwordValidationMsg = getMsg("clientPasswordValidationInfo");
            // $("#new-login-pass").attr("oninvalid", "this.setCustomValidity('" + passwordValidationMsg + "')");
            // $("#pass-errorMsg").text("< " + passwordValidationMsg);

            // https://css-tricks.com/snippets/javascript/get-url-variables/
            function getQueryVariable(variable) {
                const query = window.location.search.substring(1);
                const vars = query.split("&");
                for (let i = 0; i < vars.length; i++) {
                    const pair = vars[i].split("=");
                    if (pair[0] == variable) {
                        return pair[1];
                    }
                }
                return false;
            }

            simpleAjaxPost(
                {method: "POST", url: "php/GDPRacceptance.php"},
                {token: getQueryVariable("gdpr_token")},
                function () {
                    $("#msg").html(getMsg("GDPRacceptance"));
                }
            )
        // });
    });
}

export function confirmationReminder() {
    $(document).ready(function(e) {

            const spinner = getSpinner();

            const splitRegex = "/.*@.*/".split(
                "/");
            $("#email").attr("pattern", splitRegex[1]);

            const emailValidationMsg = getMsg("clientEmailValidationInfo");
            $("#email-errorMsg").text("< " +
                emailValidationMsg);

            // divert form submit action to this javascript function
            $("#register_form").submit(function (e) {
                $(".error2").css("display",
                    "none");
                spinner.spin(document.getElementById(
                    "spinBox"));
                ajaxPost(e
                        .target,
                    {
                        // "g-recaptcha-response": grecaptcha
                        //     .getResponse()
                    },
                    function () {
                        spinner.stop();
                    });
                e.preventDefault();
            });
        });
}

export function userRequestPassword() {
    $(document).ready(function(e) {
    // const onloadCallback = function () {
    //
    //     $.when(
    //         $.getJSON("./json/config.json"),
    //         $.getJSON("./json/msgs.json")
    //     ).done(function (configxhr, msgsxhr) {
    //         const config = configxhr[0];
    //         const msgs = msgsxhr[0];
    //         window.msgs = msgs;
    //         makeFooter();
    //         makeHelpButton();
    //         // finaliseRecaptcha (config.googleRecaptchaPublicKey);
            const spinner = getSpinner();

            // divert form submit action to this javascript function
            $("#password_retrieve_form").submit(function (e) {
                $(".error2").css("display", "none");
                spinner.spin(document.getElementById("spinBox"));
                ajaxPost(e.target, {}, function () {
                    spinner.stop();
                });
                e.preventDefault();
            });
        });
    // };
    // });
}

export function pwreset() {
$(document).ready (function() {
        var passwordValidationMsg = getMsg("clientPasswordValidationInfo");
        $("#new-login-pass").attr("oninvalid", "this.setCustomValidity('"+passwordValidationMsg+"')");
        $("#pass-errorMsg").text("< "+passwordValidationMsg);

        // https://css-tricks.com/snippets/javascript/get-url-variables/
        function getQueryVariable (variable)  {
           var query = window.location.search.substring(1);
           var vars = query.split("&");
           for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                if(pair[0] == variable){return pair[1];}
           }
           return false;
        }

        $("#password_reset_form").submit (function(e) {
            $(".error").css("display", "none");
            ajaxPost (e.target, {token: getQueryVariable("ptoken")});
            e.preventDefault();
        });

});
}

export function login() {
    $(document).ready(function (e) {
        // $.when(
        //     $.getJSON("../userGUI/json/config.json"),
        //     $.getJSON("../userGUI/json/msgs.json")
        // ).done(function (configxhr, msgsxhr) {
        //     const msgs = msgsxhr[0];
        //     const config = configxhr[0];
        //     window.msgs = msgs;
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

            const nameValidationMsg = getMsg("clientNameValidationInfo");
            $("#login-name").attr("oninvalid", "this.setCustomValidity('" + nameValidationMsg + "')");
            $("#login-name-errorMsg").text("< " + nameValidationMsg);

            // divert form submit action to this javascript function
            $("#login_form").submit(function (e) {
                $(".error2").css("display", "none");
                ajaxPost(e.target);
                e.preventDefault();
            });
        // });
    });
}

// export function upload() {
//     uploadForm();
// }