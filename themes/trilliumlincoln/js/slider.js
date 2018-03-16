(function ($, Drupal) {
	Drupal.behaviors.slider = {
		attach: function (context, settings) {
			$( document ).ready(function() {
				$('.slider', context).once('slider').each(function (i,el) {

					var slider = $(this),
						item = slider.find('.item'),
						item_caption = item.find('> .list'),
						header = $('.header'),
						$window = $(window),
						images = slider.find('.item picture img');						

					var opt = {
						slides: '.item',
						fx: 'fadeout',
						next: ".slider-control.next",
						prev: ".slider-control.prev",
						pager: ".slider-pager",
						timeout: 0,
						log: false,
					}	

					if(item.length < 2){
						$('.slider-control').hide();
						$('.slider-pager').hide();
					}	

					slider.cycle(opt);

					function modifiedEachItems() {
						item.each(function(i,elem) {	
							if ( getWindowWidth() < 768 ){
								images[i].style.bottom = (item_caption[i].clientHeight)+'px';														
							} else {
								images[i].style.bottom = null;
							}
							$(images[i]).resizeToParent({parent: '.slider'});
						});						
					}

					function coverImg(slide_H){
						var containerHeight = $window.outerHeight();
						var containerWidth = $window.outerWidth();
						images.each(function(i,elem){
							var item = {
								elem: $(elem),
								width: parseInt(elem.dataset.width),
								height: parseInt(elem.dataset.height),
							};

							if( getWindowWidth() < 768 ){
								item.width = parseInt(elem.dataset.modileWidth);
								item.height = parseInt(elem.dataset.mobileHeight);
							}

							if ( getWindowWidth() / item.width > slide_H / item.height ) {
								item.elem.css({
									width: '100%',
									height: 'auto',
								});
							} else {
								item.elem.css({
									width: 'auto',
									height: '100%',
								});
							}
						});
					}
			
					$window.on( 'resize', function() {
						slide_H = +getWindowHeight() - +header.height();
						slider.height(slide_H);
						// coverImg(slide_H);
						modifiedEachItems();
					}).trigger('resize');

					

				});
			});
		}
	}

	function getWindowWidth() { 
		return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	}
	function getWindowHeight(){
		return window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	}

})(jQuery, Drupal);