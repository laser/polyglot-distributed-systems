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
  proxy := NewTodoManagerProxy("http://localhost:3000/v1/todos")

  props := todo_manager.TodoProperties{"Call Dad", false}

  res, err := proxy.CreateTodo(props)

  if err == nil {
    fmt.Println(res)
  } else {
    fmt.Println("ERROR! ", err)
  }

}
