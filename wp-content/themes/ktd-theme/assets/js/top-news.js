jQuery.noConflict();
(function($) {   
  $(document).ready(function() {
	  // Swiper: Slider
   var slider1 = new Swiper('.swiper-container.top-news-ticker', {
    observer: true,
        observeParents: true,
        loop: true,
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
           autoplay: {
            delay: 3500,
            disableOnInteraction: true,
          },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            paginationClickable: true,
          },
        slidesPerView: 3,
        paginationClickable: true,
        spaceBetween: 20,
        breakpoints: {
            1920: {
                slidesPerView: 4,
                spaceBetween: 30
            },
            991: {
                slidesPerView: 2,
                spaceBetween: 30
            },
            480: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            0: {
                slidesPerView: 1,
                spaceBetween: 10
            }
        }
    });
    // Swiper: Slider 2
   var slider2 = new Swiper('.swiper-container.swiper-two-opinion', {
        observer: true,
        observeParents: true,
        loop: true,
          navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          },
           autoplay: {
            delay: 3500,
            disableOnInteraction: true,
          },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            paginationClickable: true,
          },
        slidesPerView: 3,
        paginationClickable: true,
        spaceBetween: 20,
        breakpoints: {
            1920: {
                slidesPerView: 4,
                spaceBetween: 30
            },
            991: {
                slidesPerView: 2,
                spaceBetween: 30
            },
            480: {
                slidesPerView: 1,
                spaceBetween: 10
            },
            0: {
                slidesPerView: 1,
                spaceBetween: 10
            }
        }
    });
  });  
})(jQuery)