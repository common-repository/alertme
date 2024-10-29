function isEmail(email) {
  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return regex.test(email);
}

jQuery(document).ready(function($) {

	function triggerLoadingOn() {
	    jQuery('body').addClass('checkout-loading');
	}

	function triggerLoadingOff() {
	    jQuery('body').removeClass('checkout-loading');
	}


	$('#alert_me_submit').click(function (e) {
		e.preventDefault();

		jQuery('#alert_me_email').removeClass('alert_me_email_subscribe_error');
		$('.alert-me-error-box').remove();

		var alert_me_email = $('#alert_me_email').val();
		var alert_me_post_id = jQuery('#alert_me_post_id').val();

		if (isEmail(alert_me_email)) {

            $.ajax({
              type: 'POST',
              url: ajaxurl+"?action=alert_me_subscription",
              //data: {email: alert_me_email, post_id: alert_me_post_id},
              data: $('#alert_me_form').serialize(),
              dataType: "text",
              beforeSend: function() {
                triggerLoadingOn();
              },
              complete: function() {
                triggerLoadingOff();
              },
              success: function(response) {
              		var response_final = JSON.parse(response);
              		console.log(response_final);
              		console.log('ww='+response_final);

              		if (response_final.error) {
						$( "<div class='alert-me-error-box' style='display: flex;'></div>").insertAfter( $( "#alert_me_email" ) );

						$('.alert-me-error-box').fadeOut(800, function() {
							$('.alert-me-error-box').html('<span>'+response_final.error+'</span>').fadeIn(1500);
						});              			
              		} else if (response_final.success) {
              			jQuery('#alert_me_email').val('');
              			//$('.alertme_email').html('');

						$('.alertme_email').fadeOut(800, function() {
							$('.alertme_email').html(response_final.success).fadeIn(800);
						});
              			
              		}

                }
            });

		} else {
			jQuery('#alert_me_email').addClass('alert_me_email_subscribe_error');
		}

	});
});