<?php

namespace Drupal\trilliumlincoln_sync\Plugin\QueueWorker;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\media_entity\Entity\Media;
use \Drupal\file\Entity\File;
use Drupal\physical\Length;
use Drupal\physical\LengthUnit;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * A import worker.
 *
 * @QueueWorker(
 *   id = "trilliumlincoln_sync_queue_car",
 *   title = @Translation("Car import worker"),
 *   cron = {"time" = 40}
 * )
 */
class ImportWorkerCar extends ImportWorkerBase {

  protected $importerName = 'car';

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (empty($this->importer)) {
      return;
    }

    $source = $this->importer['source'];

    // Get source data.
    $item = $this->sourceService->getCar($data[$source['primary_key']]);

    // Prepare row.
    $item = $this->prepareRow($item);

    if (empty($item['stock_number']) || $item['price'] == 0) {
      return;
    }

    // Get Drupal entity if exists.
    $entity = $this->destService->getEntityBySourceKey(
      $this->importer['entity_type'],
      $this->importer['bundle'],
      'car_stock_number',
      $data[$source['primary_key']]
    );

    // Create car.
    if (empty($entity)) {
      $entity = $this->prepareEntity($item);

      //Accessory
      if (!empty($item['accessory_code']) || !empty($item['accessory_description']) || !empty($item['accessory_price'])) {
        $length_accessory_code = count($item['accessory_code']);
        $length_accessory_description = count($item['accessory_description']);
        $length_accessory_price = count($item['accessory_price']);

        $max_lenght = max($length_accessory_code, $length_accessory_description, $length_accessory_price);
        for ($i=0; $i < $max_lenght; $i++) { 
          $field_car_accessory_code = isset($item['accessory_code'][$i]) ? $item['accessory_code'][$i]: '';
          $field_car_accessory_description = isset($item['accessory_description'][$i]) ? $item['accessory_description'][$i]: '';
          $field_car_accessory_price = isset($item['accessory_price'][$i]) ? $item['accessory_price'][$i]: '';
          
          $paragraph = Paragraph::create(['type' => 'car_accessory',]);
          $paragraph->set('field_car_accessory_code', $field_car_accessory_code); 
          $paragraph->set('field_car_accessory_description', $field_car_accessory_description); 
          $paragraph->set('field_car_accessory_price', $field_car_accessory_price); 

          $paragraph->isNew();
          $paragraph->save();

          $entity->field_car_accessory[] = [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId()
          ];
        }
      }

      //Category.
      if (!empty($item['category'])) {
        $tid = $this->getTaxonomyTermId($item['category'], 'car_category');
        $entity->field_car_category[] = [
          'target_id' => $tid,
        ];
      }

      //Body style.
      if (!empty($item['body_style'])) {
        $tid = $this->getTaxonomyTermId($item['body_style'], 'car_body_style');
        $entity->field_car_body_style[] = [
          'target_id' => $tid,
        ];
      }

      //Car type.
      if (!empty($item['type'])) {
        $tid = $this->getTaxonomyTermId($item['type'], 'car_type');
        $entity->field_car_type[] = [
          'target_id' => $tid,
        ];
      }

      //Car model.
      if (!empty($item['model'])) {
        $tid = $this->getTaxonomyTermId($item['model'], 'car_model');
        $entity->field_car_model[] = [
          'target_id' => $tid,
        ];
      }

      //msrp
      $msrp = new Price($item['msrp'], 'USD');
      $entity->set('field_car_msrp', $msrp->toArray());

      $entity->save();

      // The price of the variation.
      $price = new Price($item['price'], 'USD');
      $variation = ProductVariation::create([
        'type' => 'default',
        'sku' => $item['stock_number'],
        'status' => 1, // The product status. 0 for disabled, 1 for enabled.
        'price' => $price,
      ]);
      $variation->save();

      $entity->addVariation($variation);
      $entity->save();
    }
    // Update entity if required ( source information was changed )
    else {
      $entity->need_to_save = FALSE;
      $new_entity = $this->prepareEntity($item);

      //Accessory
      $field_car_accessory = $entity->get('field_car_accessory')->getValue();
      $new_entity->set('field_car_accessory', $field_car_accessory);
      if (!empty($item['accessory_code']) || !empty($item['accessory_description']) || !empty($item['accessory_price'])) {
        $new_accessory = [];
        $old_accessory = [];

        $length_accessory_code = count($item['accessory_code']);
        $length_accessory_description = count($item['accessory_description']);
        $length_accessory_price = count($item['accessory_price']);

        $max_lenght = max($length_accessory_code, $length_accessory_description, $length_accessory_price);
        for ($i=0; $i < $max_lenght; $i++) { 
          $new_accessory[] = [
            'accessory_code' => isset($item['accessory_code'][$i]) ? $item['accessory_code'][$i]: '',
            'accessory_description' => isset($item['accessory_description'][$i]) ? $item['accessory_description'][$i]: '',
            'accessory_price' => isset($item['accessory_price'][$i]) ? $item['accessory_price'][$i]: '',
          ];
        }
        $field_car_accessory_value = array();
        if (!empty($field_car_accessory)) {
          foreach ($field_car_accessory as $accessory_key => $accessory_id) {
            $car_accessory = Paragraph::load($accessory_id['target_id']);
            if (!empty($car_accessory)) {
              $accessory_code = $car_accessory->get('field_car_accessory_code')->getValue();
              $accessory_description = $car_accessory->get('field_car_accessory_description')->getValue();
              $accessory_price = $car_accessory->get('field_car_accessory_price')->getValue();

              $field_car_accessory_value[$accessory_id['target_id']]['accessory_code'] = (isset($accessory_code[0]['value'])) ? $accessory_code[0]['value'] : '';
              $field_car_accessory_value[$accessory_id['target_id']]['accessory_description'] = (isset($accessory_description[0]['value'])) ? $accessory_description[0]['value'] : '';
              $field_car_accessory_value[$accessory_id['target_id']]['accessory_price'] = (isset($accessory_price[0]['value'])) ? $accessory_price[0]['value'] : '';
            }
          }
        }

        if (!empty($new_accessory) && !empty($field_car_accessory_value)) {
          foreach ($new_accessory as $new_accessory_key => $new_accessory_value) {
            foreach ($field_car_accessory_value as $accessory_id => $accessory_value) {
              if (($accessory_value['accessory_code'] == $new_accessory_value['accessory_code']) && ($accessory_value['accessory_description'] == $new_accessory_value['accessory_description']) && ($accessory_value['accessory_price'] == $new_accessory_value['accessory_price'])) {
                unset($new_accessory[$new_accessory_key]);
                break;
              }
            }
          }
        }

        if (!empty($new_accessory)) {
          foreach ($new_accessory as $new_accessory_key => $new_accessory_value) {
            $paragraph = Paragraph::create(['type' => 'car_accessory',]);
            $paragraph->set('field_car_accessory_code', $new_accessory_value['accessory_code']); 
            $paragraph->set('field_car_accessory_description', $new_accessory_value['accessory_description']); 
            $paragraph->set('field_car_accessory_price', $new_accessory_value['accessory_price']); 

            $paragraph->isNew();
            $paragraph->save();

            $field_car_accessory[] = [
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId()
            ];
          }
          $new_entity->set('field_car_accessory', $field_car_accessory);
        }
      }

      //Category.
      if (!empty($item['category'])) {
        $tid = $this->getTaxonomyTermId($item['category'], 'car_category');
        $new_entity->field_car_category[] = [
          'target_id' => $tid,
        ];
      }

      //Body style.
      if (!empty($item['body_style'])) {
        $tid = $this->getTaxonomyTermId($item['body_style'], 'car_body_style');
        $new_entity->field_car_body_style[] = [
          'target_id' => $tid,
        ];
      }

      //Car type.
      if (!empty($item['type'])) {
        $tid = $this->getTaxonomyTermId($item['type'], 'car_type');
        $new_entity->field_car_type[] = [
          'target_id' => $tid,
        ];
      }

      //Car model.
      if (!empty($item['model'])) {
        $tid = $this->getTaxonomyTermId($item['model'], 'car_model');
        $new_entity->field_car_model[] = [
          'target_id' => $tid,
        ];
      }

      //msrp
      $msrp = new Price($item['msrp'], 'USD');
      $new_entity->set('field_car_msrp', $msrp->toArray());

      $variations_field = $entity->get('variations')->getValue();
      $need_create_variations = FALSE;
      if (!empty($variations_field)) {
        $variation = ProductVariation::load($variations_field[0]['target_id']);
        if (!empty($variation)) {
          $sku_field = $variation->get('sku')->getValue();
          $sku = NULL;
          if (!empty($sku_field)) {
            $sku = $sku_field[0]['value'];
          }
          $price_field = $variation->get('price')->getValue();
          $price = NULL;
          if (!empty($price_field)) {
            $price = (float) $price_field[0]['number'];
          }

          $new_sku = $item['stock_number'];
          $new_price = (float) $item['price'];
          if ($sku != $new_sku || $price != $new_price) {
            $variation->set('sku', $new_sku);
            $variation->set('price', new Price($item['price'], 'USD'));
            $variation->save();
          }
        }
        else{
          $need_create_variations = TRUE;
        }
      }
      else{
        $need_create_variations = TRUE;
      }

      $this->compareEntities($entity, $new_entity);

      // Compare Accessory field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_accessory', 'target_id');
      // Compare Category field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_category', 'target_id');
      // Compare Body Style field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_body_style', 'target_id');
      // Compare Type field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_type', 'target_id');
      // Compare Model field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_model', 'target_id');
      // Compare msrp field
      $this->compareEntitiesField($entity, $new_entity, 'field_car_msrp', 'number');

      if (!empty($entity->need_to_save)) {
        $entity->save();
      }

      if ($need_create_variations) {
        // The price of the variation.
        $price = new Price($item['price'], 'USD');
        $sku = $item['stock_number'];
        $variation = ProductVariation::create([
          'type' => 'default',
          'sku' => $sku,
          'status' => 1, // The product status. 0 for disabled, 1 for enabled.
          'price' => $price,
        ]);
        $variation->save();

        $entity->addVariation($variation);
        $entity->save();
      }
    } //end update section

  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow($item, $entity = NULL) {
    //generate searchKeys
    if (!empty($item)) {
      $source = $this->importer['source'];
      $map_fields = $this->sourceService->getMapFields();

      foreach ($item as $field_key => $field_value) {
        $new_value = $field_value;
        if (isset($map_fields[$field_key]['preprocess'])) {
          foreach ($map_fields[$field_key]['preprocess'] as $preprocess) {
            switch ($preprocess) {
              case 'lower':
                $new_value = mb_strtolower(html_entity_decode($new_value));
                break;
              case 'capitalize':
                $new_value = ucfirst(html_entity_decode($new_value));
                break;
              case 'explode':
                $new_value = explode(',',$new_value);
                break;
            }
          }
        }
        $item[$field_key] = $new_value;
      }

      $item['title'] = $item['make'] . ' ' . $item['model'] . ' ' . $item['year'] . ' - ' . $item['description'];
      $item['status'] = 1;
      $item['year'] = !empty($item['year']) ? $item['year'] . '-01-01': '';
    }
    return $item;
  }
}