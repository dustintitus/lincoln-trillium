/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.calculator = {
    attach: function (context, settings) {
      $('.trilliumlincoln-utility-payment-calculator-form', context).once('calculator-form').each(function () {
        var price = parseFloat($(this).find('input[name=price]').val());
        var msrp = parseFloat($(this).find('input[name=msrp]').val());
        var $totalLease = $(this).find('.total-lease span');
        var $totalFinance = $(this).find('.total-finance span');
        //Lease section
        $(this).on('change paste keyup','#edit-lease-term, #edit-lease-cash-down', function(event) {
          var $leaseTerm = $(this).parents('#edit-lease').find('#edit-lease-term');
          var leaseRate = parseFloat($leaseTerm.find('option:selected').attr('data-lease-rate'));
          var leaseTerm = parseInt($leaseTerm.val());
          var residual = parseFloat($leaseTerm.find('option:selected').attr('data-residual'));
          var leaseCashDown = parseFloat($(this).parents('#edit-lease').find('#edit-lease-cash-down').val()) / 1.13;
          if (isNaN(leaseCashDown) || leaseCashDown < 0) {
            leaseCashDown = 0;
          }

          var $pmt = $(this).parents('#edit-lease').find('#edit-pmt');
          var capitalizedCost = price - leaseCashDown;
          var biweeklyLeasePmt = 'Nan';
          
          if (capitalizedCost > 0 && leaseCashDown >= 0) {
            var newResidual = residual * msrp;
            var amortAmt = capitalizedCost - newResidual;
            var basePmt = amortAmt/leaseTerm;
            var moneyFactor = (leaseRate/24)/100;
            var interestCost = (capitalizedCost + newResidual) * moneyFactor;
            var pmt = (basePmt + interestCost).toFixed(2);
            biweeklyLeasePmt = '$' + ((pmt * 12) / 26).toFixed(2);
          }
          $totalLease.text(biweeklyLeasePmt);
        });

        //finance section
        $(this).on('change paste keyup','#edit-finance-term, #edit-finance-cash-down', function(event) {
          var $financeTerm = $(this).parents('#edit-finance').find('#edit-finance-term');
          var financeRate = parseFloat($financeTerm.find('option:selected').attr('data-finance-rate'));
          var financeTerm = parseInt($financeTerm.val());
          var financeCashDown = parseFloat($(this).parents('#edit-finance').find('#edit-finance-cash-down').val()) / 1.13;

          if (isNaN(financeCashDown) || financeCashDown < 0) {
            financeCashDown = 0;
          }

          var capitalizedCost = price - financeCashDown;
          var biweeklyFinancePmt = 'Nan';
          if (capitalizedCost > 0 && financeCashDown >= 0) {
            var partFirst = 1 + (financeRate/100)/12;
            var compoundInterest = capitalizedCost*Math.pow(partFirst, financeTerm);
            var basePmt = compoundInterest/financeTerm;
            biweeklyFinancePmt = '$' + ((basePmt * 12) / 26).toFixed(2);
          }

          $totalFinance.text(biweeklyFinancePmt);
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);