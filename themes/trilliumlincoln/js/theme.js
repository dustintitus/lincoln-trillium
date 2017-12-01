(function($) {
	// $(document).ready(function() {
		// changeImgSizes();
	// });

	function changeElementSizes(elem,delimer){
		if(!isNumeric(delimer) || delimer === 1 ) return;
		var extrema = 'min';
		if(delimer > 1) extrema = 'max';
		elem.style[extrema+'Width'] = parseInt(elem.width/delimer)+'px';
	}

	function changeImgSizes(){
		var elems = $('[class*=size-img-]');

		elems.each(function(i,elem){
			var classes = elem.classList;
			var delimer;
			var re = /(.*?)size-img-(.*?)/;
			for(var i = 0; i < classes.length; i++){
				if(re.test(classes[i])){
					delimer = +(classes[i].replace( /^\D+/g, ''));
					break;
				}
			}

			if( typeof delimer !== 'undefined'){
				if(elem.tagName == 'IMG'){
					changeElementSizes(elem,delimer);
				} else {
					var imgs = elem.querySelectorAll('img');
					[].forEach.call(imgs, function(elem){
						changeElementSizes(elem,delimer);
					});
				}
			}
		});
	}

	function isNumeric(n) {
		return !isNaN(parseFloat(n)) && isFinite(n);
	}
	function tryPopup() {		
     if( !Cookies.get('newsitepopup') ) {
     	Cookies.set('newsitepopup', 'true', { expires: 7, path: '/' });
     	$('#newSite').modal();
     }
	}
	$(document).ready(function()
	{
	  setTimeout(tryPopup, 5000);
	});

})(jQuery);