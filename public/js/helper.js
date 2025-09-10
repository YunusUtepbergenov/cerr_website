(function ($) {
  'use strict';

  var imJs = {
      m: function (e) {
          imJs.d();
          imJs.methods();
      },
      d: function (e) {
          this._window = $(window),
          this._document = $(document),
          this._body = $('body'),
          this._html = $('html')
      },
      methods: function (e) {
          imJs.vedioActivation();
          imJs.myAudio(); 
          imJs.stickySidebar(); 
          imJs.searchOpton(); 
      },
      vedioActivation: function (e) {
        $(document).ready(function(){
          $('.popup-youtube, .popup-vimeo').magnificPopup({
            disableOn: 700,
            type: 'iframe',
            mainClass: 'mfp-fade',
            removalDelay: 160,
            preloader: false,
            fixedContentPos: false
          });
        });
      },
      myAudio: function (e){
        $(document).ready (function(){
          function myFunction() {
            var x = document.getElementById("myAudio").duration;
           
          };
          $(function() {
            $('audio').audioPlayer();
        });
        
        });
      },

       // side menu desktop
       stickySidebar: function(e) {
        if ($("#sidebar").length) {
        var top = $('#sidebar').offset().top - parseFloat($('#sidebar').css('marginTop').replace(/auto/, 0));
        var footTop = $('#footer').offset().top - parseFloat($('#footer').css('marginTop').replace(/auto/, 0));
      
        var maxY = footTop - $('#sidebar').outerHeight();
      
        $(window).scroll(function(evt) {
          var y = $(this).scrollTop();
          if (y > top) {
            if (y < maxY) {
              $('#sidebar').addClass('fixed').removeAttr('style');
            } else {
              $('#sidebar').removeClass('fixed').css({
                position: 'absolute',
                top: (maxY - top) + 'px'
              });
            }
          } else {
            $('#sidebar').removeClass('fixed');
          }
        });
      }
      },
      // Search Bar show & hide
      searchOpton:function(){
        $(document).on('click', '.search-icon', function () {
          $(".search-input-area").addClass("show");
        });
        $(document).on('click', '.search-input-area input', function () {
          $(".search-input-area").addClass("show");
        });
        $(document).on('click', '.search-input-inner before', function () {
          $(".search-input-area").addClass("show");
        });
        $('html').click(function (e) {
          if (!$(e.target).hasClass('show')) {
            $(".search-input-area").removeClass("show");
          }
          $(document).on('click', '.search-close-icon', function () {
            $(".search-input-area").removeClass("show");
          });
        });
      },
      
  }

  $(window).on("scroll", function() {
    var ScrollBarPostion = $(window).scrollTop();
    if (ScrollBarPostion > 150) {
      $(".echo-header-area").addClass("header-sticky");      
    } else {
      $(".echo-header-area").removeClass("header-sticky");
      $(".echo-header-area .echo-header-top").removeClass("remove-content");     
    }
  });

  	/* magnificPopup img view */
	$('.echo-hm2-img-popup').magnificPopup({
		type: 'image',
		gallery: {
			enabled: true
		}
	});

  // Day 
  var rts_date = $('#echo-date');
  if(rts_date.length){
    const weekday = ["Sun","Mon","Tues","Wed","Thur","Fri","Sat"];
    const month = ["Jan","Feb","March","April","May","June","July","August","Sept","Oct","Nov","Dec"];
    const d = new Date();
    let day = weekday[d.getUTCDay()];
    let mdate = d.getDate();
    const year = d.getFullYear().toString().substr(2, 2);
    let mname = month[d.getMonth()];
    document.getElementById("echo-date").innerHTML = '<strong>'+day+'</strong>'+ ', ' + mdate+ ' ' + mname + '  '+ year ;
  }

  // stickySidebar
    if (typeof $.fn.theiaStickySidebar !== "undefined") {
      $(".sticky-coloum-wrap .sticky-coloum-item").theiaStickySidebar({
        additionalMarginTop: 130,
      });
    }

  
    var rts_light = $('.rts-dark-light');
        if(rts_light.length){
        var toggle = document.getElementById("rts-data-toggle");
        var storedTheme = localStorage.getItem('echo-theme') || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
        if (storedTheme)
            document.documentElement.setAttribute('data-theme', storedTheme)
            toggle.onclick = function() {
            var currentTheme = document.documentElement.getAttribute("data-theme");
            var targetTheme = "light";

            if (currentTheme === "light") {
                targetTheme = "dark";
            }
            document.documentElement.setAttribute('data-theme', targetTheme)
            localStorage.setItem('echo-theme', targetTheme);
        };
    }


  var win=$(window);
  var totop = $('.scroll-top-btn');    
  win.on('scroll', function() {
      if (win.scrollTop() > 150) {
          totop.fadeIn();
      } else {
          totop.fadeOut();
      }
  });
  totop.on('click', function() {
      $("html,body").animate({
          scrollTop: 0
      }, 500)
  });

  // Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
  imJs.m();
})(jQuery, window)