jQuery.noConflict();
(function($){
    
$(document).ready(function() {
  $("#submit-form").validate();
});

$(document).ready(function() {
    $('#submit-form-2').validate({

        errorPlacement: function(error,element) {
        	var attr_name = element.attr('name');
        	$('.'+attr_name+'-notice').html(error);
        	if(error){
        		$('#notice').html('Something is wrong, Please try again!').addClass('error');
        	}

        },

        submitHandler: function(form) {
	        var formData = new FormData($("#submit-form-2")[0]);
	        $(form).ajaxSubmit({
	            url:"action.php",
	            type:"post",
	            success: function(data,status){
	              $('#notice').html('Thank you! Your message has been sent!').addClass('success');
	            }
	        });
	    }


    });
    });

})(jQuery)