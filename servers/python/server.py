#!/usr/bin/env python

import barrister
from bottle import run, post, request
from store import Store, RecordNotFound, UserDataInvalid, MaxTodosExceeded
from functools import wraps
import sys

def guard(*exceptions):
  errors = {
    UserDataInvalid: 1000,
    RecordNotFound: 1001,
    MaxTodosExceeded: 1002,
  }

  def decorator(function):
    @wraps(function)
    def wrapper(*args, **kwargs):
      try:
        return function(*args, **kwargs)
      except Exception, e:
        exc_type = sys.exc_info()[0]
        if exc_type in list(exceptions):
          raise barrister.RpcException(errors[exc_type], str(e))

    return wrapper
  return decorator

class TodoManager(object):

  def __init__(self, store):
    self.store = store

  def readTodos(self):
    return self.store.get_all()

  @guard(UserDataInvalid, MaxTodosExceeded)
  def createTodo(self, properties):
    return self.store.save(properties)

  @guard(UserDataInvalid, RecordNotFound)
  def updateTodo(self, todo):
    return self.store.update(todo.id, todo)

  @guard(RecordNotFound)
  def deleteTodo(self, todo_id):
    return self.store.delete(todo.id)

store = Store()
todo_manager = TodoManager(store)

contract = barrister.contract_from_file('../todo_manager.json')
server   = barrister.Server(contract)
server.add_handler('TodoManager', todo_manager)

@post('/todos')
def todos():
  return server.call_json(request.body.read())

run(host='localhost', port=3000)
