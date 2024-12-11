(function($){

	var data = {
		action: 'every_stats_update_count',
		post_id: ajax.post_id
	};

	$.post( ajax.ajaxurl, data, function(response) {} );

})(jQuery);