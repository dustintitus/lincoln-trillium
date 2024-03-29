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
        var $totalFinance = $(this).find('.total-finance span');
        //Lease section
        $(this).on('change paste keyup','#edit-lease-term, #edit-lease-cash-down', function(event) {
          console.log("*** START LEASE CALCULATION");
          console.log("price: "+price);
          console.log("msrp: "+msrp);

          var $leaseTerm = $(this).parents('#edit-lease').find('#edit-lease-term');
          var leaseRate = parseFloat($leaseTerm.find('option:selected').attr('data-lease-rate'));
          console.log("leaseRate: "+leaseRate);

          var leaseTerm = parseInt($leaseTerm.val());
          console.log("leaseTerm: "+leaseTerm);

          var residual = parseFloat($leaseTerm.find('option:selected').attr('data-residual'));
          residual = residual/100;
          console.log("residual: "+residual);

          var leaseCashDown = parseFloat($(this).parents('#edit-lease').find('#edit-lease-cash-down').val()) / 1.13;
          console.log("leaseCashDown: "+leaseCashDown);

          if (isNaN(leaseCashDown) || leaseCashDown < 0) {
            leaseCashDown = 0;
            console.log("leaseCashDown: "+leaseCashDown);
          }

          var $pmt = $(this).parents('#edit-lease').find('#edit-pmt');

          var capitalizedCost = price - leaseCashDown;
          console.log("capitalizedCost: "+capitalizedCost);

          var biweeklyLeasePmt = 'Nan';
          
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
          console.log("$totalLease/biweeklyLeasePmt: "+biweeklyLeasePmt);
          console.log("*** END LEASE CALCULATION");
        });
        $('.trilliumlincoln-utility-payment-calculator-form').change();
        //finance section
        $(this).on('change paste keyup','#edit-finance-term, #edit-finance-cash-down', function(event) {
          console.log("*** START FINANCE CALCULATION");
          console.log("price: "+price);

          var $financeTerm = $(this).parents('#edit-finance').find('#edit-finance-term');
          var financeRate = parseFloat($financeTerm.find('option:selected').attr('data-finance-rate'));
          console.log("financeRate: "+financeRate);

          var financeTerm = parseInt($financeTerm.val());
          console.log("financeTerm: "+financeTerm);

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

/*
            COMPOUND INTEREST CALCULATOR

            var firstPart = 1 + (financeRate/100/1);
            console.log("firstPart: "+firstPart);

            var secondPart = financeTerm/12;
            console.log("secondPart: "+secondPart);

            var compoundInterest = capitalizedCost*Math.pow(firstPart, secondPart);            
            console.log("compoundInterest: "+compoundInterest);

            var basePmt = compoundInterest/financeTerm;
            console.log("basePmt: "+basePmt);

*/
           //console.log("************* START NEW RATE **********");
           var interestRate = financeRate;
           //console.log("interestRate :" + interestRate);
           var loanTerm = financeTerm;
           //console.log("loanTerm :" + loanTerm);
           var principalAmount = price;
           //console.log("principalAmount :" + principalAmount);
           var i = interestRate/100.0/12.0;
           var tau = 1.0 + i;
           var tauToTheN = Math.pow(tau, loanTerm);
           var magicNumber = tauToTheN * i / (tauToTheN - 1.0 );
           //console.log("magicNumber :" + magicNumber);
           var monthlyPayment = principalAmount * magicNumber;
           //console.log("monthlyPayment :" + monthlyPayment);
           var costOfLoan =  principalAmount * magicNumber * loanTerm - principalAmount;
           //console.log("costOfLoan :" + costOfLoan);
           var princPlusLoad = costOfLoan + principalAmount;
           //console.log("princPlusLoad :" + princPlusLoad);
           var numberOfPayments = 52 * (financeTerm/12) / 2;
           //console.log("numberOfPayments :" + numberOfPayments);
           var biweeklyNewPayment = princPlusLoad / numberOfPayments;
           biweeklyNewPayment = parseFloat(biweeklyNewPayment).toFixed(2);


           // console.log("biweeklyNewPayment biweeklyNewPayment :" + biweeklyNewPayment);
           // console.log("************* END NEW RATE **********");




            biweeklyFinancePmt = '$' + biweeklyNewPayment;
            // console.log("biweeklyFinancePmt: "+ biweeklyFinancePmt);
          }

          $totalFinance.text(biweeklyFinancePmt);
          console.log("$totalFinance/biweeklyFinancePmt: "+biweeklyFinancePmt);
          
          console.log("*** END FINANCE CALCULATION");
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);