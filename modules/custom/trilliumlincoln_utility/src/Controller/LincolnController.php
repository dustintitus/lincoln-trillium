<?php

namespace Drupal\trilliumlincoln_utility\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\taxonomy\Entity\Term;

/**
 * LincolnController class.
 */
class LincolnController extends ControllerBase {
  /**
   * Callback for opening the modal form.
   */
  public function modalRedirectContent() {
    $response = new AjaxResponse();
    
    $element = [
      '#theme' => 'redirect-lincoln',
      '#url' => 'https://shop.lincolncanada.com/showroom/?linktype=build#/'
    ];

    // Add an AJAX command to open a modal dialog with the content.
    $response->addCommand(new OpenDialogCommand('#trilliumlincoln-modal', $this->t('Redirect lincoln'), $element, ['modal' => true]));

    return $response;
  }

    /**
   * Callback for opening the modal form test drive.
   */
  public function openModalTestDriveForm() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('test_drive');
    $modal_form = $webform->getSubmissionForm();

    $pid = \Drupal::request()->query->get('pid');
    $product = Product::load($pid);

    if (!empty($modal_form['elements']['sent_from'])) {
      $url_product = $product->toUrl('canonical', ['absolute' => true]);
      $modal_form['elements']['sent_from']['#value'] = $url_product->toString();
    }

    //sku
    $variations_field = $product->get('variations')->getValue();
    if (!empty($variations_field)) {
      $variation = ProductVariation::load($variations_field[0]['target_id']);
      if (!empty($variation)) {
        $sku_field = $variation->get('sku')->getValue();
        if (!empty($sku_field)) {
          $sku = $sku_field[0]['value'];
          $modal_form['elements']['stock_number']['#value'] = $sku;
        }
      }
    }

    //model
    if (!empty($product->hasField('field_car_model'))) {
      $field_car_model = $product->get('field_car_model')->getValue();
      if (isset($field_car_model[0]['target_id'])) {
        $field_car_model_id = $field_car_model[0]['target_id'];
        $model_term = Term::load($field_car_model_id);
        $modal_form['elements']['model']['#value'] = $model_term->getName();
      }
    }

    //year
    if (!empty($product->hasField('field_car_year'))) {
      $field_car_year = $product->get('field_car_year')->getValue();
      if (!empty($field_car_year)) {
        $model_year = $field_car_year[0]['value'];
        $modal_form['elements']['year']['#value'] = date('Y', strtotime($model_year));
      }
    }

    //make
    if (!empty($product->hasField('field_car_make'))) {
      $field_car_make = $product->get('field_car_make')->getValue();
      if (!empty($field_car_make)) {
        $model_make = $field_car_make[0]['value'];
        $modal_form['elements']['make']['#value'] = $model_make;
      }
    }

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenDialogCommand('#trilliumlincoln-close-modal', $webform->label(), $modal_form, ['modal' => true]));

    return $response;
  }

  /**
   * Callback for opening the modal form Email Sales.
   */
  public function openModalEmailSalesForm() {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('email_sales');
    $modal_form = $webform->getSubmissionForm();

    $pid = \Drupal::request()->query->get('pid');
    $product = Product::load($pid);

    if (!empty($modal_form['elements']['sent_from'])) {
      $url_product = $product->toUrl('canonical', ['absolute' => true]);
      $modal_form['elements']['sent_from']['#value'] = $url_product->toString();
    }

    //sku
    $variations_field = $product->get('variations')->getValue();
    if (!empty($variations_field)) {
      $variation = ProductVariation::load($variations_field[0]['target_id']);
      if (!empty($variation)) {
        $sku_field = $variation->get('sku')->getValue();
        if (!empty($sku_field)) {
          $sku = $sku_field[0]['value'];
          $modal_form['elements']['stock_number']['#value'] = $sku;
        }
      }
    }

    //model
    if (!empty($product->hasField('field_car_model'))) {
      $field_car_model = $product->get('field_car_model')->getValue();
      if (isset($field_car_model[0]['target_id'])) {
        $field_car_model_id = $field_car_model[0]['target_id'];
        $model_term = Term::load($field_car_model_id);
        $modal_form['elements']['model']['#value'] = $model_term->getName();
      }
    }

    //year
    if (!empty($product->hasField('field_car_year'))) {
      $field_car_year = $product->get('field_car_year')->getValue();
      if (!empty($field_car_year)) {
        $model_year = $field_car_year[0]['value'];
        $modal_form['elements']['year']['#value'] = date('Y', strtotime($model_year));
      }
    }

    //make
    if (!empty($product->hasField('field_car_make'))) {
      $field_car_make = $product->get('field_car_make')->getValue();
      if (!empty($field_car_make)) {
        $model_make = $field_car_make[0]['value'];
        $modal_form['elements']['make']['#value'] = $model_make;
      }
    }

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenDialogCommand('#trilliumlincoln-close-modal', $webform->label(), $modal_form, ['modal' => true]));

    return $response;
  }

  /**
   * Callback for add to compare.
   */
  public function addProductToCompareAjax($pid){
    $response = new AjaxResponse();

    $tempstore = \Drupal::service('user.private_tempstore')->get('trilliumlincoln_utility');
    $compare_cars = $tempstore->get('compare_cars');
    if (empty($compare_cars)) $compare_cars = [];
    if (($key = array_search($pid, $compare_cars)) !== false) {
        unset($compare_cars[$key]);
    } else {
      array_push($compare_cars, $pid);
    }
    $tempstore->set('compare_cars',$compare_cars);
    return $response;
  }

}
