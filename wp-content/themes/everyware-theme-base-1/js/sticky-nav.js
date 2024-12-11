jQuery(document).ready(function( $ ) {
    var stickyMainNav = $('.main-navigation').offset().top;

    $(window).scroll(function () {
        var currentScroll = $(window).scrollTop();
        if (currentScroll >= stickyMainNav) {
            $('#wrapper-navbar').css({
                position: 'fixed',
                top: '0',
                left: '0',
            });
        } else {
            $('#wrapper-navbar').css({
                position: 'static'
            });
        }
    });

});