(function($){

	$(function(){

		$( document ).on( 'click', '.oc_calendar_header a', function(e) {
			if (!e) {
				e = window.event;
			}
			e.preventDefault();

			var url		= $(this).attr( 'href' );
			var date	= $.getUrlParam( url, 'date' );

			$.ajax({
				url: ajax.url,
				data: {
					'action': 'cal_widget',
					'date': date
				},
				success: function(response) {
					var cal_div = ('.calender_div');
					$(cal_div).replaceWith(response);
					$(cal_div).show();
				},
				error: function(error){
					console.log(error);
				}
			});

			return false;
		});
	});

	$.getUrlParam = function( url, name ) {
		var results = new RegExp('[\\?&amp;]' + name + '=([^&amp;#]*)').exec( url );
		if( results !== null ) {
			return results[1];
		}

		return 0;
	};

})(jQuery);