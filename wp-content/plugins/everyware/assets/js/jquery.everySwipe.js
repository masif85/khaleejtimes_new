/**
 * jQuery Plugin to detect Swipe on touch units
 * 
 * @author Joacim Ståhl for InfoMaker AB (http://www.infomaker.se/)
 * @copyright (c) InfoMaker AB
 * @version 1.0
 */

// TODO: Unbind an elements swipe when already swiped

(function($) {
	
	$.fn.everySwipe = function(user_config) {
		var config = {
	    	min_swipe: 50,
	    	max_diff: 30,
	 		swipe_left: function() { },
	 		swipe_right: function() { },
	 		cursor_swipe: false
		};
	     
	    if (user_config) $.extend(config, user_config);
	    $this = this;
	    
	    var mouse_down = false;
	    
	    // Loop all the elements
	    this.each(function(index) {
	    	var start_x = start_y = 0;
	    	var start_scroll = $(window).scrollTop();
	    	var touching = false;
	    	
	    	var current_x = 0;
	    	var current_y = 0;
	    	var dx = 0;
	    	var dy = 0;


	    	
	    	function touch_move(e) {
	    		var touch = (config.cursor_swipe && !('ontouchstart' in document.documentElement)) ? e : (e.originalEvent.touches[0] || e.originalEvent.changedTouches[0]);
	    		
	    		current_x = touch.pageX;
	    		current_y = touch.pageY;
	    		dx = start_x - current_x;
	    		dy = start_y - current_y;
	    		
	    		if(Math.abs(dx) > 20) {
	    			e.preventDefault();
                    $($this.selector).trigger('swiping', {x: dx, y: dy});
	    		}
	    		
	    		if( (Math.abs(dx) >= config.min_swipe) && (Math.abs(dy) <= config.max_diff) ) {
	    			e.preventDefault();
	    		};
	    		
	    	};
	    	
	    	function touch_stop(elm) {
				var current_scroll = $(window).scrollTop();
				touching = false;
				
				if(current_scroll !== start_scroll){ return; }
				
				// Make sure the touch pattern matches the config
	    		if( (Math.abs(dx) >= config.min_swipe) && (Math.abs(dy) <= config.max_diff) ) {
	    			if(dx > 0) {
	    				config.swipe_left();
	    				$($this.selector).trigger('swipe_left');
	    				$($this.selector).trigger('swipe', {direction: "left"});
	    			} else {
	    				config.swipe_right();
	    				$($this.selector).trigger('swipe_right');
	    				$($this.selector).trigger('swipe', {direction: "right"});
	    			}
	    		} else {
                    $($this.selector).trigger('deswipe', {});
                }
	    		
	    		$(document).off('touchmove', $this.selector, touch_move);
	    		
	    		start_x = start_y = current_x = current_y = dx = dy = 0;
			};
			
	    	if ('ontouchstart' in document.documentElement) {
				$(document).on('touchstart', $this.selector, function(e){
					var touch = e.originalEvent.touches || e.originalEvent.changedTouches;
					if(touch.length > 1) return
					
					touch = touch[0];
					start_x = touch.pageX;
					start_y = touch.pageY;
					start_scroll = $(window).scrollTop();
					
					touching = true;
					$(this).on('touchmove', touch_move);
				})
				.on('touchend', $this.selector, function(e){
					touch_stop($(this));
				});
			};
			
			if(config.cursor_swipe) {
				$(document).on('mousedown', $this.selector, function(e) {
					touching = true;
					//e.preventDefault();
					start_x = e.pageX;
					start_y = e.pageY;
					touching = true;
					
					$(this).on('mousemove', touch_move);
				}).on('mouseup', function(e){
					touch_stop($($this.selector));
				});
			};
	    });
	     
		return this;
	};
	
})(jQuery);