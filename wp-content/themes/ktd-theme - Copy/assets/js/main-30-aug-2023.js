function survey_click_func() {
    jQuery(".textz_block").hide(), jQuery(".textz_show").show(), setTimeout(function () {
        window.location.href = "https://www.khaleejtimes.com"
    }, 1e3)
}
jQuery.noConflict(),
    function (a) {
        window.navigator.userAgent.match(/MSIE|Trident/) && a("body").addClass("isIE"), a(document).ready(function () {
            a(".hamburger_menu_nf span").on("click", function () {
                var b = a(".sticky-div").outerHeight(),
                    c = 0;
                992 > a(".sticky-div").outerWidth() ? (c = 0, a(".nav-overlay-nf").css({
                    top: b + c + "px"
                })) : a(".nav-overlay-nf").css({
                    top: b
                }), a(".nav-overlay-nf").fadeToggle(200, function () {}), a(this).toggleClass("btn-open").toggleClass("btn-close"), a(this).hasClass("btn-close") && a(this).html('<i class="fa fa-times"></i>') && utag.link({
                    event_action: "menu click",
                    event_category: "Menu",
                    event_label: "click ",
                    event_type: "",
                    tealium_event: "menu_click"
                }), a(this).hasClass("btn-open") && a(this).html('<i class="fas fa-bars"></i>') && utag.link({
                    event_action: "menu closed",
                    event_category: "Menu",
                    event_label: "click ",
                    event_type: "",
                    tealium_event: "menu_closed"
                })
            }), header = document.querySelector(".header-row.secondary-bg"), stickyElem = document.querySelector(".sticky-div"), mega = document.querySelector(".nav-overlay-nf"), null != header && null != stickyElem && null != mega && (stickyElemBottom = stickyElem.getBoundingClientRect().bottom, stickyElemPos = stickyElem.getBoundingClientRect().bottom + window.pageYOffset, currStickyPos = header.getBoundingClientRect().bottom + window.pageYOffset + 50, window.onscroll = function () {
                stickyElemPos2 = stickyElem.getBoundingClientRect().bottom + window.pageYOffset, stickyElemPos3 = stickyElem.getBoundingClientRect().bottom, window.pageYOffset < currStickyPos && window.pageYOffset > stickyElemBottom ? mega.style.top = "-" + stickyElemPos2 + "px" : mega.style.top = stickyElemPos3 + "px", window.pageYOffset > currStickyPos ? (stickyElem.classList.add("is-sticky"), stickyElem.classList.add("shadow-sm"), mega.classList.add("is-sticky-enabled")) : (stickyElem.classList.remove("is-sticky"), stickyElem.classList.remove("shadow-sm"), mega.classList.remove("is-sticky-enabled"))
            })
        });
        var k = {
            navBarTravelling: !1,
            navBarTravelDirection: "",
            navBarTravelDistance: 150
        };
        document.documentElement.classList.remove("no-js"), document.documentElement.classList.add("js");
        var d = document.getElementById("pnAdvancerLeft"),
            e = document.getElementById("pnAdvancerRight"),
            b = document.getElementById("pnProductNav"),
            c = document.getElementById("pnProductNavContents");
        if (null != b && null != c) {
            b.setAttribute("data-overflowing", m(c, b));
            var l = !1;
            b.addEventListener("scroll", function () {
                window.scrollY, l || window.requestAnimationFrame(function () {
                    b.setAttribute("data-overflowing", m(c, b)), l = !1
                }), l = !0
            })
        }

        function m(g, h) {
            var a = h.getBoundingClientRect(),
                b = Math.floor(a.right),
                c = Math.floor(a.left),
                d = g.getBoundingClientRect(),
                e = Math.floor(d.right),
                f = Math.floor(d.left);
            return c > f && b < e ? "both" : f < c ? "left" : e > b ? "right" : "none"
        }
        null != d && d.addEventListener("click", function () {
            if (!0 !== k.navBarTravelling) {
                if ("left" === m(c, b) || "both" === m(c, b)) {
                    var a = b.scrollLeft;
                    a < 2 * k.navBarTravelDistance ? c.style.transform = "translateX(" + a + "px)" : c.style.transform = "translateX(" + k.navBarTravelDistance + "px)", c.classList.remove("pn-ProductNav_Contents-no-transition"), k.navBarTravelDirection = "left", k.navBarTravelling = !0
                }
                b.setAttribute("data-overflowing", m(c, b))
            }
        }), null != e && e.addEventListener("click", function () {
            if (!0 !== k.navBarTravelling) {
                if ("right" === m(c, b) || "both" === m(c, b)) {
                    var a = Math.floor(c.getBoundingClientRect().right - b.getBoundingClientRect().right);
                    a < 2 * k.navBarTravelDistance ? c.style.transform = "translateX(-" + a + "px)" : c.style.transform = "translateX(-" + k.navBarTravelDistance + "px)", c.classList.remove("pn-ProductNav_Contents-no-transition"), k.navBarTravelDirection = "right", k.navBarTravelling = !0
                }
                b.setAttribute("data-overflowing", m(c, b))
            }
        }), c.addEventListener("transitionend", function () {
            var a = window.getComputedStyle(c, null),
                d = Math.abs(parseInt((a.getPropertyValue("-webkit-transform") || a.getPropertyValue("transform")).split(",")[4]) || 0);
            c.style.transform = "none", c.classList.add("pn-ProductNav_Contents-no-transition"), "left" === k.navBarTravelDirection ? b.scrollLeft = b.scrollLeft - d : b.scrollLeft = b.scrollLeft + d, k.navBarTravelling = !1
        }, !1), c.addEventListener("click", function (a) {
            [].slice.call(document.querySelectorAll(".pn-ProductNav_Link")).forEach(function (a) {
                a.setAttribute("aria-selected", "false")
            }), a.target.setAttribute("aria-selected", "true")
        });
        var f = a(".video-player"),
            i = a(".video-player video"),
            j = a(".movie-preview iframe"),
            g = f.width(),
            h = a(".movie-preview").width();
        i.width(g).height(.5625 * g), j.width(h).height(.5625 * h), a(".tracks").height(f.height()), a(window).resize(function () {
            g = f.width(), niframewidth = a(".movie-preview").width(), j.width(niframewidth).height(.5625 * niframewidth), i.width(g).height(.5625 * g), a(".tracks").height(f.height())
        }).resize();
        var n = a(".takeover"),
            o = a(".takeover article"),
            p = 0,
            q = 0;
        a.fn.isInViewport = function () {
            var c = (a(window).width() - a(this).parent().outerWidth()) / 2,
                b = a(this);
            if (0 != p) {
                var e = old_width - b.parent().width();
                q = b.parent().width() + c - e
            } else p = b.parent().width(), q = b.parent().width() + c, old_width = p;
            var d = b.offset().left,
                f = d + b.width();
            return 0 <= d && f <= q
        }, a(".image-slider").length && new Swiper(".image-slider", {
            pagination: {
                el: ".swiper-pagination",
                type: "progressbar"
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev"
            }
        }), a(document).ready(function () {
            a(".sub-menu").each(function () {
                var b = a(this),
                    c = b.parent().height(),
                    d = b.data("id"),
                    e = 0;
                992 > a(window).width() && (a("#" + d).css({
                    top: c + "px"
                }), b.find("li").each(function () {
                    a(this).isInViewport() || (a(this).detach().appendTo("#" + d), e++)
                }), a("#" + d).css({
                    top: c + "px"
                }), b.find(".toggle-mobile-sub-menu").length || e > 0 && a('<span class="toggle-mobile-sub-menu"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></span>').appendTo(b))
            }), a(document).on("click", ".cinema-location-filter", function (c) {
                c.preventDefault();
                var b = a(this).data("target");
                a(".cinema-location-filter").removeClass("active"), a(this).addClass("active"), a(".cinema-location").hide(), b ? a('.cinema-location[data-location="' + b + '"]').fadeIn(1e3) : a(".cinema-location").fadeIn(1e3)
            }), a(document).on("click", ".movie-language-filter", function (b) {
                b.preventDefault();
                var c = a("#movie-listings"),
                    e = c.find(".loader"),
                    d = a(this).attr("href");
                a(".movie-language-filter").removeClass("active"), a(this).addClass("active"), jQuery.ajax({
                    url: d,
                    type: "POST",
                    beforeSend: function () {
                        e && e.show()
                    },
                    complete: function () {
                        e && e.hide()
                    },
                    success: function (a) {
                        a && a.data && c.fadeOut(200, function () {
                            c.html(a.data.html), c.fadeIn(1e3)
                        })
                    }
                })
            }), a(document).on("click", "#movie-listings a.page-link", function (b) {
                b.preventDefault();
                var c = a(".movie-language-filter.active").data("target"),
                    d = a("#movie-listings"),
                    f = d.find(".loader"),
                    e = a(this).attr("href");
                jQuery.ajax({
                    url: e,
                    type: "POST",
                    data: {
                        language: c
                    },
                    beforeSend: function () {
                        f && f.show()
                    },
                    complete: function () {
                        f && f.hide()
                    },
                    success: function (a) {
                        a && a.data && d.fadeOut(200, function () {
                            d.html(a.data.html), d.fadeIn(1e3)
                        })
                    }
                })
            }), a(document).on("click", ".photo-gallery .image-nav a", function (d) {
                d.preventDefault();
                var c = a(this),
                    b = a(".photo-gallery .image-thumbnail img"),
                    e = c.data("target-img");
                c.parent().parent().find(".fps-item-single img"), b.length && (b.animate({
                    opacity: 0
                }, function () {
                    b.attr("src", e)
                }), b.animate({
                    opacity: 1
                }, 300))
            })
        }), a(window).resize(function () {
            var a = n.width();
            o.width(a).height(.75 * a), jQuery(".sub-menu").each(function () {
                var b = 0,
                    c = jQuery(".sub-menu").width();
                jQuery(".sub-menu li").each(function () {
                    b += parseInt(jQuery(this).outerWidth(), 10)
                }), console.log(c + " => " + b);
                var a = jQuery(this),
                    d = a.parent().height(),
                    e = a.data("id");
                c < b && (1024 > jQuery(window).width() && jQuery("#" + e).css({
                    top: d + "px"
                }), a.find("li").each(function () {
                    var a = jQuery(this);
                    a.isInViewport() || a.detach().appendTo("#" + e)
                }), a.find(".toggle-mobile-sub-menu").length || jQuery('<span class="toggle-mobile-sub-menu"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></span>').appendTo(a))
            })
        }), a(document).on("click", ".toggle-mobile-sub-menu", function () {
            var b = a(this).parent().parent().find(".mobile-sub-menu");
            a(b).fadeToggle(200, function () {})
        }), a("#ifrm").length && (document.getElementById("ifrm").onload = function () {
            var e, a, f, c, d, b;
            e = this.id, a = document.getElementById(e), console.log(a.contentWindow), f = a.contentWindow.document ? a.contentWindow.document : a.contentDocument, a.style.visibility = "hidden", a.style.height = "10px", a.style.height = (d = (c = (c = f) || document).body, b = c.documentElement, Math.max(d.scrollHeight, d.offsetHeight, b.clientHeight, b.scrollHeight, b.offsetHeight) + 4 + "px"), a.style.visibility = "visible"
        });
        var r = Math.floor(jQuery(".article-paragraph-wrapper").find("p").length / 2.5);
        jQuery.each(jQuery(".article-paragraph-wrapper p"), function (a, b) {
            a == r && jQuery(b).after( /* "<div class='mpu-ad-unit ad-unit d-flex justify-content-center mt-3'><div id='div-gpt-ad-1613481322643-0'><script>googletag.cmd.push(function() { googletag.display('div-gpt-ad-1613481322643-0'); });</script></div>" */ )
        })
    }(jQuery), jQuery(window).on("load", function () {
        jQuery(".sub-menu").each(function () {
            var b = 0,
                c = jQuery(".sub-menu").width();
            jQuery(".sub-menu li").each(function () {
                b += parseInt(jQuery(this).outerWidth(), 10)
            }), console.log(c + " => " + b);
            var a = jQuery(this),
                d = a.parent().height(),
                e = a.data("id");
            c < b && (1024 > jQuery(window).width() && jQuery("#" + e).css({
                top: d + "px"
            }), a.find("li").each(function (b) {
                var a = jQuery(this);
                a.isInViewport() || a.detach().appendTo("#" + e)
            }), a.find(".toggle-mobile-sub-menu").length || jQuery('<span class="toggle-mobile-sub-menu"><i class="fa fa-ellipsis-h" aria-hidden="true"></i></span>').appendTo(a))
        })
    })
