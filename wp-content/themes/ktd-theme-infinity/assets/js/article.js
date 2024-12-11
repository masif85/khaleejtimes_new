jQuery.noConflict();
(function($) {   
  $(document).ready(function() {
    // Init gallery
    initGallery();	
	//dpause();
	
	


	
  });
  
  window.dpause=function(){
	 var contentMap = {};
    $('.ew-embed, .facebook-responsive-video').filter(function(){
        contentMap[this.class] = $(this).html();
       // var src = $(this).attr('src');
        var src = $('iframe',this).attr('src');
		if(src){
        if (src.indexOf("dailymotion") >= 0)
        {
        src=src.slice(0, -1)
        src=src+'1';
      // $('iframe',this).attr('src',src);
       }
		}
    });  
  }

  window.initGallery = function() {
    const myCustomSlider = document.querySelectorAll('.gallery-top');
    const myCustomGalleryThumbs = document.querySelectorAll('.gallery-thumbs');
    const captions = document.querySelectorAll('.slide-captions');
    //const photoslider = document.querySelectorAll('.photo-slider');
    //const photoslider = document.querySelectorAll('.gallery-top').find('swiper-slide');
    for (i = 0; i < myCustomSlider.length; i++) {
      myCustomSlider[i].classList.add('gallery-top-' + i);
      $(".gallery-top-"+i).find(".swiper-slide").attr('data-number',i);
      myCustomGalleryThumbs[i].classList.add('gallery-thumbs-' + i);
      captions[i].classList.add('slide-captions-' + i);
      //photoslider[i].classList.add("photo-slider-"+i);
      //$(".photo-slider-"+i).attr('data-number',i);
      //photoslider[i].classList.add('slide-captionss' + i);
      //alert();
      /*      var current = 0;
              $(".slide-captions").each(function(){
                  $(this).addClass("captions"+current);
                  current++;
              });
              var current2 = 0;
              $(".thumbsn").each(function(){
                  var data_title=$(this).attr("data-title");
                  var data_subtitle=$(this).attr("data-subtitle");
                  $(".captions"+current2).html(data_title+"<br />"+data_subtitle);
                  current2++;
              });*/
      //myCustomGalleryThumbscaption[i].classList.add('gallery-caption-' + i);
      //var slideTitle = $(".swiper-slide").attr("data-title");
      var galleryThumbs = new Swiper('.gallery-thumbs-' + i , {
        spaceBetween: 10,
        slidesPerView: 4,
        freeMode: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        autoplay:false
      });
      /*       var galleryTop2 = new Swiper('.gallery-top-' + i{
                  $(".slide-captions").html(i);
               });

              var thumbvalue =$('.gallery-top-' + i).attr("data-title");  */
      var j=i;
      var galleryTop = new Swiper('.gallery-top-' + i, {
        spaceBetween: 10,
        autoplay:false,
        //$(".slide-captions").html("afs"),
        thumbs: {
          // el: '.thumbs-class',
          // slidesPerView: 5,
          swiper: galleryThumbs,
        },
        navigation: {
          nextEl: '.swiper-button-next',
          prevEl: '.swiper-button-prev',
        },
        on: {
          slideChangeTransitionStart: function () {
            var swiper = this;
            //alert(swiper.activeIndex);
            gallerySlideEvent(galleryTop.activeIndex);
            var slideTitle =$(swiper.slides[swiper.activeIndex]).attr("data-title");
            // var captionval=$(swiper.slides[swiper.activeIndex]).("caption-val");
            // var captionval=$(swiper.slides[swiper.activeIndex]).("caption-val");
            var slideSubtitle = $(swiper.slides[swiper.activeIndex]).attr("data-subtitle");
            var data_slider=$(swiper.slides[swiper.activeIndex]).attr("data-number");
            // alert(data_slider);
            //alert(JSON.stringify(captions));
            //var capt=$(swiper.slides[swiper.activeIndex]).find(".slide-captions");
            $(".slide-captions-"+data_slider).html(function(){
              return "<h2 class='current-title'>"+slideTitle+"</h2>" + "<h3 class='current-subtitle'>"+slideSubtitle+"</h3>";
            });
          },
        },
        hashNavigation: {
          replaceState: true,
        }
      });

      var sizes1 = $('.gallery-top-'+i).find(".swiper-slide").attr("data-title");
      var sizes2 = $('.gallery-top-'+i).find(".swiper-slide").attr("data-subtitle");
      $(".slide-captions-"+i).html(function() {
        return "<h2 class='current-title'>" + sizes1 + "</h2>" + "<h3 class='current-subtitle'>" + sizes2 + "</h3>";
      });
    }
  }
})(jQuery)
