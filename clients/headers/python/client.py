#!/usr/bin/env python
import barrister
import urllib2
import sys

password_mgr = urllib2.HTTPPasswordMgrWithDefaultRealm()
password_mgr.add_password(None, 'http://localhost:3000/','admin','admin')
auth_handler = urllib2.HTTPBasicAuthHandler(password_mgr)

trans        = barrister.HttpTransport("http://localhost:3000/v1/todos", handlers=[auth_handler])
client       = barrister.Client(trans)

try:
  result = client.TodoManager.createTodo({ "title" : "Call Mom", "completed" : False })
  print result
except barrister.RpcException as e:
  print "err.code=%d" % e.code
