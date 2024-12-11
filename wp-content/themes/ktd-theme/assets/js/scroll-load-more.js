jQuery.noConflict();
(function($) {   
  $(document).ready(function() {
   
    // Instantiate ScrollTrigger
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
 var offsetvalue=-2000; 
      if(detectMob())  
    {
      offsetvalue=-6500;
    }
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
      scrollTrigger.add('[data-triggerView]', {
        once: false,
        offset: {
          element: { y: 200 },
          viewport: { y: 0 }
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

    jQuery.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.search);
    return (results !== null) ? results[1] || 0 : false;
    }

    function triggerView(trigger) {
      var triggerElement = trigger.element;
      var url = triggerElement.dataset.url;
       var startx = triggerElement.dataset.articlez;
      //var start2 = parseInt(trigger.element.dataset.article);
      // Update URL in address bar

    //if (window.location.href.indexOf("/arabic/") > -1 || window.location.href.indexOf("=ar") > -1 || window.location.href.indexOf("/ar/") > -1) {        
       // var url = window.location.href;
       // }
      //window.history.replaceState(null, null, url+"?utm_source=netc&utm_medium=art-rcmd-api&utm_campaign=infinite&utm_content="+start);
      //window.history.replaceState(null, null, url+"?utm_source="+core_source+"&utm_medium=art-rcmd-api&utm_campaign=infinite");
     if(startx>=1)
      {     
        window.history.replaceState(null, null, url+"?utm_source="+core_source+"&utm_medium=art-rcmd-api&utm_campaign=infinite"); 
      }
      else if(startx==0 && jQuery.urlParam('utm_source')=='netcore')
      {     
      //window.history.replaceState(null, null, url);
        window.history.replaceState(null, null, url+"?utm_source=netcore&utm_medium=art-rcmd-api&utm_campaign=infinite"); 
      }
      else
      {
        window.history.replaceState(null, null, url);
      }
    }

    function loadArticle(trigger) {
   var start = parseInt(trigger.element.dataset.article);
    //if(start>1)
   // {
    // var xurl=window.location.href.split('?')[0];
    // window.history.replaceState(null, null, xurl+"?utm_source="+core_source+"&utm_medium=art-rcmd-api&utm_campaign=infinite"); 
    //}
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
          scrollTrigger.remove('[data-triggerView]');
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
        var slotSidebarMPUHalfPage = 'adslot-sidebar-mpu-halfpage-' + slotid;
        var slotLeaderboardFooter = 'adslot-leaderboard-footer-' + slotid;
        var slotInArticleMPU1 = 'adslot-inarticle-mpu1-' + slotid;
        var slotInArticleMPU2 = 'adslot-inarticle-mpu2-' + slotid;
        var slotInArticleMPU3 = 'adslot-inarticle-mpu3-' + slotid;
        var mappingMPUInArticleInfinite = googletag.sizeMapping().
        addSize([1024,0],[[300,250],[336,280]]). //desktop
        addSize([768,0],[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid']). //tablet
        addSize([567,0],[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid']). //smartphone
        addSize([0,0],[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid']). //other
        build();
        
        //var slotInsideAd = 'adslot-insidead-' + slotid;
        if(window.innerWidth > 1200){
          //AD: MPU1-innerWidth
          googletag.cmd.push(function() {
            var slot = googletag.defineSlot('/78059622/Responsive-Article-MPU-1',[300,250], slotSidebarMPU1).addService(googletag.pubads());
            googletag.display(slotSidebarMPU1);
            googletag.pubads().refresh([slot]);
          });
          //AD: MPU2-innerWidth
          googletag.cmd.push(function() {
            var slot = googletag.defineSlot('/78059622/Responsive-Article-MPU-2',[300,250], slotSidebarMPU2).addService(googletag.pubads());
            googletag.display(slotSidebarMPU2);
            googletag.pubads().refresh([slot]);
          });
          //AD: MPU-HalfPage-innerWidth
          googletag.cmd.push(function() {
            var slot = googletag.defineSlot('/78059622/Desktop_Article_Page_RHS_300x600',[300,600], slotSidebarMPUHalfPage).addService(googletag.pubads());
            googletag.display(slotSidebarMPUHalfPage);
            googletag.pubads().refresh([slot]);
          });
        }
        //AD: Leaderboard Footer
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/78059622/Responsive-Article-Leaderboard-footer',[[970,250],[320,100]], slotLeaderboardFooter).defineSizeMapping(mappingMobLeadfullads).addService(googletag.pubads());
          googletag.display(slotLeaderboardFooter);
          googletag.pubads().refresh([slot]);
        });
        //AD: InArticle MPU1
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/23110992253/InArticle-300x250-1',[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid'], slotInArticleMPU1).defineSizeMapping(mappingMPUInArticleInfinite).addService(googletag.pubads());
          googletag.display(slotInArticleMPU1);
          googletag.pubads().refresh([slot]);
        });
        //AD: InArticle MPU2
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/23110992253/InArticle-300x250-2',[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid'], slotInArticleMPU2).defineSizeMapping(mappingMPUInArticleInfinite).addService(googletag.pubads());
          googletag.display(slotInArticleMPU2);
          googletag.pubads().refresh([slot]);
        });
        //AD: InArticle MPU3
        googletag.cmd.push(function() {
          var slot = googletag.defineSlot('/23110992253/InArticle-300x250-3',[[300,250],[336,280],[300, 100],[250,250],[300,75],[300,50],[320,50],'fluid'], slotInArticleMPU3).defineSizeMapping(mappingMPUInArticleInfinite).addService(googletag.pubads());
          googletag.display(slotInArticleMPU3);
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
