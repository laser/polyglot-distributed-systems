#!/usr/bin/env php

<?php

include_once("./lib/barrister.php");

$barrister = new Barrister();
$client    = $barrister->httpClient("http://localhost:3000/v1/todos");
$proxy     = $client->proxy("TodoManager");

try {
  $result = $proxy->createTodo([ "title" => "Call Mom", "completed" => false ]);
  var_dump($result);
}
catch (BarristerRpcException $e) {
  echo "err.code=" . $e->getCode() . "\n";
}

?>
