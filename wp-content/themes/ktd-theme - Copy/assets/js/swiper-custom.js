var swiper = new Swiper(".mySwipersales", {
    slidesPerView: 1,
    spaceBetween: 10,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
   
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      },
      loop: true,
      breakpoints: {
        // when window width is >= 320px
        320: {
          slidesPerView: 1,
          spaceBetween: 5
        },
        // when window width is >= 480px
        480: {
          slidesPerView: 1,
          spaceBetween: 5
        },
        // when window width is >= 640px
        640: {
          slidesPerView: 3,
          spaceBetween: 5
        },
        991: {
          slidesPerView: 4,
          spaceBetween: 5
        },
      }
    });



  var swiper = new Swiper(".sldSwiper", {
    slidesPerView: 1,
    spaceBetween: 10,
    pagination: {
      el: ".swiper-pagination",
      clickable: true,
    },
     // Navigation arrows
     navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    autoplay: {
        delay: 2500,
        disableOnInteraction: false,
      },
      loop: true,
    breakpoints: {
      "@0.00": {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      "@0.75": {
        slidesPerView: 3,
        spaceBetween: 5,
      },
      "@1.00": {
        slidesPerView: 4,
        spaceBetween: 5,
      },
      "@1.50": {
        slidesPerView: 6,
        spaceBetween: 5,
      },
    },
  });

 /* document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

*/
jQuery('a[href^="#"]').click(function() {
	event.preventDefault();
    jQuery('html, body').animate({
        scrollTop: jQuery("#form-class").offset().top
    }, 600);
});

