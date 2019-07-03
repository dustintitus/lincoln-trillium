(function ($, Drupal) {
  Drupal.behaviors.compare_car = {
    attach: function (context, settings) {
      $('.compare-car-container', context).once('compare-car').each(function () {
        var id = $(this).find('.compare-car-field').attr('id');
        $(this).find('label').attr('for', id);
        $(this).find('.compare-car-field').on('change', function(event) {
          //var id = $(this).attr('data-id');
          $(this).parents('.compare-car-container').find('.use-ajax').trigger('click');
          var compare = false;
          if ( $(this).prop( "checked" ) ){
            compare = true;
          }

          console.log(compare);
        });
      });

    }
  }
})(jQuery, Drupal);