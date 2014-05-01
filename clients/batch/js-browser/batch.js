var client = Barrister.httpClient("/todos");

client.loadContract(function() {
  var batch = client.startBatch();
  var batchTodoManager = batch.proxy('TodoManager');

  batchTodoManager.createTodo({ 'title' : 'Call Mom', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Call Dad', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Wash car', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Eat Ham', 'completed' : false })

  batch.send(function(err, results) {
    // either r.error or r.result will be set
    for (var i = 0, len = results.length; i < len; i++) {
      if (results[i].error) {
        // results[i].error is a object containing code and message, which you can throw yourself, if desired
        console.log(results[i].error.code);
      }
      else {
        // result from a successful call
        console.log(results[i].result);
      }
    }
  });
});
