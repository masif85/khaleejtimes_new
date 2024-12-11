jQuery.noConflict();
(function($) {   
  $(document).ready(function() {
	  function detectMob() {
    const toMatch = [
        /Android/i,
        /webOS/i,
        /iPhone/i,
        /iPad/i,
        /iPod/i,
        /BlackBerry/i,
        /Windows Phone/i
    ];
    
    return toMatch.some((toMatchItem) => {
        return navigator.userAgent.match(toMatchItem);
    });
}
	 var offsetvalue=-200; 
	  	if(detecMob())  
		{
			offsetvalue=-6500;
		}
    // Instantiate ScrollTrigger
    var scrollTrigger = new ScrollTrigger.default();
	   
    addTriggers();
   


    function addTriggers()
    {
window.scrollTracker = {
    "25": 0,
    "50": 0,
    "75": 0,
    "100": 0
  };	
		
      scrollTrigger.add('[data-triggerViews]', {
        once: false,
        offset: {
          element: { y: 200 },
          viewport: { y: 0.3 }
        },
        toggle: {
          callback: {
            in: triggerView
          }
        }
      });
      scrollTrigger.add('[data-triggerLoadArticle]', {
        once: true,
        offset: {
          element: { y: offsetvalue }
        },
        toggle: {
          callback: {
            in: loadArticle,
          }
        }
      });
		
		
		
	    var windowHeight = jQuery(document).height();
		var currentPosition = jQuery(document).scrollTop();
		var windowViewingArea = jQuery(window).height();
		var bottomScrollPosition = currentPosition + windowViewingArea;
		var percentScrolled = parseInt((bottomScrollPosition / windowHeight * 100).toFixed(0));

		var fireEvent = 0;
		var scrollBucket = 0;

		if (percentScrolled >= 25 && percentScrolled < 50) {
		  checkAndFireEvent("25", "user_scroll");
		} else if (percentScrolled >= 50 && percentScrolled < 75) {
		  checkAndFireEvent("50", "user_scroll");
		} else if (percentScrolled >= 75 && percentScrolled < 100) {
		  checkAndFireEvent("75", "user_scroll");
		} else if (percentScrolled === 100) {
		  checkAndFireEvent("100", "user_scroll");
		}	
		
    }

    function triggerView(trigger) {
      var triggerElement = trigger.element;
      var url = triggerElement.dataset.url;

      // Update URL in address bar

    //if (window.location.href.indexOf("/arabic/") > -1 || window.location.href.indexOf("=ar") > -1 || window.location.href.indexOf("/ar/") > -1) {        
       // var url = window.location.href;
       // }
      window.history.replaceState(null, null, url);
    }

	  
	  
    function loadArticle(trigger) {
      var start = parseInt(trigger.element.dataset.article);
      $.ajax({
        async: false,
        type: 'GET',
        dataType: 'text',
        url: infiniteArticles[start-1] + '?infinite=1&skip=' + start,
        success: function(response) {
          $('.loaded-articles').append(response);
		  //dpause2();
          initGallery();
          setTimeout(function() { 
            loadInfiniteAd(start); 
          }, 2000);		  
		  // Retrack Example when new data is present
          // Unbind previous article from ScrollTrigger so it will not trigger loadArticle() again
          trigger.element.removeAttribute('data-triggerLoadArticle');
          scrollTrigger.remove('[data-triggerViews]');
           scrollTrigger.remove('invisible');
          // Bind new article to ScrollTrigger
          addTriggers();
		 
        }
      });
    }
	
	
	function dpause2(){
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
     //  $('iframe',this).attr('src',src);
       }
		}
    });  
  }
    
    // Google DFP Ad
    function loadInfiniteAd(slotid) {
      var output = '#content-right-' + slotid + ' .output-container';
      $(output).html('<h3>Loading...</h3>');
      $(output).html('');

      // Amazon affliate script
      //var affliateScript = '<div class="content_padd">&nbsp;</div><div class="amzsidebarprdlist"></div>';
      //$(output).html('<h1>Loading...</h1>');
      //$(output).html(affliateScript);

      setTimeout(function() {
        var slotSidebarMPU1 = 'adslot-sidebar-mpu1-' + slotid;
        var slotSidebarMPU2 = 'adslot-sidebar-mpu2-' + slotid;
        var slotLeaderboardFooter = 'adslot-leaderboard-footer-' + slotid;
        //var slotInsideAd = 'adslot-insidead-' + slotid;

        //AD: MPU1
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/78059622/Responsive-Article-MPU-1',[300,250], slotSidebarMPU1).addService(googletag.pubads());
          googletag.display(slotSidebarMPU1);
          googletag.pubads().refresh([slot]);
        });
        //AD: MPU2
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/78059622/Responsive-Article-MPU-2',[300,250], slotSidebarMPU2).addService(googletag.pubads());
          googletag.display(slotSidebarMPU2);
          googletag.pubads().refresh([slot]);
        });
        //AD: Leaderboard Footer
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/78059622/Responsive-Article-Leaderboard-footer',[[970,250],[320,100]], slotLeaderboardFooter).defineSizeMapping(mappingMobLeadfullads).addService(googletag.pubads());
          googletag.display(slotLeaderboardFooter);
          googletag.pubads().refresh([slot]);
        });
        //AD: InsideAd
        //googletag.cmd.push(function() {
        //  var slot = googletag.defineSlot('/78059622/Responsive-Article-Inarticle-MPU',[[336,280],[300,250]], slotInsideAd).addService(googletag.pubads());
        //  googletag.display(slotInsideAd);
        //  googletag.pubads().refresh([slot]);
        //});
      }, 250);
    }
  });
})(jQuery)
