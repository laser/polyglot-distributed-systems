<?php

/*
function barrister_debug($s) {
  $fh = fopen("/tmp/php.log", "a");
  fwrite($fh, $s);
  fwrite($fh, "\n");
  fclose($fh);
}
*/

function bar_json_decode($jsonStr) {
  if ($jsonStr === null || $jsonStr === "null") {
    return null;
  }

  $ok  = true;
  $val = json_decode($jsonStr);
  if (function_exists('json_last_error')) {
    if (json_last_error() !== JSON_ERROR_NONE) {
      $ok = false;
    }
  }
  else if ($val === null) {
    $ok = false;
  }

  if ($ok) {
    return $val;
  }
  else {
    $s = substr($jsonStr, 0, 100);
    throw new BarristerRpcException(-32700, "Unable to decode JSON. First 100 chars: $s");
  }
}

class Barrister {

  function httpClient($url) {
    return new BarristerClient(new BarristerHttpTransport($url));
  }

}

class BarristerRpcException extends Exception {

  function __construct($code, $message, $data=null) {
    parent::__construct($message, $code);
    $this->data = $error["data"];
  }

  function getData() {
    return $this->data;
  }

  function __toString() {
    $s = "BarristerRpcException code=" . $this->getCode() . " message=" . $this->getMessage();
    if (isset($this->data)) {
      $s .= " data=" . $this->data;
    }
    return $s;
  }

}

class BarristerServer {

  function __construct($idlFile) {
    if (file_exists($idlFile)) {
      $fh = fopen($idlFile, 'r');
      $data = fread($fh, filesize($idlFile));
      fclose($fh);

      $this->contract = new BarristerContract(bar_json_decode($data));
      $this->handlers = array();
    }
    else {
      throw new Exception("File not found: $idlFile");
    }
  }

  function addHandler($interfaceName, $handler) {
    $this->contract->checkInterface($interfaceName);
    $this->handlers[$interfaceName] = $handler;
  }

  function handleHTTP() {
    $isCGI = strpos($_ENV['GATEWAY_INTERFACE'], "CGI") === 0;
    if ($isCGI) {
      $reqJson = file_get_contents('php://stdin', NULL, NULL, 0, $_ENV['CONTENT_LENGTH']);
    }
    else {
      $reqJson = file_get_contents('php://input');
    }

    $resp = null;
    $req  = null;
    try {
      $req = bar_json_decode($reqJson);
    }
    catch (BarristerRpcException $e) {
      $resp = $this->errResp($req, $e->getCode(), $e->getMessage());
    }

    if ($resp === null) {
      $resp = $this->handle($req);
    }

    $respJson = json_encode($resp);
    $len      = strlen($respJson);

    if ($isCGI) {
      print "Content-Type: application/json\r\n";
      print "Content-Length: $len\r\n\r\n";
      print $respJson;
    }
    else {
      header("Content-Type: application/json");
      header("Content-Length: $len");
      print $respJson;
    }
  }

  function handle($req) {
    if (is_array($req)) {
      $retList = array();
      foreach ($req as $i=>$r) {
        array_push($retList, $this->handleSingle($r));
      }
      return $retList;
    }
    else {
      return $this->handleSingle($req);
    }
  }

  function handleSingle($req) {
    $method = $req->method;
    if (!$method) {
      return $this->errResp($req, -32600, "No method specified on request");
    }

    if ($method === "barrister-idl") {
      return $this->okResp($req, $this->contract->idl);
    }

    $pos = strpos($method, ".");
    if ($pos > 0) {
      $iface = substr($method, 0, $pos);
      $func  = substr($method, $pos+1);
    }
    else {
      return $this->errResp($req, -32600, "Invalid request method: $method");
    }

    $ifaceInst = $this->contract->getInterface($iface);
    $funcInst  = null;
    if ($ifaceInst) {
      $funcInst = $ifaceInst->getFunction($func);
    }
    if (!$ifaceInst || !$funcInst) {
      return $this->errResp($req, -32601, "Method not found on IDL: $method");
    }

    $params = $req->params;
    if (!$params) {
      $params = array();
    }

    $invalid = $funcInst->validateParams($this->contract, $params);
    if ($invalid !== null) {
      return $this->errResp($req, -32602, $invalid);
    }

    $handler = $this->handlers[$iface];
    if (!$handler) {
      return $this->errResp($req, -32601, "Interface not found: $iface");
    }

    $reflectMethod = null;
    try {
      $reflectMethod = new ReflectionMethod(get_class($handler), $func);
    }
    catch (Exception $e) { }

    if (!$reflectMethod) {
      try {
        $reflectMethod = new ReflectionMethod(get_class($handler), $func . "_");
      }
      catch (Exception $e) { }
    }

    if (!$reflectMethod) {
      return $this->errResp($req, -32601, "Method not found: $method");
    }

    try {
      $result = $reflectMethod->invokeArgs($handler, $params);

      $invalid = $funcInst->validateResult($this->contract, $result);
      if ($invalid !== null) {
        return $this->errResp($req, -32001, $invalid);
      }
      else {
        return $this->okResp($req, $result);
      }
    }
    catch (BarristerRpcException $e) {
      return $this->errResp($req, $e->getCode(), $e->getMessage(), $e->getData());
    }
    catch (Exception $e) {
      return $this->errResp($req, -32000, "Unknown error: " . $e->getMessage());
    }
  }

  function okResp($req, $result) {
    $resp = array("jsonrpc"=>"2.0", "result"=>$result);
    if (isset($req->id)) {
      $resp["id"] = $req->id;
    }
    return $resp;
  }

  function errResp($req, $code, $message, $data=null) {
    $err = array("code"=>$code, "message"=>$message);
    if ($data) {
      $err["data"] = $data;
    }
    $resp = array("jsonrpc"=>"2.0", "error"=>$err);
    if (isset($req->id)) {
      $resp["id"] = $req->id;
    }
    return $resp;
  }

}

class BarristerClient {

  function __construct($trans) {
    $this->trans = $trans;
    $this->loadContract();
  }

  function proxy($interfaceName) {
    $this->contract->checkInterface($interfaceName);
    return new BarristerClientProxy($this, $interfaceName);
  }

  function getMeta() {
    return $this->contract->getMeta();
  }

  function loadContract() {
    $req = array("jsonrpc"=>"2.0", "id"=>"1", "method"=>"barrister-idl");
    $resp = $this->trans->request($req);
    $this->contract = new BarristerContract($resp->result);
  }

  function startBatch() {
    return new BarristerBatch($this);
  }

  function request($method, $params) {
    $req = $this->createRequest($method, $params);
    return $this->trans->request($req);
  }

  function createRequest($method, $params) {
    $req = array("jsonrpc"=>"2.0", "id"=>uniqid("", true), "method"=>$method);
    if ($params && is_array($params) && count($params) > 0) {
      $req["params"] = $params;
    }
    return $req;
  }

}

class BarristerBatch {

  function __construct($client) {
    $this->client = $client;
    $this->requests = array();
    $this->sent = false;
  }

  function getRequest($i) {
    return $this->requests[$i];
  }

  function proxy($interfaceName) {
    $this->client->contract->checkInterface($interfaceName);
    return new BarristerClientProxy($this, $interfaceName);
  }

  function request($method, $params) {
    if ($this->sent) {
      throw new Exception("Batch has already been sent!");
    }
    array_push($this->requests, $req = $this->client->createRequest($method, $params));
  }

  function send() {
    if ($this->sent) {
      throw new Exception("Batch has already been sent!");
    }
    $this->sent = true;

    $results = $this->client->trans->request($this->requests);

    $resultsSorted = array();
    $resultsById   = array();

    foreach ($results as $i=>$res) {
      if (isset($res->id)) {
        $resultsById[$res->id] = $res;
      }
    }

    foreach ($this->requests as $i=>$req) {
      $res = $resultsById[$req["id"]];
      if (!$res) {
        $err = array("code"=>-32603, "message"=>"No result for request id: " . $req["id"]);
        $res = array("jsonrpc"=>"2.0", "id"=>$req["id"], "error"=>$err);
      }
      array_push($resultsSorted, $res);
    }

    return $resultsSorted;
  }

}

class BarristerClientProxy {

  function __construct($client, $interfaceName) {
    $this->client        = $client;
    $this->interfaceName = $interfaceName;
  }

  function __call($name, $args) {
    $method = $this->interfaceName . "." . $name;
    $resp   = $this->client->request($method, $args);
    if (isset($resp->error)) {
      throw new BarristerRpcException($resp->error->code, 
                                      $resp->error->message,
                                      $resp->error->data);
    }
    else {
      return $resp->result;
    }
  }

}

class BarristerHttpTransport {

  function __construct($url) {
    $this->url = $url;
  }

  function request($req) {
    $post_data = json_encode($req);
    //print "request: $post_data\n";
    $headers = array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data));
    $ch = curl_init($this->url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    $result = curl_exec($ch);
    if ($result === false) {
      $err = curl_error($ch);
      throw new BarristerRpcException(-32603, "HTTP POST to " . $this->url . " failed: " . $err);
    }
    else {
      //print "result: $result\n";
      $resp = bar_json_decode($result);
      return $resp;
    }
  }

}

class BarristerContract {

  function __construct($idl) {
    $this->idl        = $idl;
    $this->interfaces = array();
    $this->structs    = array();
    $this->enums      = array();
    $this->meta       = array();

    foreach ($idl as $i=>$val) {
      $type = $val->type;
      if ($type === "interface") {
        $this->interfaces[$val->name] = new BarristerInterface($val);
      }
      elseif ($type === "struct") {
        $this->structs[$val->name] = $val;
      }
      elseif ($type === "enum") {
        $this->enums[$val->name] = $val;
      }
      elseif ($type === "meta") {
        foreach ($val as $k=>$v) {
          if ($k !== "type") {
            $this->meta[$k] = $v;
          }
        }
      }
    }
  }

  function getMeta() {
    return $this->meta;
  }

  function getInterface($name) {
    return $this->interfaces[$name];
  }

  function checkInterface($interfaceName) {
    $iface = $this->getInterface($interfaceName);
    if (!$iface) {
      throw new Exception("No interface found with name: $interfaceName");
    }
  }

  function validate($name, $expected, $isArray, $val) {
    if ($val === null) {
      if ($expected->optional === true) {
        return null;
      }
      else {
        return "$name cannot be null";
      }
    }
    else {
      if ($isArray) {
        if (is_array($val)) {
          $len = count($val);
          for ($i = 0; $i < $len; $i++) {
            $invalid = $this->validate($name."[$i]", $expected, false, $val[$i]);
            if ($invalid !== null) {
              return $invalid;
            }
          }

          return null;
        }
        else {
          return $this->typeErr($name, "[]".$expected->type, $val);
        }
      }
      elseif ($expected->type === "string") {
        if (is_string($val)) {
          return null;
        }
        else {
          return $this->typeErr($name, "string", $val);
        }
      }
      elseif ($expected->type === "bool") {
        if (is_bool($val)) {
          return null;
        }
        else {
          return $this->typeErr($name, "bool", $val);
        }
      }
      elseif ($expected->type === "int") {
        if (is_int($val)) {
          return null;
        }
        else {
          return $this->typeErr($name, "int", $val);
        }

      }
      elseif ($expected->type === "float") {
        if (is_int($val) || is_float($val)) {
          return null;
        }
        else {
          return $this->typeErr($name, "float", $val);
        }
      }
      else {
        $enum = $this->enums[$expected->type];
        if ($enum) {
          if (!is_string($val)) {
            return "$name - enum values must be strings, got: " . gettype($val);
          }

          $len  = count($enum->values);
          for ($i = 0; $i < $len; $i++) {
            if ($enum->values[$i]->value === $val) {
              return null;
            }
          }

          return "$name value '$val' is not in the enum '" . $enum->name . "'";
        }

        $struct = $this->structs[$expected->type];
        if ($struct) {
          if (is_array($val) || is_object($val)) {
            $fields = $this->getAllStructFields(array(), $struct);
            $vars = $val;
            if (is_object($val)) {
              $vars = get_object_vars($val);
            }

            $validFields = array();
            foreach ($fields as $i=>$f) {
              if (array_key_exists($f->name, $vars)) {
                $invalid = $this->validate($name.".".$f->name, $f, $f->is_array, $vars[$f->name]);
                if ($invalid !== null) {
                  return $invalid;
                }
              }
              else if (!$f->optional) {
                return "$name missing required field '". $f->name . "'";
              }

              $validFields[$f->name] = 1;
            }

            foreach ($vars as $k=>$v) {
              if (!array_key_exists($k, $validFields)) {
                return "$name contains invalid field '$k' for type '". $f->name . "'";
              }
            }

            return null;
          }
          else {
            return $this->typeErr($name, $expected->type, $val);
          }
        }

        return "$name - Unknown type: " . $expected->type;
      }
    }
  }

  function getAllStructFields($arr, $struct) {
    foreach ($struct->fields as $i=>$f) {
      array_push($arr, $f);
    }

    if ($struct->extends) {
      $parent = $this->structs[$struct->extends];
      if ($parent) {
        return $this->getAllStructFields($arr, $parent);
      }
    }

    return $arr;
  }

  function typeErr($name, $expType, $val) {
    $actual = gettype($val);
    $s = "$name expects type '$expType' but got type '$actual'";
    if ($actual !== "object") {
      $s .= " for value: $val";
    }
    return $s;
  }

}

class BarristerInterface {

  function __construct($iface) {
    $this->functions = array();
    foreach ($iface->functions as $i=>$func) {
      $this->functions[$func->name] = new BarristerFunction($func);
    }
  }

  function getFunction($name) {
    return $this->functions[$name];
  }

}

class BarristerFunction {

  function __construct($func) {
    $this->returns = $func->returns;
    $this->params  = $func->params;
  }

  function validateParams($contract, $reqParams) {
    $len = count($this->params);
    if ($len != count($reqParams)) {
      return "Param length: " . count($reqParams) . " != expected length: $len";
    }

    for ($i = 0; $i < $len; $i++) {
      $p = $this->params[$i];
      $invalid = $contract->validate($p->name, $p, $p->is_array, $reqParams[$i]);
      if ($invalid !== null) {
        return "Invalid request param[$i]: $invalid";
      }
    }

    return null;
  }

  function validateResult($contract, $result) {
    return $contract->validate("", $this->returns, $this->returns->is_array, $result);
  }

}

?>