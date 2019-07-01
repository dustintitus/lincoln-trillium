(function ($, Drupal) {
  Drupal.behaviors.compare_car = {
    attach: function (context, settings) {
      $('.compare-car-container', context).once('compare-car').each(function () {
        $(this).find('.compare-car-field').on('change', function(event) {
          var id = $(this).attr('data-id');
          var compare = false;
          if ( $(this).prop( "checked" ) ){
            compare = true;
          }

          console.log(compare);
        });
      });

      //$('.compare-car-container', context).once('compare-car', function () {

        // $('.compare-car-field').on('change', function(event) {
        // console.log($(this).val());
        // });
      //});
    }
  }
})(jQuery, Drupal);