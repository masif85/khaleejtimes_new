(function($){
    var mleft = 0;
    var sliding = 0;
		var imslider = {};
		imslider.current_item = 1;

		$(document).ready(function(){
			$(document).trigger('im_slider');
		});

    $(document).on('im_slider', function(){

        setTimeout(function(){
            $("#im-slider-content").css({
                height: $("#im-slider").height(),
                width: $("#im-slider").width() * $(".im-slide").length
            });
            $(".im-slide").show();
            $(".im-slide").css("width", $("#im-slider").width());


            $(window).resize(function(){
                $("#im-slider-content").css({
                    width: ($("#im-slider").width()+1) * $(".im-slide").length
                });
                $(".im-slide").css("width", $("#im-slider").width());

                var prev_items = $(".im-slide[rel=" + imslider.current_item + "]").prevAll('.im-slide').length;
                $("#im-slider-content").css({"margin-left": -($("#im-slider").width()*prev_items)});
            });
        }, 500);


        $("#im-slider").everySwipe({
            min_swipe: 80,
            max_diff: 40,
            cursor_swipe: false
        });

        $(document).on('swiping', '#im-slider', function(e, data){
            if(sliding == 1) { return; }
           $("#im-slider-content").css({"margin-left": -( mleft-(-data.x) ) });
        });


        $(document).on('deswipe', '#im-slider', function(e, data){
            if(sliding == 1) { return; }
            $("#im-slider-content").animate({"margin-left": (-( mleft ) || 0) }, 500);
        });

        $(document).on('swipe', '#im-slider', function(e, data){
            if(sliding == 1) { return; }

            var slide_item = $("<div/>");
            if(data.direction == "right") {
                slide_item = $(".im-slide-active").prev();
            } else {
                slide_item = $(".im-slide-active").next();
            }

            if($(slide_item).attr("rel") && $(slide_item).attr("rel").length > 5) {
                slide_to_uuid($(slide_item).attr("rel"));
            } else {
                $("#im-slider-content").animate({"margin-left": (-( mleft ) || 0) }, 500);
            }
        });

        $("<span/>").html("<strong>«</strong>")
            .css({"float": "left", "cursor": "pointer"})
            .click(function(){
                var next_item = $(".im-slide-active").prev();
                if($(next_item).attr("rel") !== undefined && $(next_item).attr("rel").length > 5) {
                    slide_to_uuid($(next_item).attr("rel"));
                } else {
                    slide_to_uuid($(".im-slide:last").attr("rel"));
                }
            })
            .appendTo("#im-slider-control");

        $(".im-slide").each(function(){
            var uuid = $(this).attr("rel");
            $("<span/>").addClass("im-slide-navigate")
                .html("•")
                .addClass("im-slider-bullet")
                .css({"font-size": 50, "color": "#b1b3b4", "cursor": "pointer"})
                .attr("rel", uuid)
                .appendTo("#im-slider-control")
                .click(function(){
                    slide_to_uuid(uuid);
                });
        });

        $("<span/>").html("<strong>»</strong>")
            .css({"float": "right", "cursor": "pointer"})
            .click(function(){
                var next_item = $(".im-slide-active").next();
                if($(next_item).attr("rel") !== undefined && $(next_item).attr("rel").length > 5) {
                    slide_to_uuid($(next_item).attr("rel"))
                } else {
                    slide_to_uuid($(".im-slide:first").attr("rel"));
                }
            })
            .appendTo("#im-slider-control");

				if(imslider){

					if( imslider.current_item && $(".im-slide-navigate[rel=" + imslider.current_item + "]").length > 0) {
							slide_to_uuid(imslider.current_item, 1500);
					} else {
							$(".im-slide-navigate:first").css({"color": "#df137a"}).addClass("im-slide-active")
					}
				}
    });

    function slide_to_uuid(uuid, timer) {
        sliding = 1;
        $(".im-slide-navigate").css({"color": "#b1b3b4"}).removeClass("im-slide-active");

        var prev_items = $(".im-slide[rel=" + uuid + "]").prevAll('.im-slide').length;
        $("#im-slider-content").animate({"margin-left": -($("#im-slider").width()*prev_items)}, timer, function(){
            $(".im-slide-navigate[rel=" + uuid + "]").css({"color": "#df137a"}).addClass("im-slide-active");
            imslider.current_item = uuid;
            mleft = ($("#im-slider").width()*prev_items);
            sliding = 0;
        });

    }

})(jQuery);