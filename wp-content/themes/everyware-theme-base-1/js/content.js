jQuery(document).ready(function( $ ) {
	
	if(window.location.href.indexOf("?print") > -1) {
		$('body.article-template-single-article').addClass("print");
		$('.content-area').removeClass("col-md-9").addClass("col-md-12");
		window.print();
		 window.close();
	  }
	
	var adFrequency = $(".in-article-ad").attr('data-ad-frequency');
	$( ".article-restofcontent .article-factbox .article__body" ).removeClass( "article__body" );
	$( ".article-restofcontent .article__body:eq(" + adFrequency  + ")" ).addClass( "beforeArticleAd" );
	$( ".in-article-ad" ).insertAfter( $( ".article__body.beforeArticleAd" ) );
	$( ".in-article-ad" ).removeClass("hidden");

	/* Adds the image caption from the image.twig file to the image slider. */
	$( ".article-page .article_image_caption" ).each(function() {
		var image_caption_uuid = $(this).attr("data-uuid");
		$(this).show();
		$(this).insertAfter( ".article-slider img[uuid='" + image_caption_uuid + "']" );
	});

});

function fontSizeUpdateSmaller() {
	var fontSize = parseInt($("p.article__body").css("font-size"));
	fontSize = fontSize - 2 + "px";
	console.log(fontSize);
	$("p.article__body").css({'font-size':fontSize});
}

function fontSizeUpdateLarger() {
	var fontSize = parseInt($("p.article__body").css("font-size"));
	fontSize = fontSize + 2 + "px";
	console.log(fontSize);
	$("p.article__body").css({'font-size':fontSize});
}