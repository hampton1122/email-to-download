jQuery(document).ready(function () {


    jQuery('.download').click(function () {

        jQuery.curCSS = function(element, prop, val) {
            return jQuery(element).css(prop, val);
        };

        var html = '<div id="dialog" title="Download your free ebook" style="display:none"><p>To download, enter your name and email and we will email your free eBook: Five things financial institutions do to confuse their clients, that that can cost you money.</p> <form id="modalform"><input type="hidden" value="save_email"><input type="text" name="first_name" placeholder="First Name"> <input type="text" name="last_name" placeholder="Last Name"> <input type="text" name="email" placeholder="email"></form>';
       
        jQuery(html).appendTo(document.body);       

        jQuery("#dialog").dialog({
            height: 400,
            width: 450,
            modal: true,
            buttons: [{
                text: "Cancel",
                click: function () {
                    jQuery(this).dialog("close");
                }},
                {
                text: "Download",
                click: function () {
                    var that = this;
                    jQuery.ajax({
                        url: ajaxurl,
                        timeout: 30000,
                        type: "POST",
                        data: jQuery('#modalform').serialize(),
                        dataType: 'json',
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert("An error has occurred making the request: " + errorThrown)
                        },
                        success: function (response) {
                            //Do stuff here on success such as modal info      
                            jQuery("#dialog").dialog("close");
                        }
                    });
                }
            }]
        });
    });
});