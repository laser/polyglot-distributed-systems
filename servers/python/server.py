#!/usr/bin/env python

import barrister
from bottle import run, post, request
from store import Store

class TodoManager(object):

  def __init__(self, store):
    self.store = store

  def readTodos(self):
    return self.store.get_all()

  def createTodo(self, properties):
    return self.store.save(properties)

  def updateTodo(self, todo):
    return self.store.update(todo.id, todo)

  def deleteTodo(self, todo_id):
    return self.store.delete(todo.id)

store = Store()
todo_manager = TodoManager(store)

contract = barrister.contract_from_file('../todo_manager.json')
server   = barrister.Server(contract)
server.add_handler('TodoManager', todo_manager)

@post('/todos')
def todos():
  resp_data = server.call_json(request.body.read())
  return resp_data

run(host='localhost', port=3000)
