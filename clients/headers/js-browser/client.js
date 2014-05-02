// initialize the API client

function initApp(callback) {
  function transport(req, callback) {
    var settings = {
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(req),
      beforeSend: function(xhr) {
        // set the Authorization header
        xhr.setRequestHeader('Authorization', 'Basic ' + btoa('admin:admin'));
      },
      success: function(data) {
        callback(data);
      }
    };

    jQuery.ajax('/todos', settings);
  };

  var client = Barrister.httpClient(transport);

  client.loadContract(function() {
    var proxy = client.proxy('TodoManager');
    callback(null, proxy);
  });
}

// consume the API client

initApp(function(err, TodoManager) {
  TodoManager.readTodos(function(err, todos) {
    jQuery('#result').text(JSON.stringify(todos));
  })
});
