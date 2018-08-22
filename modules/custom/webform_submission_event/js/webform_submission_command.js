(function($, Drupal) {

  if(Drupal.AjaxCommands){

    // Custom Ajax command
    Drupal.AjaxCommands.prototype.webformSubmissionCommand = function(ajax, response, status){
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({
        'event': 'webform_submission',
        'selector': response.selector,
        'result': response.result
      });
    }
  }

})(jQuery, Drupal);