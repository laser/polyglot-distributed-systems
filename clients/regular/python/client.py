#!/usr/bin/env python
import barrister

trans  = barrister.HttpTransport("http://localhost:3000/todos")
client = barrister.Client(trans)

try:
  result = client.TodoManager.createTodo({ "title" : "Call Mom", "completed" : False })
  print result
except barrister.RpcException as e:
  print "err.code=%d" % e.code
