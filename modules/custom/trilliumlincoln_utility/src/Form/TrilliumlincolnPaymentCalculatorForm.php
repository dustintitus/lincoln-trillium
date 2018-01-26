<?php

/**
 * @file
 * Contains \Drupal\trilliumlincoln_utility\Form\TrilliumlincolnPaymentCalculatorForm.
 */

namespace Drupal\trilliumlincoln_utility\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

    $form['payment']['lease']['lease_term'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Term"),
      '#size' => 30,
      '#default_value' => "00"
    );

    $form['payment']['lease']['lease_cash_down'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Cash Down"),
      '#size' => 30,
      '#default_value' => "$0000"
    );

    $form['payment']['lease']['lease_mileage'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Mileage (Annual)"),
      '#size' => 30,
      '#default_value' => "00,000"
    );

    $form['payment']['finance'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Finance'),
    ];

    $form['payment']['finance']['finance_term'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Term"),
      '#size' => 30,
      '#default_value' => "00"
    );

    $form['payment']['finance']['finance_cash_down'] = array(
      '#type' => 'textfield',
      '#title' => $this->t("Cash Down"),
      '#size' => 30,
      '#default_value' => "$0000"
    );

    $form['payment']['total'] = [
      '#type' => 'container',
    ];
    $form['payment']['total']['total_lease'] = [
      '#markup' => '<div class="total-lease total-item"><label>' . $this->t('Total Lease') . '</label><span>$000</span></div>'
    ];
    $form['payment']['total']['total_finance'] = [
      '#markup' => '<div class="total-finance total-item"><label>' . $this->t('Total Finance') . '</label><span>$000</span></div>'
    ];

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
