<?php

namespace Drupal\trilliumlincoln_sync;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Serialization\Json;

/**
 * TrilliumlincolnSourceService class.
 */
class TrilliumlincolnSourceService {
  protected $configFactory;

  protected $file_directoty;

  /**
   * TrilliumlincolnSourceService construct.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $this->file_directoty = \Drupal::service('file_system')->realpath(file_default_scheme() . "://") . '/' . 'trilliumlincoln';
  }


  public function getCsvFile(){
    $file = [];
    $files = file_scan_directory($this->file_directoty, '/.*\.csv$/', ['key' => 'filename']);
    $files_created = [];

    if (!empty($files)) {
      foreach($files as $file){
        $created_time = filectime($file->uri);
        $files_created[$created_time] = $file;
      }
    }

    if (!empty($files_created)) {
      arsort($files_created);
      $file = reset($files_created);
    }

    return $file;
  }

  public function parseCSV($include = []) {
    $items = [];
    $file = $this->getCsvFile();

    if (!empty($file)) {
      $fields = $this->getMapFields();
      if (($handle = fopen($file->uri, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle)) !== FALSE) {
          $item = [];
          foreach ($fields as $field_name => $field_value) {
            if (isset($data[$field_value['index']])) {
              $item[$field_name] = $data[$field_value['index']];
            }
          }

          if (!empty($include)) {
            foreach ($include as $field_key => $value) {
              if ($item[$field_key] == $value) {
                $items[] = $item;
              }
            }
          }
          else{
            $items[] = $item;
          }
        }
        fclose($handle);
      }
    }

    return $items;
  }

  /**
   * Get Car function.
   */
  public function getCar($primary_key_value) {
    $result = [];

    $result_csv = $this->parseCSV(['make' => 'LINCOLN']);
    if (!empty($result_csv)) {
      foreach ($result_csv as $key => $value) {
        if ($value['stock_number'] == $primary_key_value) {
          $result = $value;
        }
      }
    }

    return $result;
  }


  /**
   * Get CarIds function.
   */
  public function getCarIds() {
    $result = [];

    $query = \Drupal::database()->select('commerce_product', 'cp');
    $query->fields('cpfsn', ['field_car_stock_number_value']);
    $query->join('commerce_product_field_data', 'cpfd', 'cpfd.product_id = cp.product_id');
    $query->join('commerce_product__field_car_stock_number', 'cpfsn', 'cpfsn.entity_id = cp.product_id');
    $query->condition('cp.type', 'car');
    $query->condition('cpfd.status', 1);
    $products = $query->execute()->fetchAll();

    if (!empty($products)) {
      foreach ($products as $key => $value) {
        $data = [];
        $sku = $value->field_car_stock_number_value;
        $data['stock_number'] = $sku;
        $result[$sku] = $data;
      }
    }

    $result_item = [];
    $result_csv = $this->parseCSV(['make' => 'LINCOLN']);
    if ($result_csv) {
      foreach ($result_csv as $key => $value) {
        $data = [];
        $sku = $value['stock_number'];
        $data['stock_number'] = $sku;
        $result[$sku] = $data;
      }
    }

    return $result;
  }

  /**
   * Get CarIds function.
   */
  public function unpublishCars() {
    /*$result = [];
    $result_csv = $this->parseCSV(['make' => 'LINCOLN']);
    if ($result_csv) {
      foreach ($result_csv as $key => $value) {
        $sku = $value['stock_number'];
        $result[] = $sku;
      }
    }

    $query = \Drupal::database()->select('commerce_product', 'cp');
    $query->fields('cpfsn', ['entity_id']);
    $query->join('commerce_product_field_data', 'cpfd', 'cpfd.product_id = cp.product_id');
    $query->join('commerce_product__field_car_stock_number', 'cpfsn', 'cpfsn.entity_id = cp.product_id');
    if (!empty($result)) {
      $query->condition('cpfsn.field_car_stock_number_value', $result,'NOT IN');
    }
    $query->condition('cp.type', 'car');
    $query->condition('cpfd.status', 1);
    $products = $query->execute()->fetchAll();

    if (!empty($products)) {
      foreach ($products as $key => $value) {
        $product = \Drupal\commerce_product\Entity\Product::load($value->entity_id);
        $product->setPublished(FALSE);
        $product->save();
      }
    }*/
  }

  public function getMapFields(){
    $fields = [
      'dealership_id' => [
        'type' => 'textfield',
        'index' => 1
      ],
      'stock_number' => [
        'type' => 'textfield',
        'index' => 2,
      ],
      'make' => [
        'type' => 'textfield',
        'index' => 3,
        'preprocess' => [
          'lower',
          'capitalize'
        ]
      ],
      'model' => [
        'type' => 'textfield',
        'index' => 4
      ],
      'year' => [
        'type' => 'date',
        'index' => 6
      ],
      'vin' => [
        'type' => 'textfield',
        'index' => 7
      ],
      'description' => [
        'type' => 'textfield',
        'index' => 10
      ],
      'color_long' => [
        'type' => 'textfield',
        'index' => 52,
        'preprocess' => [
          'lower',
          'capitalize'
        ]
      ],
      'fuel_type' => [
        'type' => 'textfield',
        'index' => 11,
        'preprocess' => [
          'lower',
          'capitalize'
        ]
      ],
      'cylinders' => [
        'type' => 'textfield',
        'index' => 12
      ],
      'type' => [
        'type' => 'taxonomy',
        'index' => 13,
        'preprocess' => [
          'lower',
          'capitalize'
        ]
      ],
      'interior_trim' => [
        'type' => 'textfield',
        'index' => 14
      ],
      'engine_type' => [
        'type' => 'textfield',
        'index' => 19
      ],
      'price' => [
        'type' => 'variations',
        'index' => 23
      ],
      'msrp' => [
        'type' => 'price',
        'index' => 24
      ],
      'odometer_reading' => [
        'type' => 'textfield',
        'index' => 36
      ],
      'odometer_type' => [
        'type' => 'select',
        'index' => 38
      ],
      'total_length' => [
        'type' => 'textfield',
        'index' => 42
      ],
      'number_of_axles' => [
        'type' => 'textfield',
        'index' => 43
      ],
      'number_of_doors' => [
        'type' => 'textfield',
        'index' => 44
      ],
      'category' => [
        'type' => 'taxonomy',
        'index' => 45,
        'preprocess' => [
          'lower',
          'capitalize'
        ]
      ],
      'body_style' => [
        'type' => 'taxonomy',
        'index' => 46
      ],
      'traction' => [
        'type' => 'textfield',
        'index' => 47
      ],
      'differential' => [
        'type' => 'textfield',
        'index' => 48
      ],
      'accessory_code' => [
        'type' => 'paragraph_fields',
        'index' => 69,
        'parent' => 'field_car_accessory',
        'preprocess' => [
          'explode'
        ]
      ],
      'accessory_description' => [
        'type' => 'paragraph_fields',
        'index' => 70,
        'parent' => 'field_car_accessory',
        'preprocess' => [
          'explode'
        ]
      ],
      'accessory_price' => [
        'type' => 'paragraph_fields',
        'index' => 71,
        'parent' => 'field_car_accessory',
        'preprocess' => [
          'explode'
        ]
      ],
    ];

    return $fields;
  }
}
