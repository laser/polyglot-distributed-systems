#!/usr/bin/env php

<?php

include_once("./lib/barrister.php");

$barrister = new Barrister();
$client    = $barrister->httpClient("http://localhost:3000/todos");

$batch = $client->startBatch();

$batchTodoManager = $batch->proxy("TodoManager");
$batchTodoManager->createTodo([ "title" => "Call Mom", "completed" => false ]);
$batchTodoManager->createTodo([ "title" => "Call Dad", "completed" => false ]);
$batchTodoManager->createTodo([ "title" => "Wash car", "completed" => false ]);
$batchTodoManager->createTodo([ "title" => "Eat Ham", "completed" => false ]);

$result = $batch->send();
foreach ($result as $i=>$res) {
  if ($res->error) {
    // $res->error is a BarristerRpcException, so you can raise if desired
    echo "err.code=" . $res->error->code . "\n";
  }
  else {
    // result from a successful call
    var_dump($res->result);
  }
}

?>
