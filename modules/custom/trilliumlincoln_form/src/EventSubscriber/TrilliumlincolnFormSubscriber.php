<?php

namespace Drupal\trilliumlincoln_form\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response subscriber to handle AJAX responses.
 */
class TrilliumlincolnFormSubscriber implements EventSubscriberInterface {
/**
   * Renders the ajax commands right before preparing the result.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event, which contains the possible AjaxResponse object.
   */
  public function onResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if ($response instanceof \Drupal\webform\Ajax\WebformSubmissionAjaxResponse || $response instanceof \Drupal\Core\Ajax\AjaxResponse){
      $commands = &$response->getCommands();
      $form_id = \Drupal::request()->get('form_id');
      if ($form_id) {
        $pos = strpos($form_id, 'webform_submission');
        if ($pos === 0) {
          $valid_form = \Drupal::request()->get('valid_form');
          $commands[] = [
            'command' => 'WebformSubmission',
            'selector' => $form_id,
            'result' => $_REQUEST['valid_form']
          ];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::RESPONSE => [['onResponse']]];
  }
}
