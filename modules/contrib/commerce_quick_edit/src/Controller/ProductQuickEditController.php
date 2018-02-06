<?php

namespace Drupal\commerce_quick_edit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ProductQuickEditController class.
 */
class ProductQuickEditController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFormBuilder
   */
  protected $entityFormBuilder;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * QuickNoteController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\EntityFormBuilder $entity_form_builder
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFormBuilder $entity_form_builder, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
    $this->configFactory = $config_factory;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.form_builder'),
      $container->get('config.factory')
    );
  }

  /**
   * Open product edit form in a modal.
   *
   * @param integer $id
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function openModalForm($id) {
    $product_entity = $this->entityTypeManager
      ->getStorage('commerce_product')
      ->load($id);

    $config = $this->configFactory
      ->get('commerce_quick_edit.settings');

    $quick_note_form = $this->entityFormBuilder->getForm($product_entity);
    $quick_note_form['#attributes']['class'][] = 'commerce-quick-edit';
    $response = new AjaxResponse();

    return $response->addCommand(
      new OpenModalDialogCommand(
        'Product edit',
        $quick_note_form,
        ['width' => $config->get('modal_width')]
      )
    );
  }

}
