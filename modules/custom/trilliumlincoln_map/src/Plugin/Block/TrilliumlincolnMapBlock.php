<?php

/**
 * @file
 * Contains \Drupal\trilliumlincoln_map\Plugin\Block\TrilliumlincolnMapBlock.
 */

namespace Drupal\trilliumlincoln_map\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Trilliumlincoln: Map block'.
 *
 * @Block(
 *   id = "trilliumlincoln_map_custom_block",
 *   admin_label = @Translation("Trilliumlincoln: Map block"),
 *   category = @Translation("Trilliumlincoln Custom")
 * )
 */
class TrilliumlincolnMapBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message'),  
      '#default_value' => $config['message'],    
    );
    $form['lat'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Coord Lat'),     
      '#default_value' => $config['lat'], 
    );
    $form['lng'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Coord Lng'),
      '#default_value' => $config['lng'],       
    );

    return $form;
  }

  /**
  * {@inheritdoc}
  */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['message'] = $values['message'];
    $this->configuration['lat'] = $values['lat'];
    $this->configuration['lng'] = $values['lng'];
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    //disable page cache for the page where this block is placed
    \Drupal::service('page_cache_kill_switch')->trigger();

    $config = $this->getConfiguration();

    $lat = '';
    $lng = '';

    if(isset($config['lat'])) $lat = $config['lat'];
    if(isset($config['lng'])) $lng = $config['lng'];



    $build = array(
      '#type' => 'markup',
      '#markup' => '
      <div class="trilliumlincoln_map-message">'.$config['message'].'</div>
      <div id="map"></div>
      <div class="trilliumlincoln_map-button-wrapper">
      <a target="_blank" class="btn btn-info btn-lg btn-block" href="https://maps.google.com/?q='.$lat.','.$lng.'&ll='.$lat.','.$lng.'&z=14">GET DIRECTIONS</a>
      </div>',
      '#attached' => array(
        'library' => array(
          'trilliumlincoln_map/trilliumlincoln_map',
        ),   
      ),   
      '#cache' => array(
        'max-age' => 0
      ),
    );

    $build['#attached']['drupalSettings']['trilliumlincoln_map']['path'] = drupal_get_path('module', 'trilliumlincoln_map');
    $build['#attached']['drupalSettings']['trilliumlincoln_map']['coords']['lat'] = $lat;
    $build['#attached']['drupalSettings']['trilliumlincoln_map']['coords']['lng'] = $lng;

    return $build;
  }

}