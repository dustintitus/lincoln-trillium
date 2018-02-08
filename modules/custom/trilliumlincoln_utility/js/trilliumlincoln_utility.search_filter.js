/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.search_filter = {
    attach: function (context, settings) {
      $('#views-exposed-form-product-list-page-1', context).once('search-filter').each(function () {
        var $checkbox = $(this).find('input[type=checkbox]');

        $checkbox.on('change', function(event) {
          if ( $(this).prop( "checked" ) ){
            var checkboxValue = $(this).val();
            if (checkboxValue == 'all') {
              $checkbox.filter("[value!='all']").prop( "checked", false );
            }
            else{
              $checkbox.filter("[value='all']").prop( "checked", false );
            }
          }
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);