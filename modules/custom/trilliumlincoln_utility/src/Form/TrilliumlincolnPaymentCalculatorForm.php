<?php

/**
 * @file
 * Contains \Drupal\trilliumlincoln_utility\Form\TrilliumlincolnPaymentCalculatorForm.
 */

namespace Drupal\trilliumlincoln_utility\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\commerce_product\Entity\ProductVariation;

/**
 * Payment Calculator form.
 */
class TrilliumlincolnPaymentCalculatorForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trilliumlincoln_utility_payment_calculator_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $residual = [
      '24' => 0,
      '36' => 0,
      '48' => 0
    ];
    $finance_term_rate = [
      '36' => 0,
      '48' => 0,
      '60' => 0,
      '72' => 0
    ];
    $lease_term_rate = [
      '24' => 0,
      '36' => 0,
      '48' => 0
    ];
    $lease_term_options = [];
    $finance_term_options = [];
    $price = 0;
    $msrp = 0;
    $hide_lease_section = FALSE;
    $hide_finance_section = FALSE;
    if ($product = \Drupal::routeMatch()->getParameter('commerce_product')) {
      $variations_field = $product->get('variations')->getValue();
      if (!empty($variations_field)) {
        $variation = ProductVariation::load($variations_field[0]['target_id']);
        if (!empty($variation)) {
          $price_field = $variation->get('price')->getValue();
          $price = NULL;
          if (!empty($price_field)) {
            $price = (float) $price_field[0]['number'];
          }
        }
      }

      if ($product->hasField('field_car_msrp')) {
        $item = $product->get('field_car_msrp')->getValue();
        if (!empty($item[0]['number'])) {
          $msrp = $item[0]['number'];
        }
      }

      foreach ($residual as $key => $value) {
        if ($product->hasField('field_residual_' . $key . '_term_rate')) {
          $item = $product->get('field_residual_' . $key . '_term_rate')->getValue();
          if (!empty($item[0]['value'])) {
            $residual[$key] = $item[0]['value'];
          }
          else{
            $hide_lease_section = TRUE;
          }
        }
      }

      foreach ($finance_term_rate as $key => $value) {
        if ($product->hasField('field_finance_' . $key . '_term_rate')) {
          $finance_term_options[$key] = $key;
          $item = $product->get('field_finance_' . $key . '_term_rate')->getValue();
          if (!empty($item[0]['value'])) {
            $finance_term_rate[$key] = ['finance-rate' => $item[0]['value']];
          }
          else{
            $hide_finance_section = TRUE;
          }
        }
      }
      foreach ($lease_term_rate as $key => $value) {
        if ($product->hasField('field_lease_' . $key . '_term_rate')) {
          $lease_term_options[$key] = $key;
          $item = $product->get('field_lease_' . $key . '_term_rate')->getValue();
          if (!empty($item[0]['value'])) {
            $lease_term_rate[$key] = [
              'lease-rate' => $item[0]['value'],
              'residual' => isset($residual[$key]) ? $residual[$key] : 0
            ];
          }
          else{
            $hide_lease_section = TRUE;
          }
        }
      }
    }

    if ($price > 0) {
      $price+=449;
    }

    $default_lease_cash_down = 0;
    $default_lease_term = 48;
    $capitalized_cost = $price - $default_lease_cash_down;
    $new_residual = (($residual[$default_lease_term])/100) * $msrp;
    $amort_amt = $capitalized_cost - $new_residual;
    $base_pmt = $amort_amt/$default_lease_term;
    $lease_rate = isset($lease_term_rate[$default_lease_term]['lease-rate']) ? $lease_term_rate[$default_lease_term]['lease-rate']: 0;
    $money_factor = ($lease_rate/24)/100;
    $interest_cost = ($capitalized_cost + $new_residual) * $money_factor;
    $pmt = ($base_pmt + $interest_cost);
    $default_biweekly_lease_pmt = '$' . round((($pmt * 12) / 26),2);

    $form['price'] = [
      '#type' => 'hidden',
      '#value' => $price,
    ];
    $form['msrp'] = [
      '#type' => 'hidden',
      '#value' => $msrp,
    ];
    $form['payment'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Payment Calculator'),
      '#collapsible' => TRUE, 
      '#collapsed' => TRUE,
      '#title_attributes' => [
        'class' => [
          'collapsed'
        ]
      ]
    ];
    if (!$hide_lease_section && $price > 0) {
      $form['payment']['lease'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Lease'),
      ];

      $form['payment']['lease']['lease_term'] = [
        '#type' => 'select',
        '#title' => $this->t("Term"),
        '#options' => $lease_term_options,
        '#options_attribute' => $lease_term_rate,
        '#default_value' => $default_lease_term
      ];

      $form['payment']['lease']['lease_cash_down'] = [
        '#type' => 'textfield',
        '#title' => $this->t("Cash Down"),
        '#placeholder' => '$0',
      ];
    }

    $default_finace_cash_down = 0;
    $default_finance_term = 48;
    $capitalized_cost = $price - $default_finace_cash_down;
    $finance_rate = isset($finance_term_rate[$default_finance_term]['finance-rate']) ? $finance_term_rate[$default_finance_term]['finance-rate']: 0;
    $compoundInterest = $capitalized_cost * pow((1 + (($finance_rate / 12)/12), $default_finance_term);
    $base_pmt = $compoundInterest/$default_finance_term;
    $default_biweekly_finance_pmt = '$' . round((($base_pmt * 12) / 26),2);

    if (!$hide_finance_section && $price > 0) {
      $form['payment']['finance'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Finance'),
      ];

      $form['payment']['finance']['finance_term'] = [
        '#type' => 'select',
        '#title' => $this->t("Term"),
        '#options' => $finance_term_options,
        '#options_attribute' => $finance_term_rate,
        '#default_value' => $default_finance_term
      ];

      $form['payment']['finance']['finance_cash_down'] = [
        '#type' => 'textfield',
        '#title' => $this->t("Cash Down"),
        '#placeholder' => '$0',
      ];
    }
    if (!($hide_lease_section && $hide_finance_section) && $price > 0) {
      $form['payment']['total'] = [
        '#type' => 'container',
      ];
    }
    if (!$hide_lease_section && $price > 0) {
      $form['payment']['total']['total_lease'] = [
        '#markup' => '<div class="total-lease total-item"><label>' . $this->t('Total Lease*') . '</label><span>' . $default_biweekly_lease_pmt . '</span></div>'
      ];
    }
    if (!$hide_finance_section && $price > 0) {
      $form['payment']['total']['total_finance'] = [
        '#markup' => '<div class="total-finance total-item"><label>' . $this->t('Total Finance*') . '</label><span>' . $default_biweekly_finance_pmt . '</span></div>'
      ];
    }

    if (($hide_lease_section && $hide_finance_section) || $price == 0) {
      $form['payment']['message'] = [
        '#markup' => '<p>' . $this->t('Calculator not available for this vehicle. Please contact us for more info.') . '</p>'
      ];
    }
    else{
      $form['payment']['pmt_description'] = [
        '#markup' => '<p>' . $this->t('*PMT is biweekly, plus HST & Licensing.Lease parts are based on 20,000km/yr with a due on delivery of any cost down, 1st mth part, applicable HST, licensing & registration. Pricing is subject to incentive eligibility.') . '</p>'
      ];
    }
    $form['#attached']['library'][] = 'trilliumlincoln_utility/trilliumlincoln_utility.calculator';

    return $form;
  }


    /**
   * Implements a form submit handler.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
