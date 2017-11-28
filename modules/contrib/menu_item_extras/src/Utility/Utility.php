<?php

namespace Drupal\menu_item_extras\Utility;

/**
 * Utility functions specific to menu_item_extras.
 */
class Utility {

  /**
   * Checks if bundle and entity fields are different.
   *
   * @param string $entity_type
   *   Entity type for checking.
   * @param string $bundle
   *   Bundle for checking.
   *
   * @return bool
   *   Returns TRUE if bundle has other fields than entity.
   */
  public static function checkBundleHasExtraFieldsThanEntity($entity_type, $bundle) {
    $entity_manager = \Drupal::service('entity_field.manager');
    $bundle_fields = array_keys($entity_manager->getFieldDefinitions($entity_type, $bundle));
    $entity_type_fields = array_keys($entity_manager->getBaseFieldDefinitions($entity_type));
    if ($bundle_fields !== $entity_type_fields) {
      return TRUE;
    }
    return FALSE;
  }

}
