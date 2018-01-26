<?php

namespace Drupal\trilliumlincoln_sync;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * TrilliumlincolnDestService class.
 */
class TrilliumlincolnDestService {
  protected $configFactory;

  /**
   * TrilliumlincolnDestService construct.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Count function.
   */
  public function count($importer) {
    $total = 0;
    $query = db_select($importer['entity_type'] . '__field_' . $importer['dest']['primary_key']);
    $query->condition('deleted', 0);
    $query->condition('bundle', $importer['bundle']);
    $query->addExpression('COUNT(entity_id)');
    $total = $query->execute()->fetchField();
    return $total;
  }

  /**
   * Get Entity By SourceKey function.
   */
  public function getEntityBySourceKey($entity_type, $bundle, $key_primary_name, $key_primary) {

    $entity_id = $this->getEntityIdBySourceKey($entity_type, $bundle, $key_primary_name, $key_primary);
    if (!empty($entity_id)) {
      $entity = entity_load($entity_type, $entity_id);
      return $entity;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Get EntityId By SourceKey function.
   */
  public function getEntityIdBySourceKey($entity_type, $bundle, $key_primary_name, $key_primary) {

    $query = db_select($entity_type . '__field_' . $key_primary_name, 'fpk');
    $query->fields('fpk', ['entity_id']);
    $query->condition('fpk.deleted', 0);
    $query->condition('fpk.bundle', $bundle);
    $query->condition('fpk.field_' . $key_primary_name . '_value', $key_primary);

    $entity_id = $query->execute()->fetchField();

    return $entity_id;
  }

  /**
   * Get FileId By Filename function.
   */
  public function getFileIdByFilename($filename) {

    $query = db_select('file_managed', 'fm');
    $query->fields('fm', ['fid']);
    $query->condition('filename', $filename);

    $fid = $query->execute()->fetchField();

    return $fid;
  }

  /**
   * Get ExtraIds function.
   */
  public function getExtraIds($importer, $primary_keys) {
    $query = db_select($importer['entity_type'] . '__field_' . $importer['source']['primary_key'], 'fpk');
    $query->fields('fpk', ['entity_id', 'field_' . $importer['source']['primary_key'] . '_value']);
    $query->condition('deleted', 0);
    $query->condition('bundle', $importer['bundle']);
    $query->condition('field_' . $importer['source']['primary_key'] . '_value', $primary_keys, 'NOT IN');
    $result = $query->execute();
    return $result;
  }

  /**
   * Get EmptyIds function.
   */
  public function getEmptyIds($importer, $primary_keys) {
    $query = db_select($importer['entity_type'], 'et');
    $query->leftJoin($importer['entity_type'] . '__field_' . $importer['source']['primary_key'], 'fpk', 'et.' . $importer['id'] . ' = fpk.entity_id');
    $query->fields('et', [$importer['id']]);
    $query->condition('type', $importer['bundle']);
    $query->isNull('field_' . $importer['source']['primary_key'] . '_value');
    $result = $query->execute();
    return $result;
  }

}
