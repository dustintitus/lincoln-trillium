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

    $residual = 0;
    $price = 0;
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

      $field_car_model = $product->get('field_car_model')->getValue();
      if (isset($field_car_model[0]['target_id'])) {
        $car_model_term = Term::load($field_car_model[0]['target_id']);
        if (!empty($car_model_term)) {
          if ($car_model_term->hasField('field_car_model_residual')) {
            $field_car_model_residual = $car_model_term->get('field_car_model_residual')->getValue();
            $residual = isset($field_car_model_residual[0]['number']) ? $field_car_model_residual[0]['number']: 0;
          }
        }
      }
    }

    $price+=449;

    $term_options = [];
    $term_rate = [];
    $vid = 'calculator_term';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, 0, NULL, TRUE);
    foreach ($terms as $term) {
      $field_term_month = $term->get('field_term_month')->getValue();
      $field_finance_rate = $term->get('field_finance_rate')->getValue();
      $field_lease_rate = $term->get('field_lease_rate')->getValue();

      $term_options[$field_term_month[0]['value']] = $term->getName();
      $term_rate[$field_term_month[0]['value']] = [
        'finance-rate' => $field_finance_rate[0]['value'],
        'lease-rate' => $field_lease_rate[0]['value']
      ];
    }
    
    $default_lease_cash_down = 0;
    $default_lease_term = 48;
    $capitalized_cost = $price - $default_lease_cash_down;
    $amort_amt = $capitalized_cost - $residual;
    $base_pmt = $amort_amt/$default_lease_term;
    $lease_rate = isset($term_rate[$default_lease_term]['lease-rate']) ? $term_rate[$default_lease_term]['lease-rate']: 0;
    $money_factor = ($lease_rate/24)/100;
    $interest_cost = ($capitalized_cost + $residual) * $money_factor;
    $pmt = ($base_pmt + $interest_cost);
    $default_biweekly_lease_pmt = '$' . round((($pmt * 12) / 26),2);

    $form['price'] = [
      '#type' => 'hidden',
      '#value' => $price,
    ];
    $form['residual'] = [
      '#type' => 'hidden',
      '#value' => $residual,
    ];
    $form['payment'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Payment Calculator'),
      '#collapsible' => TRUE, 
      '#collapsed' => FALSE,
    ];
    $form['payment']['lease'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Lease'),
    ];

    $form['payment']['lease']['lease_term'] = [
      '#type' => 'select',
      '#title' => $this->t("Term"),
      '#options' => $term_options,
      '#options_attribute' => $term_rate,
      '#default_value' => $default_lease_term
    ];

    $form['payment']['lease']['lease_cash_down'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Cash Down"),
      '#placeholder' => '$0',
    ];

    $default_finace_cash_down = 0;
    $default_finance_term = 48;
    $capitalized_cost = $price - $default_finace_cash_down;
    $finance_rate = isset($term_rate[$default_finance_term]['finance-rate']) ? $term_rate[$default_finance_term]['finance-rate']: 0;
    $compoundInterest = $capitalized_cost * pow((1 + ($finance_rate / 100)/12), $default_finance_term);
    $base_pmt = $compoundInterest/$default_finance_term;
    $default_biweekly_finance_pmt = '$' . round((($base_pmt * 12) / 26),2);

    $form['payment']['finance'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Finance'),
    ];

    $form['payment']['finance']['finance_term'] = [
      '#type' => 'select',
      '#title' => $this->t("Term"),
      '#options' => $term_options,
      '#options_attribute' => $term_rate,
      '#default_value' => $default_finance_term
    ];

    $form['payment']['finance']['finance_cash_down'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Cash Down"),
      '#placeholder' => '$0',
    ];

    $form['payment']['total'] = [
      '#type' => 'container',
    ];
    $form['payment']['total']['total_lease'] = [
      '#markup' => '<div class="total-lease total-item"><label>' . $this->t('Total Lease*') . '</label><span>' . $default_biweekly_lease_pmt . '</span></div>'
    ];
    $form['payment']['total']['total_finance'] = [
      '#markup' => '<div class="total-finance total-item"><label>' . $this->t('Total Finance*') . '</label><span>' . $default_biweekly_finance_pmt . '</span></div>'
    ];

    $form['pmt_description'] = [
      '#markup' => '<p>' . $this->t('*PMT is biweekly, plus HST & Licensing.Lease parts are based on 20,000km/yr with a due on delivery of any cost down, 1st mth part, applicable HST, licensing & registration. Pricing is subject to incentive eligibility.') . '</p>'
    ];

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
