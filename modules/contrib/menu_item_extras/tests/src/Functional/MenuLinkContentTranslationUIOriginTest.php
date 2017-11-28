<?php

namespace Drupal\Tests\menu_item_extras\Functional;

use Drupal\Tests\menu_link_content\Functional\MenuLinkContentTranslationUITest;

/**
 * Tests the menu link content translation UI.
 *
 * @group menu_item_extras
 */
class MenuLinkContentTranslationUIOriginTest extends MenuLinkContentTranslationUITest {

  /**
   * {@inheritdoc}
   */
  public function __construct($name = NULL, array $data = [], $dataName = '') {
    // @todo: Fix that test.
    // static::$modules[] = 'menu_item_extras';
    parent::__construct($name, $data, $dataName);
  }

}
