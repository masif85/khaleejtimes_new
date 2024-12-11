jQuery(document).ready(function($){
    $('.frontpage-slider').slick({
        dots: true,
        infinite: true,
        speed: 500,
        fade: true,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 7000,
      });

      $('.article-slider').slick({
        dots: true,
        infinite: true,
        speed: 500,
        fade: true,
        slidesToScroll: 1,
        adaptiveHeight: true
      });

      $('.gallery-slider').slick({
        dots: true,
        infinite: true,
        speed: 500,
        fade: true,
        slidesToScroll: 1,
        adaptiveHeight: true,
        autoplay: true,
        autoplaySpeed: 5000,
      });
});