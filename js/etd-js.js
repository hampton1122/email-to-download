jQuery(document).ready(function () {


    jQuery('.download').click(function () {

        jQuery.curCSS = function (element, prop, val) {
            return jQuery(element).css(prop, val);
        };

        var html = '<div id="dialog" title="Download your free ebook" style="display:none"><p>To download, enter your name and email and we will email your free eBook: Five things financial institutions do to confuse their clients, that that can cost you money.</p> <form id="modalform"><input type="text" name="first_name" placeholder="First Name"> <input type="text" name="last_name" placeholder="Last Name"> <input type="text" name="email" placeholder="email"><div id="msg" style="display:none;">An email has been sent.</div></form>';

        jQuery(html).appendTo(document.body);

        jQuery("#dialog").dialog({
            height: 400,
            width: 450,
            modal: true,
            buttons: [{
                    text: "Cancel",
                    click: function () {
                        jQuery(this).dialog("close");
                    }
                },
                {
                    text: "Download",
                    click: function () {
                        var that = this;
                        jQuery.post(
                            ajaxurl, {
                                'action': 'saveEmail',
                                'data': jQuery('#modalform').serialize()
                            },
                            function (response) {
                                var res = JSON.parse(response);
                                if (res.status == "success") {
                                    jQuery("#msg").show();
                                    setTimeout(function () {
                                        jQuery("#dialog").dialog("close");
                                        jQuery("#dialog").remove();
                                    }, 3000);
                                }

                            }
                        );
                    }
                }
            ]
        });
    });
});