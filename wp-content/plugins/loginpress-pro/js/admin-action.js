jQuery(document).ready(function($) {

  $("#loginpressActiveFree").on( 'click', function(e) {
    e.preventDefault();

    $("#loginpressActiveFree").hide();
    $("#loginpressActivatingFree").show();

    var path = 'loginpress/loginpress.php';

    $.ajax({
      url: ajaxurl,
      type: 'post',
      data:{
        path : path,
        action: 'loginpress_activate_free',
      },
      success: function(response) {

        setTimeout(function(){
          $("#loginpressActivatingFree").hide();

          $("#loginpressActivatedFree").fadeIn( 1000, function() {
            $('.circle-loader').addClass('load-complete');
            $('.checkmark').show();
          });;

        }, 2000);

        location.reload();

      },
      error: function(xhr, textStatus, errorThrown) {
        console.log('Ajax Not Working');
      }
    }); // end ajax.

  });

  $('#loginpressInstallFree').on('click', function(e) {

    e.preventDefault();
    var el = $(this);

    $("#loginpressInstallFree").hide();
    $("#loginpressInstallingFree").show();

    var _wpnonce = $('input[name="loginpress_free_nonce"]').val();
    var slug = 'loginpress';
    $.ajax({
      url : ajaxurl,
      type : 'post',
      data:{
        slug : slug,
        _wpnonce : _wpnonce,
        action: 'install-plugin',
      },
      success : function( res ) {
        if ( res.success ) {
          setTimeout(function(){
            $("#loginpressInstallingFree").hide();

            $("#loginpressActivatedFree").fadeIn( 1000, function() {
              $('.circle-loader').addClass('load-complete');
              $('.checkmark').show();
            });

          }, 2000);

          window.location = res.data.activateUrl;
        } else {
          alert( res.data.errorMessage );
        }

      },
      error: function(xhr, textStatus, errorThrown) {
        console.log('Ajax Not Working');
      }
    });
  });

});
