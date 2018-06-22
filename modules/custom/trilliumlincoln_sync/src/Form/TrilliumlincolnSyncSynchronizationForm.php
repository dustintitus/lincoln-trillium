<?php

namespace Drupal\trilliumlincoln_sync\Form;

use Drupal\node\Entity\Node;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\CronInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Trilliumlincoln Synchronization From.
 */
class TrilliumlincolnSyncSynchronizationForm extends ConfigFormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * The cron service.
   *
   * @var \Drupal\Core\CronInterface
   */
  protected $cron;

  /**
   * Constructs a T
   rilliumlincolnSyncSynchronizationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\CronInterface $cron
   *   The cron service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   The queue object.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state keyvalue collection.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountInterface $current_user, CronInterface $cron, QueueFactory $queue, StateInterface $state) {
    parent::__construct($config_factory);
    $this->currentUser = $current_user;
    $this->cron = $cron;
    $this->state = $state;
    $this->queue = $queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('cron'),
      $container->get('queue'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'trilliumlincoln_sync_synchronization';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['trilliumlincoln_sync.cron'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $trilliumlincoln_sync_source_service = \Drupal::service('trilliumlincoln_sync.source');

    if (\Drupal::request()->request->count() == 0) {
      $form['sync_status'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Title'),
          $this->t('Entity Type'),
          $this->t('Bundle'),
          $this->t('Imported'),
          $this->t('In Queue'),
        ],
      ];
      $trilliumlincoln_sync_dest_service = \Drupal::service('trilliumlincoln_sync.dest');

      $importers = trilliumlincoln_sync_get_importers();

      foreach ($importers as $key => $importer) {
        $source = $importer['source'];
        $row['title'] = $importer['title'];
        $row['entity_type'] = $importer['entity_type'];
        $row['bundle'] = $importer['bundle'];
        $row['imported'] = $trilliumlincoln_sync_dest_service->count($importer);
        $row['in_queue'] = 0;
        if (isset($importer['queue_worker'])) {
          $queue = $this->queue->get($importer['queue_worker']);
          $row['in_queue'] = $queue->numberOfItems();
        }
        $form['sync_status']['#rows'][] = $row;
      }
    }

    if ($this->currentUser->hasPermission('administer site configuration')) {
      $cron_config = $this->configFactory->get('trilliumlincoln_sync.cron');
      $form['cron_config'] = [
        '#type' => 'details',
        '#title' => $this->t('Cron Configuration'),
        '#open' => TRUE,
      ];

      $form['cron_config']['trilliumlincoln_sync_cron_interval'] = [
        '#type' => 'select',
        '#title' => $this->t('Cron interval'),
        '#description' => $this->t('Time after which trilliumlincoln_sync_cron will respond to a processing request.'),
        '#default_value' => $cron_config->get('interval'),
        '#options' => [
          60 => $this->t('1 minute'),
          300 => $this->t('5 minutes'),
          600 => $this->t('10 minutes'),
          3600 => $this->t('1 hour'),
          86400 => $this->t('1 day'),
        ],
      ];

      $next_execution = \Drupal::state()->get('trilliumlincoln_sync.next_execution');
      $next_execution = !empty($next_execution) ? $next_execution : REQUEST_TIME;

      $args = [
        '%time' => date_iso8601(\Drupal::state()->get('trilliumlincoln_sync.next_execution')),
        '%seconds' => $next_execution - REQUEST_TIME,
      ];
      $form['cron_config']['last'] = [
        '#type' => 'item',
        '#markup' => $this->t('trilliumlincoln_sync_cron() will be executed after %time (%seconds seconds from now)', $args),
      ];

      $form['cron_config']['cron_reset'] = [
        '#type' => 'checkbox',
        '#title' => $this->t("Run trilliumlincoln_sync's cron regardless of whether interval has expired."),
        '#default_value' => FALSE,
      ];

      $form['cron_config']['cron_trigger']['actions'] = ['#type' => 'actions'];
      $form['cron_config']['cron_trigger']['actions']['sumbit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Run cron now'),
        '#submit' => [[$this, 'cronRun']],
      ];

      // Queue Configuration.
      $form['queue_config'] = [
        '#type' => 'details',
        '#title' => $this->t('Queue Configuration'),
        '#open' => TRUE,
      ];

      $form['queue_config']['cron_queue_setup']['queue'] = [
        '#type' => 'radios',
        '#title' => $this->t('Queue worker'),
        '#options' => [
          'car' => $this->t('Car'),
        ],
      ];

      $form['queue_config']['cron_queue_setup']['actions'] = ['#type' => 'actions'];
      $form['queue_config']['cron_queue_setup']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Add jobs to queue'),
        '#submit' => [[$this, 'addItems']],
      ];

      $form['queue_config']['cron_queue_setup']['actions']['clear'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove jobs from queue'),
        '#submit' => [[$this, 'removeItems']],
      ];

      // Upload CSV.
      $form['upload_csv'] = [
        '#type' => 'details',
        '#title' => $this->t('Upload CSV'),
        '#open' => TRUE,
      ];

      $default_file = $trilliumlincoln_sync_source_service->getCsvFile();
      if (!empty($default_file)) {
        $form['upload_csv']['current_file'] = [
          '#type' => 'item',
          '#markup' => $this->t('Current file: %file_name', ['%file_name' => $default_file->filename]),
        ];
      }

      $form['upload_csv']['csv_file'] = [
        '#type' => 'managed_file',
        '#title' => t('CSV File'),
        '#upload_location' => 'public://trilliumlincoln/',
        '#upload_validators' => [
          'file_validate_extensions' => ['csv'],
        ],
      ];
      $form['upload_csv']['actions'] = ['#type' => 'actions'];
      $form['upload_csv']['actions']['sumbit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Import Cars'),
        '#submit' => [[$this, 'carsImport']],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Empty implementation of the abstract submit class.
  }

  /**
   * Allow user to directly execute cron, optionally forcing it.
   */
  public function cronRun(array &$form, FormStateInterface &$form_state) {
    $config = $this->configFactory->getEditable('trilliumlincoln_sync.cron');
    $interval = $form_state->getValue('trilliumlincoln_sync_cron_interval');
    $config->set('interval', $interval)->save();

    $cron_reset = $form_state->getValue('cron_reset');
    if (!empty($cron_reset)) {
      \Drupal::state()->set('trilliumlincoln_sync.next_execution', 0);
    }

    // Use a state variable to signal that cron was run manually from this form.
    $this->state->set('trilliumlincoln_sync_show_status_message', TRUE);
    if ($this->cron->run()) {
      drupal_set_message($this->t('Cron ran successfully.'));
    }
    else {
      drupal_set_message($this->t('Cron run failed.'), 'error');
    }
  }

  /**
   * Add the items to the queue when signaled by the form.
   */
  public function addItems(array &$form, FormStateInterface &$form_state) {
    $importers = trilliumlincoln_sync_get_importers();
    $values = $form_state->getValues();

    if (!empty($values['queue'])) {
      $importer_name = $values['queue'];
      $queue_worker = $importers[$importer_name]['queue_worker'];
      $queue = $this->queue->get($queue_worker);
      $queue_name = $form['queue_config']['cron_queue_setup']['queue'][$values['queue']]['#title'];

      $number_items = $queue->numberOfItems();
      if ($number_items == 0) {
        $importer = $importers[$importer_name];

        $trilliumlincoln_sync_source_service = \Drupal::service('trilliumlincoln_sync.source');
        $trilliumlincoln_sync_source_service->unpublishCars();
        $result = $trilliumlincoln_sync_source_service->getCarIds();

        $num_items = 0;
        if (!empty($result)) {
          foreach ($result as $key => $row) {
            $queue->createItem($row);
            $num_items++;
          }
        }
        $args = [
          '%num' => $num_items,
          '%queue' => $queue_name,
        ];
        drupal_set_message($this->t('Added %num items to %queue queue', $args));
      }
      else {
        $args = [
          '%queue' => $queue_name,
        ];
        drupal_set_message($this->t("You can\'t add new items while %queue queue is not empty", $args));
      }

    }

  }

  /**
   * Remove items from the queue when signaled by the form.
   */
  public function removeItems(array &$form, FormStateInterface &$form_state) {
    $importers = trilliumlincoln_sync_get_importers();

    $values = $form_state->getValues();

    if (!empty($values['queue'])) {

      $importer_name = $values['queue'];
      $queue_worker = $importers[$importer_name]['queue_worker'];
      $queue = $this->queue->get($queue_worker);
      $queue_name = $form['queue_config']['cron_queue_setup']['queue'][$values['queue']]['#title'];

      $number_items = $queue->numberOfItems();
      if ($number_items > 0) {
        $queue->deleteQueue();
        $args = [
          '%queue' => $queue_name,
        ];
        drupal_set_message($this->t('Removed all items from %queue', $args));
      }
      else {
        $args = [
          '%queue' => $queue_name,
        ];
        drupal_set_message($this->t("You can\'t remove items from %queue queue because it is empty", $args));
      }
    }
  }

    /**
   * Import All cars manual.
   */
  public function carsImport(array &$form, FormStateInterface &$form_state) {
    $importer_name = 'car';
    $importers = trilliumlincoln_sync_get_importers();
    $importer = $importers[$importer_name];
    $source = $importer['source'];
    $plugin_id = $importer['queue_worker'];

    $trilliumlincoln_sync_source_service = \Drupal::service('trilliumlincoln_sync.source');
    $trilliumlincoln_sync_source_service->unpublishCars();
    $result = $trilliumlincoln_sync_source_service->getCarIds();

    $queue_worker_manager = \Drupal::service('plugin.manager.queue_worker');
    $queue_worker = $queue_worker_manager->createInstance($plugin_id);

    $count = 0;
    if (!empty($result)) {
      foreach ($result as $key => $row) {
        $queue_worker->processItem($row);
        $count++;
      }
    }

    $args = [
      '%count' => $count,
      '%title_label' => 'Cars',
    ];
    drupal_set_message(t('%count %title_label has been synced.', $args));
  }

}
