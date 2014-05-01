package main

import (
  "fmt"
  "github.com/coopernurse/barrister-go"
  "todo_manager"
)

func NewTodoManagerProxy(url string) todo_manager.TodoManager {
  trans := &barrister.HttpTransport{Url: url}
  client := barrister.NewRemoteClient(trans, true)
  return todo_manager.NewTodoManagerProxy(client)
}

func main() {
  proxy := NewTodoManagerProxy("http://localhost:3000/todos")

  c := todo_manager.TodoProperties{"Call Dad", false}

  res, err := proxy.CreateTodo(c)
  if err == nil {
    fmt.Println("Success! Created Todo: ", res)
  } else {
    fmt.Println("ERROR! ", err)
  }

}
