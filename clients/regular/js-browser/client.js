var client = Barrister.httpClient("/v1/todos");

client.loadContract(function() {
  var proxy      = client.proxy('TodoManager');
  var properties = { 'title' : 'Call Dad', 'completed' : false };

  proxy.createTodo(properties, function(err, result) {
    if (err) {
      console.log(JSON.stringify(err));
    }
    else {
      console.log(JSON.stringify(result));
    }
  });
});
