const $buttonOpen = $('.opener .opener-button.opener-button-open, .opener .opener-image img');
const $buttonClose = $('.opener .opener-button.opener-button-close');

// Image opener
$buttonOpen.click(function () {
  const $opener = $(this).closest('.opener');
  const $carousel = $opener.closest('.carousel');

  // If image is part of a carousel...
  if ($carousel.length > 0) {
    $carousel.addClass('opener-opened');
  } else {
    $opener.addClass('opener-opened');
  }

  $('body').addClass('modal-open');
});

$buttonClose.click(function () {
  const $opener = $(this).closest('.opener');
  const $carousel = $opener.closest('.carousel');

  // If image is part of a carousel...
  if ($carousel.length > 0) {
    $carousel.removeClass('opener-opened');
  } else {
    $opener.removeClass('opener-opened');
  }

  $('body').removeClass('modal-open');
});

$(document).keydown(function (e) {

  if (e.keyCode === 27) {
    $buttonClose.click();
  }
});
