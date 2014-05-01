#!/usr/bin/env ruby
require 'forwardable'
require 'barrister'
require 'sinatra'
require 'json'
require './store.rb'

class TodoManager

  def initialize(store)
    @store = store
  end

  def readTodos
    @store.get_all()
  end

  def createTodo(properties)
    @store.save(properties)
  end

  def updateTodo(todo)
    @store.update(todo.id, todo)
  end

  def deleteTodo(todo_id)
    @store.delete(todo.id)
  end

end

store = Store.new
todo_manager = TodoManager.new store

contract = Barrister::contract_from_file('../todo_manager.json')
server   = Barrister::Server.new(contract)
server.add_handler('TodoManager', todo_manager)

set :port, 3000

post '/todos' do
  request.body.rewind
  resp = server.handle_json(request.body.read)

  status 200
  headers 'Content-Type' => 'application/json'

  resp
end
