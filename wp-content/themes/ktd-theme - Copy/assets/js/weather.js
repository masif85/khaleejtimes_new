jQuery(document).ready(function () {

  var filter = '';
  var country_code = '';
  var code = '';

  jQuery('.country').each(function () {
    if (jQuery(this).hasClass('active')) {
      country_code = jQuery(this).data('country');
    }

    switch (country_code) {
      case 'uae':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="309" class="city active">DUBAI</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="300" class="city ">ABU DHABI</a>' +
          '</li>' +

          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="327" class="city">SHARJAH</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="303" class="city">AJMAN</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="334" class="city">UMM AL QUWAIN</a>' +
          '</li>' +

          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="322" class="city">RAS AL KHAIMAH</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="311" class="city">FUJAIRAH</a>' +
          '</li>' +

          '</ul>' +
          '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
        code = '309';
        break;
      case 'ksa':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="344" class="city active">Al RIYADH</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="350" class="city">AL MADINA AM MONAWORA</a>' +
          '</li>' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="379" class="city">MECCA AL MOKARMA</a>' +
          '</li>' +
          '</ul>';
        code = '344';
        break;
      case 'bh':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="1269" class="city  active">AL MANAMA</a>' +
          '</li>' +
          '</ul>' +
          '<ul id="city" class="mobile-sub-menu shadow-sm city"></ul>';
        code = '1269';
        break;
      case 'om':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="427" class="city  active">MUSCAT</a>' +
          '</li>' +
          '</ul>' +
          '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
        code = '427';
        break;
      case 'qat':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="218" class="city  active">AL DOHA</a>' +
          '</li>' +
          '</ul>' +
          '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
        code = '218';
        break;
      case 'kwt':
        filter = '<ul class="sub-menu" data-id="city">' +
          '<li class="sub-menu-item">' +
          '<a href="javascript:void(0);" data-code="284" class="city  active">KUWAIT CITY</a>' +
          '</li>' +
          '</ul>' +
          '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
        code = '284';
        break;
    }

  });
  jQuery('#city-filter').animate({opacity: 0}, function () {
    jQuery("#city-filter").html(filter);

    var sub = jQuery('#city-filter .sub-menu');
    var subH = sub.parent().height();
    var subId = sub.data('id');
    var count = 0;

    if (jQuery(window).width() < 992) {
      jQuery('#' + subId).css({'top': subH + 'px'});
      sub.find('li').each(function () {
        if (!jQuery(this).isInViewport()) {
          jQuery(this).detach().appendTo('#' + subId);
          count++;
        }
      });

      var dots = '<a href="javascript:void(0);" class="toggle-mobile-sub-menu"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>';

      jQuery('#' + subId).css({'top': subH + 'px'});

      if (!sub.find('.toggle-mobile-sub-menu').length) {
        if (count > 0)
          jQuery(dots).appendTo(sub);
      }
    }
  }).end();

  jQuery('#city-filter').animate({opacity: 1}, 500, function () {

  }).end();

    /* jQuery.ajax({
    url: 'https://widgets.media.devops.arabiaweather.com/widget/khaleej?locationID='+code,
  success: function(data){
        jQuery('#weather-container').html(data);
    }
  }); */
  var url = 'https://widgets.media.devops.arabiaweather.com/widget/khaleej?locationID=' + code;

  jQuery("#weather-container").html('').animate({opacity: 0}, function () {
  }).end();

  jQuery("#weather-container").html('<iframe id="wifrm" onload="" src="' + url + '" scrolling="no"></iframe>').animate({opacity: 1}, 500, function () {
  }).end();

});

jQuery(document).on('click', '.country', function () {
  jQuery('.country').removeClass('active');

  var filter = '';
  var code = '';
  var country_code = jQuery(this).data('country');

  jQuery(this).addClass('active');

  switch (country_code) {
    case 'uae':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="309" class="city active">DUBAI</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="300" class="city">ABU DHABI</a>' +
        '</li>' +

        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="327" class="city">SHARJAH</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="303" class="city">AJMAN</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="334" class="city">UMM AL QUWAIN</a>' +
        '</li>' +

        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="322" class="city">RAS AL KHAIMAH</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="311" class="city">FUJAIRAH</a>' +
        '</li>' +

        '</ul>' +
        '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
      code = '309';
      break;
    case 'ksa':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="344" class="city  active">Al RIYADH</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="350" class="city">AL MADINA AM MONAWORA</a>' +
        '</li>' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="379" class="city">MECCA AL MOKARMA</a>' +
        '</li>' +
        '</ul>';
      code = '344';
      break;
    case 'bh':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="1269" class="city  active">AL MANAMA</a>' +
        '</li>' +
        '</ul>' +
        '<ul id="city" class="mobile-sub-menu shadow-sm city"></ul>';
      code = '1269';
      break;
    case 'om':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="427" class="city  active">MUSCAT</a>' +
        '</li>' +
        '</ul>' +
        '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
      code = '427';
      break;
    case 'qat':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="218" class="city  active">AL DOHA</a>' +
        '</li>' +
        '</ul>' +
        '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
      code = '218';
      break;
    case 'kwt':
      filter = '<ul class="sub-menu" data-id="city">' +
        '<li class="sub-menu-item">' +
        '<a href="javascript:void(0);" data-code="284" class="city  active">KUWAIT CITY</a>' +
        '</li>' +
        '</ul>' +
        '<ul id="city" class="mobile-sub-menu shadow-sm"></ul>';
      code = '284';
      break;
  }

  jQuery('#city-filter').animate({opacity: 0}, function () {
    jQuery("#city-filter").html(filter);

    var sub = jQuery('#city-filter .sub-menu');
    var subH = sub.parent().height();
    var subId = sub.data('id');
    var count = 0;

    if (jQuery(window).width() < 992) {
      jQuery('#' + subId).css({'top': subH + 'px'});

      sub.find('li').each(function () {
        if (!jQuery(this).isInViewport()) {
          jQuery(this).detach().appendTo('#' + subId);
          count++;
        }
      });

      var dots = '<a href="javascript:void(0);" class="toggle-mobile-sub-menu"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></a>';

      jQuery('#' + subId).css({'top': subH + 'px'});

      if (!sub.find('.toggle-mobile-sub-menu').length) {
        if (count > 0)
          jQuery(dots).appendTo(sub);
      }
    }
  }).end();

  jQuery('#city-filter').animate({opacity: 1}, 500, function () {

  }).end();

  var url = 'https://widgets.media.devops.arabiaweather.com/widget/khaleej?locationID=' + code;

  jQuery("#weather-container").html('').animate({opacity: 0}, function () {
  }).end();

  jQuery("#weather-container").html('<iframe id="wifrm" onload="" src="' + url + '" scrolling="no"></iframe>').animate({opacity: 1}, 500, function () {
  }).end();

});

jQuery(document).on('click', '.city', function () {
  jQuery('.city').removeClass('active');

  var code = jQuery(this).data('code');
  jQuery(this).addClass('active');
  /* jQuery.ajax({
        url: 'https://widgets.media.devops.arabiaweather.com/widget/khaleej?locationID='+code,
    success: function(data){
            jQuery('#weather-container').html(data);
        }
  }); */
  var url = 'https://widgets.media.devops.arabiaweather.com/widget/khaleej?locationID=' + code;
  jQuery("#weather-container").html('').animate({opacity: 0}, function () {
  }).end();

  jQuery("#weather-container").html('<iframe id="wifrm" onload="" src="' + url + '" scrolling="no"></iframe>').animate({opacity: 1}, 500, function () {
  }).end();

});
