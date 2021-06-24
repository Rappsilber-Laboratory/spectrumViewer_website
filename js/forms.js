var CLMSUI = (function (mod) {

    mod.loginForms = {

        msgs: [],

        ajaxPost: function (formElement, prepostData, doneFunc) {
            var zform = $(formElement);
            // grab form input field values and put them in a json obj
            var json = prepostData || {};
            json.redirect = document.referrer;
            //console.log ("json", json);
            zform.find("input[type='text'],input[type='email'],input[type='password']").each (function() {
                var elem = $(this);
                json[elem.attr("name")] = elem.prop("value");
            });

            $.ajax ({
                type: zform.attr("method"),
                url: zform.attr("action"),
                data: json,
                dataType: "json",
                encode: true,
                success: function (data, status, xhr) {
                    if (data.status === "success") {
                        if (data.redirect) {    // redirect if login good
                            window.location.assign (data.redirect);
                        } else if (data.msg) {
                            $("#msg").html(data.msg);//addClass("backToBlack");
                            var modal = $("#msgModal");
                            modal.trigger('openModal');
                            var ml = -1 * modal.width() / 2;
                            var mt = -1 * modal.height() / 2;
                            modal.css("margin-left", ml).css("margin-top", mt);// recentre
                        }
                    }
                    else if (data.status !== "success") {    // say which field is wrong if login bad
                        if (data.redirect) {    // redirect if login good
                            window.location.assign (data.redirect);
                        } else if (data.field) {
                             $("#"+data.field).siblings(".error2").css("display", "inline");
                            if (data.msg) {
                                $("#"+data.field).siblings(".error2").text(data.msg);
                            }
                        } else if (data.msg) {
                            $("#msg").text(data.msg);
                            var modal = $("#msgModal");
                            modal.trigger('openModal');
                            var ml = -1 * modal.width() / 2;
                            var mt = -1 * modal.height() / 2;
                            modal.css("margin-left", ml).css("margin-top", mt);// recentre
                        }
                        $(".revealOnFailure").css("display", "block");
                        if (data.revalidate) {
                             grecaptcha.reset();    // captcha needs revalidated on this failure
                        }
                    }
                },
                error: function (jqXhr, textStatus, errorThrown) {
					$("#msg").html("Server side error: "+jqXhr.responseText+".<br>Please contact Xi Administrator.");
                },
                complete: function () {
                    if (doneFunc) {
                        doneFunc ();
                    }
                }
            });
        },

		simpleAjaxPost: function (ajaxSetup, prepostData, doneFunc) {
            // grab form input field values and put them in a json obj
            var json = prepostData || {};
            json.redirect = document.referrer;
            console.log ("json", json);

            $.ajax ({
                type: ajaxSetup.method || "GET",
                url: ajaxSetup.url,
                data: json,
                dataType: "json",
                encode: true,
                success: function (data, status, xhr) {
                    if (doneFunc) {
                        doneFunc ();
                    }
                },
                error: function (jqXhr, textStatus, errorThrown) {
					$("#msg").html("Server side error: "+jqXhr.responseText+".<br>Please contact Xi Administrator.");
                },
                complete: function () {
                }
            });
        },

        getMsg: function (key) {
            var language = window.navigator.userLanguage || window.navigator.language;
            language = language.split("-")[0];
            return (CLMSUI.loginForms.msgs[language] || CLMSUI.loginForms.msgs["en"])[key];
        },

        makeFooter: function () {
            $("body").append("<footer><hr class='wideDivider'><p><a href='' id='xiemail'></a></p><p></p></footer>");
            var ximail = CLMSUI.loginForms.getMsg ("xiAdminEmail");
            $("#xiemail")
                .attr("href", "mailto:"+ximail)
                .text(ximail)
            ;
        },

        finaliseRecaptcha: function (publicKey) {
            $("#recaptchaWidget")
                .attr("class", "g-recaptcha")
                .attr("data-sitekey", publicKey)
            ;
             grecaptcha.render('recaptchaWidget', {'sitekey': publicKey});
        },

        getSpinner: function () {
            return new Spinner ({
                lines: 13, // The number of lines to draw
                length: 5, // The length of each line
                width: 2, // The line thickness
                radius: 7, // The radius of the inner circle
            });
        },

        makeHelpButton: function () {
            $("#helpButton").on("click", function() { window.open (CLMSUI.loginForms.getMsg ("xiHelpURL"), "_blank"); });
            // Make buttons - previously could do immediately, but loading in text from msgs.json means icons need to be added afterwards
            var buttonData = [
                {id: "#helpButton", type: "button", label: this.getMsg ("xiHelp")},
            ];
            buttonData.forEach (function (buttonDatum) {
                var buttonID = buttonDatum.id;
                $(buttonID)
                    .attr("type", buttonDatum.type)
                    .attr("class", "btn")
                    .text (buttonDatum.label)
                ;
            });
        },
    };

    return mod;

}(CLMSUI || {}));
