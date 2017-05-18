jQuery(document).ready(function () {

    //edit the year in the footer
    var d = new Date();
    var year = d.getFullYear();
    jQuery(".mk-footer-copyright").html("Copyright All Rights Reserved ©" + new Date().getFullYear());

    jQuery('.download').click(function () {

        var html = '<form id="modalform" style="display:none"><input type="text" name="first_name" placeholder="First Name"> <input type="text" name="last_name" placeholder="Last Name"> <input type="text" name="email" placeholder="email"></form>';
        jQuery("#modalform").dialog({
            height: 250,
            width: 450,
            modal: true,
            buttons: {
                "Cancel": function () {
                    jQuery(this).dialog("close");
                },
                "Save": function () {
                    jQuery.ajax({
                        url: "/url/to/submit",
                        timeout: 30000,
                        type: "POST",
                        data: jQuery('#modalform').serialize(),
                        dataType: 'json',
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            alert("An error has occurred making the request: " + errorThrown)
                        },
                        success: function (data) {
                            //Do stuff here on success such as modal info      
                            jQuery(this).dialog("close");
                        }
                    });
                }
            }
        });
    });
});