#!/usr/bin/env python

import barrister
from bottle import run, post, request
from store import Store, RecordNotFound, UserDataInvalid, MaxTodosExceeded
from functools import wraps
import sys
import code

class TodoManager:

  def __init__(self, store):
    self.store = store

  def readTodos(self):
    return self.store.get_all()

  def createTodo(self, properties):
    return self.store.save(properties)

  def updateTodo(self, todo):
    return self.store.update(todo.id, todo)

  def deleteTodo(self, id):
    return self.store.delete(id)

class TodoManagerV1Adapter(TodoManager):

  def deleteTodo(self, todo):
    return TodoManager.deleteTodo(self, todo['id'])

store = Store()

v1_contract = barrister.contract_from_file('../../todo_manager.v1.json')
v1_server   = barrister.Server(v1_contract)
v1_server.add_handler('TodoManager', TodoManagerV1Adapter(store))

v2_contract = barrister.contract_from_file('../../todo_manager.v2.json')
v2_server = barrister.Server(v2_contract)
v2_server.add_handler('TodoManager', TodoManager(store))

@post('/v1/todos')
def todos_v1():
  return v1_server.call_json(request.body.read())

@post('/v2/todos')
def todos_v2():
  return v2_server.call_json(request.body.read())

run(host='localhost', port=3000)
