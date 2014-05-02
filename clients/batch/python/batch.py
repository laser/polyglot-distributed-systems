#!/usr/bin/env python
import barrister

trans  = barrister.HttpTransport("http://localhost:3000/v1/todos")
client = barrister.Client(trans)

batch = client.start_batch()
batch.TodoManager.createTodo({ "title" : "Call Mom", "completed" : False })
batch.TodoManager.createTodo({ "title" : "Call Dad", "completed" : False })
batch.TodoManager.createTodo({ "title" : "Wash car", "completed" : False })
batch.TodoManager.createTodo({ "title" : "Eat Ham", "completed" : False })

results = batch.send()
for res in results:
    # either res.error or res.result will be set
    if res.error:
        # res.error is a barrister.RpcException, so you can raise it if desired
        print "err.code=%d" % res.error.code
    else:
        # result from a successful call
        print res.result
