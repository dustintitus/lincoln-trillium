<?php

namespace Drupal\trilliumlincoln_sync\Plugin\QueueWorker;

use Drupal\Core\State\StateInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides base functionality for the ReportWorkers.
 */
abstract class ImportWorkerBase extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;


  /**
   * The state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  protected $importerName;
  protected $importer;

  protected $configFactory;
  protected $config;

  /**
   * ReportWorkerBase constructor.
   *
   * @param array $configuration
   *   The configuration of the instance.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service the instance should use.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger service the instance should use.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state, LoggerChannelFactoryInterface $logger, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
    $this->logger = $logger;
    $importers = trilliumlincoln_sync_get_importers();
    $this->importer = $importers[$this->importerName];

    $this->sourceService = \Drupal::service('trilliumlincoln_sync.source');
    $this->destService = \Drupal::service('trilliumlincoln_sync.dest');

    $this->configFactory = $config_factory;
    $this->config = $this->configFactory->getEditable($this->importer['config']);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('logger.factory'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow($item, $entity = NULL) {
    $importer = $this->importer;
    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareEntity($item) {
    $values = [];

    $importer = $this->importer;
    $process_fields = $this->config->get('process');

    $item = (array) $item;

    foreach ($process_fields as $key => $field) {
      $key = explode('/', $key);
      $field_name = $key[0];
      $field_component = '';
      if (!empty($key[1])) {
        $field_component = $key[1];
      }

      if (is_array($field)) {
        if (isset($field['plugin']) && $field['plugin'] == 'default_value') {
          if (!empty($field_component)) {
            $values[$field_name][$field_component] = $field['default_value'];
          }
          else {
            $values[$field_name] = $field['default_value'];
          }
        }
      }
      elseif (is_string($field)) {
        $nested_path = explode('/', $field);
        if (!empty($nested_path[1])) {
          if (is_object($item[$nested_path[0]])) {
            $item[$nested_path[0]] = (array) $item[$nested_path[0]];
          }
          if (is_string($item[$nested_path[0]][$nested_path[1]])) {
            $field_value = $item[$nested_path[0]][$nested_path[1]];
          }
          else {
            $field_value = '';
          }
        }
        else {
          $field_value = $item[$nested_path[0]];
        }

        if (!empty($field_component)) {
          $values[$field_name][$field_component] = $field_value;
        }
        else {
          $values[$field_name] = $field_value;
        }
      }
    }

    $entity = entity_create($importer['entity_type'], $values);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function compareEntities($entity, $new_entity) {
    $process_fields = $this->config->get('process');
    foreach ($process_fields as $key => $field) {
      $key = explode('/', $key);
      $field_name = $key[0];
      $field_component = '';
      if (!empty($key[1])) {
        $field_component = $key[1];
      }

      $field_value = $entity->get($field_name)->getValue();
      $value = reset($field_value);
      if (!empty($value)) {
        if (!empty($field_component)) {
          $value = $value[$field_component];
        }
        else {
          $value = reset($value);
        }
      }

      $new_field_value = $new_entity->get($field_name)->getValue();
      $new_value = reset($new_field_value);
      if (!empty($new_value)) {
        if (!empty($field_component)) {
          $new_value = $new_value[$field_component];
        }
        else {
          $new_value = reset($new_value);
        }
      }

      if ($value != $new_value) {
        $entity->get($field_name)->setValue($new_field_value);
        $entity->need_to_save = TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function compareEntitiesField($entity, $new_entity, $field_name, $component) {
    $field_value = $entity->get($field_name)->getValue();
    $new_field_value = $new_entity->get($field_name)->getValue();

    $values = [];
    foreach ($field_value as $key => $value) {
      $values[] = $value[$component];
    }

    $new_values = [];
    foreach ($new_field_value as $key => $value) {
      $new_values[] = $value[$component];
    }

    $result_removed = array_diff_assoc($values, $new_values);
    $result_added = array_diff_assoc($new_values, $values);

    if (!empty($result_removed) || !empty($result_added)) {
      $entity->get($field_name)->setValue($new_field_value);
      $entity->need_to_save = TRUE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function compareFileField($file, $new_file, $field_name, $component) {
    $field_value = $file->get($field_name)->getValue();
    $new_field_value = $new_file->{$field_name};

    $values = [];
    foreach ($field_value as $key => $value) {
      $values[] = $value[$component];
    }

    $new_values = [];
    foreach ($new_field_value as $key => $value) {
      $new_values[] = $value[$component];
    }

    $result_removed = array_diff($values, $new_values);
    $result_added = array_diff($new_values, $values);

    if (!empty($result_removed) || !empty($result_added)) {
      $file->get($field_name)->setValue($new_field_value);
      $file->need_to_save = TRUE;
    }

  }

  /**
   * Retrieves a tid for use from taxonomy.
   */
  public function getTaxonomyTermId($term_name, $vocabulary) {
    if ($terms = taxonomy_term_load_multiple_by_name($term_name, $vocabulary)) {
      $term = reset($terms);
    }
    else {
      $term = Term::create([
        'name' => $term_name,
        'vid' => $vocabulary,
      ]);
      $term->save();
    }
    return $term->id();
  }

}
