(function($, Drupal) {

  if(Drupal.AjaxCommands){

    // Custom Ajax command
    Drupal.AjaxCommands.prototype.webformSubmissionCommand = function(ajax, response, status){
      $( document ).trigger( "webform_submission", [ response.selector, response.result ] );
    }
  }

})(jQuery, Drupal);