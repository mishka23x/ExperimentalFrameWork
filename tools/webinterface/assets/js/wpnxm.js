/**
 * Browser Update Notifier - async (http://browser-update.org/update.js)
 */
var $bu = {};
$bu.ol = window.onload;
window.onload = function() {
    try {
        if ($bu.ol) $bu.ol();
    }catch (e) {}
    var e = document.createElement("script");
    e.setAttribute("type", "text/javascript");
    e.setAttribute("src", "/tools/webinterface/assets/js/browser-update.js");
    document.body.appendChild(e);

    setCurrentPageActiveInMainMenu();
    enableTooltips();
    handlePHPExtensionsForm();

    onModalHideResetRemoteUrl();

    lazyBindModalSubmitActionToFormUrl();
}

/**
 * Allow Modal Content to be fetched from the new remote URL:
 * by destroying the old modal and removing its remote URL.
 */
function onModalHideResetRemoteUrl() {
    $('body').on('hidden.bs.modal', '.modal', function () {
      $(this).removeData('bs.modal');
    });
}

/**
 * override native alert() function with a call to bootstrap3 modal
 */
/*
window.alert = function () {
  var msg = arguments[0];
  var alert = '<p class="error">' + msg.replace(/\n/g, "<br />") + '</p>';
  $('#myModal .modal-title').html('ALERT');
  $('#myModal .modal-body').html(alert);
  $('#myModal button[type="submit"]').hide();
  $("#myModal").modal('show');
};*/

/**
 * Highlights the current page in the headline main menu
 */
function setCurrentPageActiveInMainMenu() {
    var location = window.location.href.toString().split(window.location.host)[1];
    $(".main_menu li a").removeClass("active");
    $(".main_menu li a[href='" + location  + "']").addClass("active");
    // special cases
    if(location.indexOf("update") >= 0) {
       $("a.dropdown-toggle:contains('Tools')").addClass("active");
    }
}

/**
 * Enable Twitter Bootstrap Tooltips
 * Usage:
 * <a href="#" rel="tooltip" title="tooltip text!">
 */
function enableTooltips() {
    $('[rel=tooltip]').tooltip();
}

jQuery.fn.extend({
    // Pulsate
    // Usage: http://jsfiddle.net/nick_craver/HHWBv/
    pulsate: function() {
        var obj = $(this);
        // 3 times
        for(var i=0; i<2; i++) {
          obj.animate({opacity: 0.2}, 1000, 'linear')
           .animate({opacity: 1}, 1000, 'linear');
        }
        // 1 more with hide
        obj.animate({opacity: 0.2}, 1000, 'linear')
           .animate({opacity: 1}, 1000, 'linear', function() {
               obj.hide();
        });
    return obj;
    }
});

// wait for the DOM to be loaded
function handlePHPExtensionsForm() {
    var options = {
        delegation:    true,  // the form we are binding, does not exist, yet (load via ajax)
        target:        '#ajax-status',   // target element(s) to be updated with server response
        beforeSubmit:  showRequest,  // pre-submit callback
        success:       showResponse,  // post-submit callback

        // other available options:
        //url:       url         // override for form's 'action' attribute
        //type:      type        // 'get' or 'post', override for form's 'method' attribute
        dataType:    'json'      // 'xml', 'script', or 'json' (expected server response type)
        //clearForm: true        // clear all form fields after successful submit
        //resetForm: true        // reset the form after successful submit

        // $.ajax options can be used here too, for example:
        //timeout:   3000
    };

    // delegation binding of the form using 'ajaxForm'
    $('#phpExtensionsForm').ajaxForm(options);

    // pre-submit callback
    function showRequest(formData, jqForm, options) {
        // formData is an array; here we use $.param to convert it to a string to display it
        // but the form plugin does this for you automatically when it submits the data
        var queryString = $.param(formData);

        // jqForm is a jQuery object encapsulating the form element.  To access the
        // DOM element for the form do this:
        // var formElement = jqForm[0];

        /*alert('About to submit: \n\n' + queryString);*/

        // here we could return false to prevent the form from being submitted;
        // returning anything other than false will allow the form submit to continue
        // return false;

        return true;
    }

    // post-submit callback
    function showResponse(responseText, statusText, xhr, $form)  {
        // for normal html responses, the first argument to the success callback
        // is the XMLHttpRequest object's responseText property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'xml' then the first argument to the success callback
        // is the XMLHttpRequest object's responseXML property

        // if the ajaxForm method was passed an Options Object with the dataType
        // property set to 'json' then the first argument to the success callback
        // is the json data object returned by the server

        // how to update page using json data
        // ==================================
        // * iterate over each property/value combination in the JSON object
        // * look for an element in the DOM that matches the property name
        //   o first, look for an element with a matching id attribute
        //   o if no element with a matching ID is found, look for input,
        //     select or textarea elements with a matching name attribute
        // * update the value, contents or selection of the matched element(s) based on the value in the JSON object

        var jsonData = responseText;

        // after a change to php ini, we are indicating the change and the PHP daemon restart
        // by showing the ajax status
        $('#ajax-status').html(jsonData.responseText).removeClass('hide').show().pulsate();

        // restart the php daemon
        $.get("index.php?page=daemon&action=restart&daemon=php");

        // there might be a change in the on/off state of extensions.
        // fetch their state as HTML checkbox display
        var updatePHPExtensionsForm = function() {
            $.ajax({ url: "index.php?page=config&action=renderPHPExtensionsFormContent",
                success: function(data, textStatus, XMLHttpRequest) {
                    $("#phpExtensionsFormContent").html(data);
                    $('#ajax-status').hide().addClass('hide');
            }});
        }

        // delay this call, because the php daemon needs to startup again
        setTimeout(updatePHPExtensionsForm, 2200);
    }
}; // END of handlePHPExtensionsForm()

function ajaxGET(url, success) {
    $.ajax({
        url: url,
        method: "GET",
        success: success
    });
}

function lazyBindModalSubmitActionToFormUrl() {
  // bind submit action
  $('#myModal').on("click", 'input[type="submit"], button[type="submit"]', function (event) {
      var form = $("#myModal .modal-body form");

      $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: form.serializeArray(),
        cache: false
      })
      .done(function(data, textStatus, jqXHR) {
        $('#myModal .modal-body').html(data);
        //$('#myModal .modal-body').html("success");
      })
      .fail(function() {
        $('#myModal .modal-body').html("error");
      });

      // cancel the default action (submit)
      event.preventDefault();
  });

}
