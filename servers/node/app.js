var express = require('express')
  , fs = require('fs')
  , http = require('http')
  , path = require('path')
  , barrister = require('barrister')
  , store = require("./store").store
  , idl = JSON.parse(fs.readFileSync("../todo_manager.json").toString());

var app = express();

app.set('port', process.env.PORT || 3000);
app.use(express.logger('dev'));
app.use(express.bodyParser());
app.use(express.methodOverride());
app.use(express.static(__dirname + '/public'));
app.use(app.router);

var server = new barrister.Server(idl);
server.addHandler('TodoManager', {
  'readTodos': function(callback) {
    callback(null, store.getAll());
  },
  'createTodo': function(properties, callback) {
    callback(null, store.save(properties));
  },
  'updateTodo': function(todo, callback) {
    callback(null, store.update(todo.id, todo));
  },
  'deleteTodo': function(todo, callback) {
    callback(null, !!store.delete(todo.id));
  }
});

app.post('/todos', function(req, res) {
  server.handle({}, req.body, function(respJson) {
    res.contentType('application/json');
    res.send(respJson);
  });
});

http.createServer(app).listen(app.get('port'), function(){
  console.log('Express server listening on port ' + app.get('port'));
});
