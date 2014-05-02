#!/usr/bin/env ruby

require 'barrister'

trans  = Barrister::HttpTransport.new("http://localhost:3000/v1/todos")
client = Barrister::Client.new(trans)

begin
  todos = client.TodoManager.readTodos()
  puts "all todos: #{todos.to_s}\n"

  todo = client.TodoManager.createTodo({ 'title' => 'Call Mom', 'completed' => false })
  puts "created todo result: #{todo}\n"

  todo['title'] = 'Call Dad'
  todo = client.TodoManager.updateTodo(todo)
  puts "updated todo result: #{todo}\n"

  todo = client.TodoManager.deleteTodo(todo)
  puts "successful deletion of todo? #{todo}"

  todos = client.TodoManager.readTodos()
  puts "all todos: #{todos.to_s}\n"
rescue Barrister::RpcException => e
  puts "err.code=#{e.code}"
end
