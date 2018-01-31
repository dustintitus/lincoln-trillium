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
        pic.src = pager.find('.field--item > a')[0].href;
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
          var src = incomingSlideEl.querySelector('a').href; 
          $(pic).fadeOut(100, function(){ pic.src = src }).fadeIn(200);
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


        picture.on('click', 'img', function(event) {
          event.preventDefault();
          $("#modal-box .modal-body").empty();
          $($(this).parent().html()).appendTo("#modal-box .modal-body");
          $("#modal-box").modal({show:true});

          //if (true) {}

          //var modalBoxImgages = $('.product-carousel').find('.cycle-slide > a');
          //console.log(pic);

        });






      });
    }
  };

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
        subject.height(predicate.outerHeight());
      } else {
        subject.css('height', 'auto')
      }

      $(window).on('resize', function(){
        if( getWindowWidth() >= 768 ){
          subject.height(predicate.outerHeight());
        } else {
          subject.css('height', 'auto')
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