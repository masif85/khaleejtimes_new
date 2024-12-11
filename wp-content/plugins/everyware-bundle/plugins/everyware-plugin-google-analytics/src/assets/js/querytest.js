//Ajax test ga query for widget
jQuery(function($) {
    var postUrl = oc_ajax.oc_ajax_url || '';

    var selectors = {
        testResultWrapper: '.ga_query__results',
        testButton: '.test_ga_query__btn'
    };

    $(document).on('click', selectors.testButton, function (e) {
      e.preventDefault();
      var query = {
        action: 'test_ga_query'
      }

      $.post(postUrl, query)
          .done(function (data) {
              var parsedData = $.parseJSON(data);

              var result = '<pre>' + JSON.stringify(parsedData.data, null, 2) + '</pre>';

              $(selectors.testResultWrapper).html(result);
              $(selectors.testResultWrapper).show();
          });

      return false;
    });
});