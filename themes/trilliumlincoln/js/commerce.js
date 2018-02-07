(function ($, Drupal) {
  Drupal.behaviors["product-carousel"] = {
    attach: function (context, settings) {
      $('.product-carousel', context).once('product-carousel').each(function (i,el) {
        var self = $(this);

        var pager   = self.find('.product-carousel-pager');
        var picture = self.find('.product-carousel-picture');
        var visibleSlide = 4;
        var items = pager.find('.field--item');

        var pic = new Image();
        var previewImg = pager.find('.field--item > a')[0];
        
        pic.src = $(previewImg).attr('data-preview-url');
        $(pic).attr('data-orig-url', previewImg.href);
        picture.append(pic);

        var optionsPager = {
          fx: 'carousel',
          timeout: 0,
          slides: '.field--item',
          carouselVisible: visibleSlide,
          carouselFluid: true,
          allowWrap: false,
          next: ".product-carousel-control.next",
          prev: ".product-carousel-control.prev", 
          autoHeight: -1,
          log: false,
        };

        var size = getThumpSize();
        pager.innerHeight(size.height);
        items.innerHeight(size.height);
        if(items.length < visibleSlide){
          optionsPager.carouselVisible = items.length;
          optionsPager.carouselFluid = false;
          items.innerWidth(size.width);         
        } 

        if(items.length <= visibleSlide){
          $('.product-carousel-control').hide();
        }

        function getThumpSize(){
          var conteinerWidth = self.width();
          var itemWidth = parseInt(conteinerWidth/visibleSlide);
          var itemHeight = parseInt(142/190*itemWidth);
          return {
            width: itemWidth,
            height: itemHeight,
          };
        }

        pager.cycle(optionsPager);

        $('.cycle-slide a', pager).click(function(e){   
          e.preventDefault();   
          var index = pager.data('cycle.API').getSlideIndex(this.parentElement); 
          pager.cycle('goto', index);
        });

        pager.on( 'cycle-before', function( event, optionHash, outgoingSlideEl, incomingSlideEl ) {
          var activeEl = incomingSlideEl.querySelector('a');
          var src = $(activeEl).attr('data-preview-url');
          var srcOrig = activeEl.href;
          $(pic).fadeOut(100, function(){ pic.src = src; $(pic).attr('data-orig-url', srcOrig); }).fadeIn(200);
        });

        $(window).on('resize', function(){
          var size = getThumpSize();
          pager.innerHeight(size.height);
          items.innerHeight(size.height);
          if(items.length < visibleSlide){
            items.innerWidth(size.width);
            pager.width(items.length*size.width);
          } 
        });

        $("#modal-box .modal-content").on('click', function(event) {
          event.preventDefault();
          $('#modal-box').modal('hide');
        });

        picture.on('click', 'img', function(event) {
          event.preventDefault();
          var $modalBox = $("#modal-box");
          var $modalBody = $modalBox.find('.modal-body');
          $modalBody.empty();
          var origImg = new Image();
          origImg.src = $(this).attr('data-orig-url')

          $(origImg).appendTo($modalBody);
          $modalBox.modal({show:true});
          var modalBoxNav = $modalBox.find('.modal-nav');

          var slideCount = pager.data("cycle.opts").slideCount;
          var activeSlideNumber = 0;
          var $slides = items.find('a');

          if (slideCount > 1) {
            nextSlide = pager.data("cycle.opts").nextSlide;
            var currSlide = pager.data("cycle.opts").currSlide;
            prevSlide = pager.data("cycle.opts").currSlide - 1;
            if (currSlide == 0) {
              prevSlide = slideCount - 1;
            }

            $(modalBoxNav, context).once('modal-nav').each(function (i,el) {
              modalBoxNav.on('click', 'a', function(event) {
                event.preventDefault();
                if ($(this).hasClass('prev')) {
                  activeSlideNumber = prevSlide;
                }
                else{
                  activeSlideNumber = nextSlide;
                }
                if (activeSlideNumber == 0) {
                  nextSlide = activeSlideNumber + 1;
                  prevSlide = slideCount - 1;
                }
                else if (activeSlideNumber == slideCount - 1) {
                  nextSlide = 0;
                  prevSlide = activeSlideNumber - 1;
                }
                else{
                  nextSlide = activeSlideNumber + 1;
                  prevSlide = activeSlideNumber - 1;
                }

                var activeSlide = $modalBody.find('img');
                $(activeSlide).fadeOut(200, function(){ activeSlide.attr('src', $slides[activeSlideNumber].href) }).fadeIn(200);
              });
            });
          }
          else{
            modalBoxNav.hide();
          }
        });
      });
    }
  };

  var nextSlide = 0;
  var prevSlide = 0;

  Drupal.behaviors.view_product_list = {
    attach: function (context, settings) {
      $('.view-product-list', context).once('view_product_list').each(function (i,el) {
        var self = $(this);
        var cookieClass = getCookie('productListViewMode');

        // if( $(window).width() >= 768 && cookieClass === "list" || cookieClass === "grid") self.removeClass('list grid').addClass(cookieClass);
        $(window).on('resize', function(){
          cookieClass = getCookie('productListViewMode');
          if( getWindowWidth() < 768 ){
            if(self.hasClass("list")) {
              self.removeClass('list').addClass("grid");  
            }
          } 
          if( getWindowWidth() >= 768 && (cookieClass === "list" || cookieClass === "grid")){
            if(!self.hasClass(cookieClass)) {
              self.removeClass('list grid').addClass(cookieClass);  
            }
          } 
        }).trigger('resize');;

        var btnGrid = self.find('.product-list--grid');
        var btnList = self.find('.product-list--list');
        var content = self.find('.view-content');

        btnGrid.on('click', function(e){
          self.addClass('grid').removeClass('list');
          setViewMode('grid');
        });

        btnList.on('click', function(e){
          self.addClass('list').removeClass('grid');
          setViewMode('list');
        });

        function setViewMode(viewMode){
          setCookie('productListViewMode', viewMode, {expires: 86400});
        }

      });
    }
  };

  Drupal.behaviors.product_single = {
    attach: function (context, settings) {
      var subject = $('.product-single', context).find('.calt-h');
      var predicate = $('.product-single', context).find('.product-carousel-picture-wrapper');
      if( getWindowWidth() >= 768 ){
        subject.css('min-height', predicate.outerHeight());
      } else {
        subject.css('height', 'auto').css('min-height', '100%');
      }

      $(window).on('resize', function(){
        if( getWindowWidth() >= 768 ){
          subject.css('min-height', predicate.outerHeight());
        } else {
          subject.css('height', 'auto').css('min-height', '100%');
        }
      });

    }
  }


  function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
      ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }


  function setCookie(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
      var d = new Date();
      d.setTime(d.getTime() + expires * 1000);
      expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
      options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
      updatedCookie += "; " + propName;
      var propValue = options[propName];
      if (propValue !== true) {
        updatedCookie += "=" + propValue;
      }
    }

    document.cookie = updatedCookie;
  }


  function deleteCookie(name) {
    setCookie(name, "", {
      expires: -1
    })
  }

  function getWindowWidth() { 
    return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
  }


})(jQuery, Drupal);