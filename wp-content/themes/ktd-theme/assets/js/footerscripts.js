jQuery.noConflict();
jQuery(() => {
  const menu = jQuery('#ham-burger-menu-ctn-top'), bars = jQuery('.ham-burger-menu-bars'), items = jQuery('.ham-burger-menu-item'), content = jQuery('#ham-burger-menu-cnt');  
  let firstClick = true, menuClosed = true;  
  let handleMenu = event => {
    if(!firstClick) {
      bars.toggleClass('ham-burger-crossed ham-burger-hamburger');
    } else {
      bars.addClass('ham-burger-crossed');
      firstClick = false;
    }      
    menuClosed = !menuClosed;
    content.toggleClass('ham-burger-dropped');
    event.stopPropagation();
  };  
  menu.on('click', event => {
    handleMenu(event);
  });  
  jQuery('body').not('#ham-burger-menu-cnt, #ham-burger-menu-ctn-top').on('click', event => {
    if(!menuClosed) handleMenu(event);
  });  
  jQuery('#ham-burger-menu-cnt, #ham-burger-menu-ctn-top').on('click', event => event.stopPropagation());
});
jQuery(".shownitem1").on({
    mouseenter: function () {
  var datatab=jQuery(this).attr("data-tab");
    jQuery(".itemno"+datatab).fadeIn(150);    
    },
    mouseleave: function () { 
  var datatab=jQuery(this).attr("data-tab");
  if (jQuery('.itemno'+datatab+':hover').length != 1) { 
  var datatab=jQuery(this).attr("data-tab");    
    jQuery(".itemno"+datatab).fadeOut(150); 
  } 
}
});
jQuery(".shownitemz1").on({
    mouseenter: function () {   
    },
    mouseleave: function () { 
    jQuery(".shownitemz1").fadeOut(150);    
}
});

//hide right drop down if less items #click-topmenu

/*jQuery('.menu-item').each(function() {
  if(jQuery(this).is('[style]')===false)
  {
    jQuery(".click-topmenu").show();
  }else { {
    jQuery(".click-topmenu").hide();
  }}
});
*/
//end of code for 


// Code that uses other library's jQuery can follow here.
var method="mouseenter";
if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
method="click";
}
//var h = 0;
var val = 0;
jQuery(document).ready(function(){
jQuery('.click-topmenu').on(''+method+'',function() {
   if (jQuery('.top-main-menu ul#nav').hasClass('show')) {
    jQuery('.top-main-menu ul#nav').removeClass('show');
    jQuery('.top-main-menu ul#nav li.menu-item').removeClass('drop')
    return;
  } 
  val = -6;
  jQuery('.top-main-menu ul#nav li.menu-item').each(function() {
    var of = jQuery(this).offset().top - jQuery('#nav').offset().top;
  
    if ( of > 20) {
      jQuery(this).addClass('drop');  
      var menu_height=jQuery(this).height()+1; 
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += menu_height;
    }
    jQuery('.top-main-menu ul#nav').addClass('show');
    });
  });

jQuery('.top-main-menu').on('mouseleave',function() {
   if (jQuery('.top-main-menu ul#nav').hasClass('show')) {
   jQuery('.top-main-menu ul#nav').removeClass('show');
    jQuery('.top-main-menu ul#nav li.menu-item').removeClass('drop')
    return;
  } 
  })  
});
function handleSelect(elm){ 
   //window.location = elm.value; /* .html if html file */ 
   //window.location = elm.value+".html";
   window.open(elm.value, "_blank");
} 
var h = 30;
var val = 0;
jQuery('.click').click(function() {
  if (jQuery('ol').hasClass('show')) {
    jQuery('ol').removeClass('show');
    jQuery('ol li.menu-item').removeClass('drop')
    return;
  }
  val = 0;
  jQuery('ol li.menu-item').each(function() {
    var of = jQuery(this).offset().top - jQuery('ol').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('drop');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('ol').addClass('show');
  })
})

var h = 30;
var val = 0;
var attributes='';
var active_dl = '';
jQuery('.click').click(function() {
  var tabId = jQuery(this).parent().children(':first').attr('id');

  if( active_dl != '' && active_dl != tabId ){
    jQuery("#"+active_dl).removeClass('show');
    jQuery('#'+active_dl+' li.menu-item').removeClass('drop');
  }

  jQuery("#"+tabId+" > .ns-homepage-lib").each(
    function() {
      var elem = jQuery(this);
      if (elem.children().length == 0) {
        elem.remove();
      }
    }
  );
  
  var attributes='';
  var fbutes="";
  var lbutes="";

  if( jQuery("#"+tabId).hasClass('show') ) {
    active_dl = '';
    jQuery("#"+tabId).removeClass('show');
    jQuery('#'+tabId+' li.menu-item').removeClass('drop');	
    return;
  }
  
  val = 0;
  var num=0;

  jQuery('#'+tabId+' > li.menu-item').each(function(index) {
    var of = jQuery(this).offset().top - jQuery('#'+tabId).offset().top;	
    if ( of > 20) {
      jQuery(this).addClass('drop');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;		
      attributes+=fbutes+jQuery(this).get(0).outerHTML; 	  
      num++;
      jQuery(this).remove();
    }	

    jQuery('#'+tabId).addClass('show');
    active_dl = tabId;
  })
  
  jQuery("#"+tabId+" > .ns-homepage-lib").each(
  function() {
    var elem = jQuery(this);
    if (elem.children().length == 0) {
      elem.remove();
    }
  });
  
  if( jQuery("#"+tabId+" > .ns-homepage-lib").length > 0 ){
    jQuery("#"+tabId+" > .ns-homepage-lib").append(attributes);
  }
  else{
    jQuery("#"+tabId).append("<ul class='ns-homepage-lib'>"+attributes+"</ul>");
  }

});

/**
 * Close the dropdown homepage
 */
/*jQuery('html').click(function(e) {
	jQuery("#"+active_dl).removeClass('show');
    jQuery('#'+active_dl+' li.menu-item').removeClass('drop');	
});
	
jQuery('.click').click(function(e){
	e.stopPropagation();
});
*/
/**
 * End Close dropdowm functionality
 */

var h = 30;
var val = 0;

jQuery('.clicks').click(function() {
  if (jQuery('dd').hasClass('shows')) {
    jQuery('dd').removeClass('shows');
    jQuery('dd li.menu-items').removeClass('drops')
    return;
  }
  val = 0;
  jQuery('dd li.menu-items').each(function() {
    var of = jQuery(this).offset().top - jQuery('dd').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('drops');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('dd').addClass('shows');
  })
})



var h = 30;
var val = 0;

jQuery('.clicked').click(function() {
  if (jQuery('dd').hasClass('showed')) {
    jQuery('dd').removeClass('showed');
    jQuery('dd li.menu-itemed').removeClass('droped')
    return;
  }
  val = 0;
  jQuery('dd li.menu-itemed').each(function() {
    var of = jQuery(this).offset().top - jQuery('dd').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('droped');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('dd').addClass('showed');
  })
})





var h = 30;
var val = 0;

jQuery('.clickd').click(function() {
  if (jQuery('ol').hasClass('showd')) {
    jQuery('ol').removeClass('showd');
    jQuery('ol li.menu-itemd').removeClass('dropd')
    return;
  }
  val = 0;
  jQuery('ol li.menu-itemd').each(function() {
    var of = jQuery(this).offset().top - jQuery('ol').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('dropd');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('ol').addClass('showd');
  })
})




var h = 30;
var val = 0;

jQuery('.clicke').click(function() {
  if (jQuery('dd').hasClass('showe')) {
    jQuery('dd').removeClass('showe');
    jQuery('dd li.menu-iteme').removeClass('drope')
    return;
  }
  val = 0;
  jQuery('dd li.menu-iteme').each(function() {
    var of = jQuery(this).offset().top - jQuery('dd').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('drope');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('dd').addClass('showe');
  })
})




var h = 30;
var val = 0;

jQuery('.clickq').click(function() {
  if (jQuery('ol').hasClass('showq')) {
    jQuery('ol').removeClass('showq');
    jQuery('ol li.menu-itemq').removeClass('dropq')
    return;
  }
  val = 0;
  jQuery('ol li.menu-itemq').each(function() {
    var of = jQuery(this).offset().top - jQuery('ol').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('dropq');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('ol').addClass('showq');
  })
})


var h = 30;
var val = 0;

jQuery('.clickm').click(function() {
  if (jQuery('ol').hasClass('showm')) {
    jQuery('ol').removeClass('showm');
    jQuery('ol li.menu-itemm').removeClass('dropm')
    return;
  }
  val = 0;
  jQuery('ol li.menu-itemm').each(function() {
    var of = jQuery(this).offset().top - jQuery('ol').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('dropm');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('ol').addClass('showm');
  })
})


var h = 30;
var val = 0;

jQuery('.clicko').click(function() {
  if (jQuery('ol').hasClass('showo')) {
    jQuery('ol').removeClass('showo');
    jQuery('ol li.menu-itemo').removeClass('dropo')
    return;
  }
  val = 0;
  jQuery('ol li.menu-itemo').each(function() {
    var of = jQuery(this).offset().top - jQuery('ol').offset().top;
    if ( of > 20) {
      jQuery(this).addClass('dropo');
      jQuery(this).css('top', 'calc(100% + ' + val + 'px)');
      val += h;
    }
    jQuery('ol').addClass('showo');
  })
})

function openSearch() {
  document.getElementById("myOverlay").style.display = "block";
  setTimeout(
    function() 
    {
     jQuery("#search").focus();
    }, 500);  
}
function closeSearch() {
  document.getElementById("myOverlay").style.display = "none";
}


/*-- DropDown --*/
jQuery(".dropdown-nf").hover(function() {
  var isHovered = jQuery(this).is(":hover");
  if (isHovered) {
    jQuery(this).children("ul").stop().slideDown(300);
  } else {
    jQuery(this).children("ul").stop().slideUp(300);
  }
});
