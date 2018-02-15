/**
 * @file
 */

(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.calculator = {
    attach: function (context, settings) {
      $('.trilliumlincoln-utility-payment-calculator-form', context).once('calculator-form').each(function () {
        var price = parseFloat($(this).find('input[name=price]').val());
        console.log("price: "+price);
        var msrp = parseFloat($(this).find('input[name=msrp]').val());
        console.log("msrp: "+msrp);
        var $totalLease = $(this).find('.total-lease span');
        console.log("totalLease: "+totalLease);
        var $totalFinance = $(this).find('.total-finance span');
        console.log("totalFinance: "+totalFinance);
        //Lease section
        $(this).on('change paste keyup','#edit-lease-term, #edit-lease-cash-down', function(event) {
          console.log("*** START LEASE CALCULATION");
        console.log("price: "+price);
        console.log("msrp: "+msrp);
        console.log("totalLease: "+totalLease);
        console.log("totalFinance: "+totalFinance);
          var $leaseTerm = $(this).parents('#edit-lease').find('#edit-lease-term');
          console.log("leaseTerm: "+leaseTerm);
          var leaseRate = parseFloat($leaseTerm.find('option:selected').attr('data-lease-rate'));
          console.log("leaseRate: "+leaseRate);
          var leaseTerm = parseInt($leaseTerm.val());
          console.log("leaseTerm: "+leaseTerm);
          var residual = parseFloat($leaseTerm.find('option:selected').attr('data-residual'));
          console.log("residual: "+residual);
          var leaseCashDown = parseFloat($(this).parents('#edit-lease').find('#edit-lease-cash-down').val()) / 1.13;
          console.log("leaseCashDown: "+leaseCashDown);
          if (isNaN(leaseCashDown) || leaseCashDown < 0) {
            leaseCashDown = 0;
            console.log("leaseCashDown: "+leaseCashDown);
          }

          var $pmt = $(this).parents('#edit-lease').find('#edit-pmt');
          console.log("pmt: "+pmt);
          var capitalizedCost = price - leaseCashDown;
          console.log("capitalizedCost: "+capitalizedCost);
          var biweeklyLeasePmt = 'Nan';
          console.log("biweeklyLeasePmt: "+biweeklyLeasePmt);
          
          if (capitalizedCost > 0 && leaseCashDown >= 0) {
            var newResidual = residual * msrp;
            console.log("newResidual: "+newResidual);
            var amortAmt = capitalizedCost - newResidual;
            console.log("amortAmt: "+amortAmt);
            var basePmt = amortAmt/leaseTerm;
            console.log("basePmt: "+basePmt);
            var moneyFactor = (leaseRate/24)/100;
            console.log("moneyFactor: "+moneyFactor);
            var interestCost = (capitalizedCost + newResidual) * moneyFactor;
            console.log("interestCost: "+interestCost);
            var pmt = (basePmt + interestCost).toFixed(2);
            console.log("pmt: "+pmt);
            biweeklyLeasePmt = '$' + ((pmt * 12) / 26).toFixed(2);
            console.log("biweeklyLeasePmt: "+biweeklyLeasePmt);
          }
          $totalLease.text(biweeklyLeasePmt);
          console.log("totalLease: "+totalLease);
          console.log("*** END LEASE CALCULATION");
        });

        //finance section
        $(this).on('change paste keyup','#edit-finance-term, #edit-finance-cash-down', function(event) {
          console.log("*** START FINANCE CALCULATION");
        console.log("price: "+price);
        console.log("msrp: "+msrp);
        console.log("totalLease: "+totalLease);
        console.log("totalFinance: "+totalFinance);
          var $financeTerm = $(this).parents('#edit-finance').find('#edit-finance-term');
          console.log("financeCashDown: "+financeCashDown);
          var financeRate = parseFloat($financeTerm.find('option:selected').attr('data-finance-rate'));
          console.log("financeCashDown: "+financeCashDown);
          var financeTerm = parseInt($financeTerm.val());
          console.log("financeCashDown: "+)financeCashDown;
          var financeCashDown = parseFloat($(this).parents('#edit-finance').find('#edit-finance-cash-down').val()) / 1.13;
          console.log("financeCashDown: "+financeCashDown);

          if (isNaN(financeCashDown) || financeCashDown < 0) {
            financeCashDown = 0;
          console.log("financeCashDown: "+financeCashDown);
          }

          var capitalizedCost = price - financeCashDown;
          console.log("capitalizedCost: "+capitalizedCost);
          var biweeklyFinancePmt = 'Nan';
          console.log("biweeklyFinancePmt: "+biweeklyFinancePmt);
          if (capitalizedCost > 0 && financeCashDown >= 0) {
            var partFirst = 1 + (financeRate/100)/12;
          console.log("partFirst: "+partFirst);
            var compoundInterest = capitalizedCost*Math.pow(partFirst, financeTerm);
          console.log("compoundInterest: "+compoundInterest);
            var basePmt = compoundInterest/financeTerm;
          console.log("basePmt: "+basePmt);
            biweeklyFinancePmt = '$' + ((basePmt * 12) / 26).toFixed(2);
          console.log("biweeklyFinancePmt: "+biweeklyFinancePmt);
          }

          $totalFinance.text(biweeklyFinancePmt);
          console.log("biweeklyFinancePmt: "+biweeklyFinancePmt);
          console.log("*** END FINANCE CALCULATION");
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);