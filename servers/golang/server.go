package main

import "fmt"
import "github.com/coopernurse/barrister-go"
import "net/http"
import t "todo_manager"

type TodoManagerImpl struct{}

func (impl TodoManagerImpl) ReadTodos() ([]t.Todo, error) {
  // load Todos from the data store
  return []t.Todo{}, nil
}

func (impl TodoManagerImpl) CreateTodo(properties t.TodoProperties) (t.Todo, error) {
  // save a Todo to the data store
  return t.Todo{}, nil
}

func (impl TodoManagerImpl) UpdateTodo(todo t.Todo) (t.Todo, error) {
  // load the Todo from data store by id, update it, return it
  return t.Todo{}, nil
}

func (impl TodoManagerImpl) DeleteTodo(a int64) (bool, error) {
  // delete the Todo from store by id and blow up if not found
  return true, nil
}

func main() {
  idl := barrister.MustParseIdlJson([]byte(t.IdlJsonRaw))
  svr := t.NewJSONServer(idl, true, TodoManagerImpl{})
  http.Handle("/", &svr)

  fmt.Println("Starting TodoManager server on localhost:3000")
  err := http.ListenAndServe(":3000", nil)
  if err != nil {
    panic(err)
  }
}
