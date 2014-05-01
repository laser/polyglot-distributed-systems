#!/usr/bin/env ruby

require 'barrister'

trans  = Barrister::HttpTransport.new("http://localhost:3000/todos")
client = Barrister::Client.new(trans)

batch = client.start_batch()
batch.TodoManager.createTodo({ 'title' => 'Call Mom', 'completed' => false })
batch.TodoManager.createTodo({ 'title' => 'Call Dad', 'completed' => false })
batch.TodoManager.createTodo({ 'title' => 'Wash car', 'completed' => false })
batch.TodoManager.createTodo({ 'title' => 'Eat Ham', 'completed' => false })

result = batch.send
result.each do |r|
  # either r.error or r.result will be set
  if r.error
    # r.error is a Barrister::RpcException, so you can raise it if desired
    puts "err.code=#{r.error.code}"
  else
    # result from a successful call
    puts r.result
  end
end
