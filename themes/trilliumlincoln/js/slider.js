(function ($, Drupal) {
	Drupal.behaviors.slider = {
		attach: function (context, settings) {
			$('.slider', context).once('slider').each(function (i,el) {

				var slider = $(this),
					item = slider.find('.item'),
					header = $('.header'),
					$window = $(window),
					images = slider.find('.item img');

				var opt = {
					slides: '.item',
					fx: 'fadeout',
					next: ".slider-control.next",
					prev: ".slider-control.prev",
					timeout: 0,
				}	

				slider.cycle(opt);			

				function coverImg(slide_H){
					var containerHeight = $window.outerHeight();
					var containerWidth = $window.outerWidth();
					images.each(function(i,elem){
						var item = {
							elem: $(elem),
							width: parseInt(elem.getAttribute('width')),
							height: parseInt(elem.getAttribute('height')),
						};

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
					coverImg(slide_H);
				}).trigger('resize');

				

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