#!/usr/bin/env node

var barrister = require('barrister');
var client    = barrister.httpClient('http://localhost:3000/v1/todos');

client.loadContract(function(err) {
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
