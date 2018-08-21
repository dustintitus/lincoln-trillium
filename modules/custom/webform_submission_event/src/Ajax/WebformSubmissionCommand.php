<?php

namespace Drupal\webform_submission_event\Ajax;
use Drupal\Core\Ajax\CommandInterface;

class WebformSubmissionCommand implements CommandInterface {
  protected $selector;
  protected $result;

  public function __construct($selector, $result) {
    $this->selector = $selector;
    $this->result = $result;
  }

  public function render() {
    return array(
      'command' => 'webformSubmissionCommand',
      'selector' => $this->selector,
      'result' => $this->result,
    );
  }
}