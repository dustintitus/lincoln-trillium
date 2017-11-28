<?php

namespace Drupal\Tests\menu_item_extras\Functional;

use Drupal\Tests\menu_link_content\Functional\MenuLinkContentDeleteFormTest;

/**
 * Tests the menu link content delete UI.
 *
 * @group menu_item_extras
 */
class MenuLinkContentDeleteFormOriginTest extends MenuLinkContentDeleteFormTest {

  /**
   * {@inheritdoc}
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    // @todo: Fix that test.
    // static::$modules[] = 'menu_item_extras';
    parent::__construct($name, $data, $dataName);
  }

}
