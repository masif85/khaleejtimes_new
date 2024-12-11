jQuery(document).ready(function( $ ) {

	var windowWidth = $(window).width();
	var isTablet = navigator.userAgent.match(/iPad|Android/i);

	/* Mobile Touch Functionality for Menus so that touch replicates hover effect */
  if (!!isTablet) {
		$('.headerleaderboard.advertisement.right').addClass("tablet");
	}

	if (windowWidth < 667 || !!isTablet) {
		$(".hamburger-menu a.dropdown-toggle").one("click", false, function(e){
			if ("ontouchstart" in document.documentElement) {
				e.preventDefault();
			}
		});
	}

	$(document).on('click', '#top-bar .hamburger-menu ul .dropdown-menu', function (e) {
		e.stopPropagation();
	});

	if (windowWidth < 667 || !!isTablet) {
		$("#sidebarMenu a.dropdown-toggle").one("click", false, function(e){
			if ("ontouchstart" in document.documentElement) {
			  e.preventDefault();
			}
		});
	}

	if (windowWidth < 667 || !!isTablet) {
		$(".main-navigation a.dropdown-toggle").one("click", false, function(e){
			if ("ontouchstart" in document.documentElement) {
			  e.preventDefault();
			}
		});
	}
	/* END Mobile Toggle Code */


	/* Move Logo to Top Bar on Scroll down for Mobile */
  var moveLogoOnScrollOffset = $('.main-navigation').offset().top;

  $(window).scroll(function () {
      var currentScroll = $(window).scrollTop();
      if (currentScroll >= moveLogoOnScrollOffset) {
		//$('.sidebarmenu.top-bar.no-top-image.top_bar_logo_on_scroll_user_info').hide();
		$('.top_bar_logo_on_scroll_image').show();
      } else {
		//$('.sidebarmenu.top-bar.no-top-image.top_bar_logo_on_scroll_user_info').show();
		$('.top_bar_logo_on_scroll_image').hide();
      }
	});
	/* End Move Logo Code */	

	/* Mobile Search Open/Close Form on Click */
	$( ".submit.mobile-search-icon" ).click(function() {
			$( ".submit.mobile-search-icon" ).addClass("hidden");
			$( ".main-nav-mobile-search.hidden" ).removeClass("hidden");
	});

	$(".sidebar-menu-search-above").prependTo("#sidebarMenu");
	$(".sidebar-menu-search-below").appendTo("#sidebarMenu");
	$(".sidebar_menu_logo").appendTo("#sidebarMenu");
	$(".sidebarMenuCloseButton").prependTo("#sidebarMenu");
	$(".sidebar_menu_logo").show()	
	$(".sidebarMenuCloseButton").show()	
	$(".sidebar-menu-search-above").show()
	$(".sidebar-menu-search-below").show()	

});

function toggleDropdown() {
	var dropdownMenuButton = document.getElementById("dropdownMenuButton");
	var dropdownDiv = document.getElementById("sidemenuDim");
	if ( dropdownMenuButton.classList.contains("closed") ){
		dropdownMenuButton.classList.remove("closed");
		dropdownMenuButton.classList.add("open");
		dropdownDiv.classList.remove("closed");
		dropdownDiv.classList.add("open");
	} else if (dropdownMenuButton.classList.contains("open")) {
		dropdownMenuButton.classList.remove("open");
		dropdownMenuButton.classList.add("closed");
		dropdownDiv.classList.remove("open");
		dropdownDiv.classList.add("closed");
	}
}

function toggleSidebar() {
	var dropdownMenuButton = document.getElementById("dropdownMenuButton");
	var dropdownDiv = document.getElementById("sidemenuDim");
	if ( dropdownMenuButton.classList.contains("closed") ){
		dropdownMenuButton.classList.remove("closed");
		dropdownMenuButton.classList.add("open");
		dropdownDiv.classList.remove("closed");
		dropdownDiv.classList.add("open");
	} else if (dropdownMenuButton.classList.contains("open")) {
		dropdownMenuButton.classList.remove("open");
		dropdownMenuButton.classList.add("closed");
		dropdownDiv.classList.remove("open");
		dropdownDiv.classList.add("closed");
	}
}


function openNav(sidebarWidth) {
	document.getElementById("sidebarMenu").style.width = sidebarWidth + 'px';
	var sideMenuDiv = document.getElementById("sidemenuDim");
	sideMenuDiv.classList.remove("closed");
	sideMenuDiv.classList.add("open");
}

function closeNav() {
  document.getElementById("sidebarMenu").style.width = "0";
	var sideMenuDiv = document.getElementById("sidemenuDim");
	sideMenuDiv.classList.remove("open");
	sideMenuDiv.classList.add("closed");
}