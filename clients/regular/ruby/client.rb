#!/usr/bin/env ruby

require 'barrister'

trans  = Barrister::HttpTransport.new("http://localhost:3000/todos")
client = Barrister::Client.new(trans)

begin
  result = client.TodoManager.createTodo({ 'title' => 'Call Mom', 'completed' => false })
  puts "create successful with data: #{result}"
  result = client.TodoManager.deleteTodo(result)
  puts "delete successful? #{result}"
rescue Barrister::RpcException => e
  puts "err.code=#{e.code}"
end
