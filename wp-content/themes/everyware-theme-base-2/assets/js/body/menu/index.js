$(function () {
  const $fixedTop = $('.navbar-wrapper');
  handleScroll();
  window.addEventListener('scroll', handleScroll);
  window.addEventListener('load', handleScroll);


  $fixedTop.on('show.bs.collapse', handleMenuCollapse);
  $fixedTop.on('hide.bs.collapse', handleMenuCollapse);

  function handleScroll() {
    const currentScrollPos = window.pageYOffset;
    const containerPos = $fixedTop.position().top;
    if (currentScrollPos > containerPos) {
      $fixedTop.addClass('fixed-top').removeClass('position-relative');
    } else {
      $fixedTop.removeClass('fixed-top').addClass('position-relative');
    }
  }

  /**
   * Handle Bootstrap's collapse events on main menu
   * by adding class to body tag to freeze scrolling
   * @param {*} event
   */
  function handleMenuCollapse(event) {
    const $navbarlogo = $('.navbar-brand');
    const $navbarText = $('.navbar-text-main');
    const $navbarList = $('.navbar-nav-main');

    if (event.namespace === 'bs.collapse') {
      if (event.type === 'show') {
        $navbarlogo.addClass('collapse-active');
        $navbarText.removeClass('d-none');
        $navbarList.addClass('d-none');
      } else if (event.type === 'hide') {
        $navbarlogo.removeClass('collapse-active');
        $navbarText.addClass('d-none');
        $navbarList.removeClass('d-none');
      }
    }
  }
});
