(function($){

	$(function(){
		var feedback = $('#setting-error-settings_updated');

		if( feedback ){
			feedback.delay(1500).fadeOut(2500, "linear");
		}

		$('.oc_prop_form').delegate('select', 'change', function(){
			$('.unsaved').fadeIn(500, "linear");
		});
	});

})(jQuery);