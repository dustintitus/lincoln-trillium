/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.calculator = {
    attach: function (context, settings) {
      $('.trilliumlincoln-utility-payment-calculator-form', context).once('calculator-form').each(function () {
        var residual = parseFloat($(this).find('input[name=residual]').val());
        var price = parseFloat($(this).find('input[name=price]').val()) + 449;
        var $totalLease = $(this).find('.total-lease span');
        //Lease section
        $(this).on('change paste keyup','#edit-lease-term, #edit-lease-cash-down', function(event) {
          var $leaseTerm = $(this).parents('#edit-lease').find('#edit-lease-term');
          var leaseRate = parseFloat($leaseTerm.find('option:selected').attr('data-lease-rate'));
          var leaseTerm = parseInt($leaseTerm.val());
          var leaseCashDown = parseFloat($(this).parents('#edit-lease').find('#edit-lease-cash-down').val()) / 1.13;
          var $pmt = $(this).parents('#edit-lease').find('#edit-pmt');

          var capitalizedCost = price - leaseCashDown;
          var pmt = '00,000';
          var biweeklyLeasePmt = '000';
          
          if (capitalizedCost > 0 && leaseCashDown >= 0) {
            var amortAmt = capitalizedCost - residual;
            var basePmt = amortAmt/leaseTerm;
            var moneyFactor = (leaseRate/24)/100;
            var interestCost = (capitalizedCost + residual) * moneyFactor;
            pmt = (basePmt + interestCost).toFixed(2);
            biweeklyLeasePmt = ((pmt * 12) / 26).toFixed(2);
          }
          $totalLease.text('$' + biweeklyLeasePmt);
          $pmt.val(pmt);
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);