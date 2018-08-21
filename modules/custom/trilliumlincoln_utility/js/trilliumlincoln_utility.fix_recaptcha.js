/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.fix_recaptcha = {
    attach: function (context, settings) {
      if ('grecaptcha' in window && context !== document) {
        $('.g-recaptcha:empty', context).each(function () {
          grecaptcha.render(this, $(this).data());
        });
      }
    }
  };

})(jQuery, Drupal, drupalSettings);