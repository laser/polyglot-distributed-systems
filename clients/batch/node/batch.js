#!/usr/bin/env node

var barrister = require('barrister');

var client = barrister.httpClient("http://localhost:3000/v1/todos");

client.loadContract(function(err) {
  var batch = client.startBatch();
  var batchTodoManager = batch.proxy("TodoManager");

  batchTodoManager.createTodo({ 'title' : 'Call Mom', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Call Dad', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Wash car', 'completed' : false })
  batchTodoManager.createTodo({ 'title' : 'Eat Ham', 'completed' : false })

  batch.send(function(err, result) {
    for (var i = 0, len = result.length; i < len; i++) {
      // either result[i].error or result[i].result will be set
      if (result[i].error) {
        console.log("err.code=" + result[i].error.code);
      }
      else {
        // result from a successful call
        console.log(result[i].result);
      }
    }
  });
});
