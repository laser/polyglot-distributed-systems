package main

import (
  "fmt"
  "github.com/coopernurse/barrister-go"
  "net/http"
  t "./todos"
  s "./store"
)

type TodoManagerImpl struct {
	Store *s.Store
}

func (impl TodoManagerImpl) ReadTodos() ([]t.Todo, error) {
	return impl.Store.GetAll()
}

func (impl TodoManagerImpl) CreateTodo(properties t.TodoProperties) (t.Todo, error) {
	return impl.Store.Save(properties)
}

func (impl TodoManagerImpl) UpdateTodo(todo t.Todo) (t.Todo, error) {
	return impl.Store.Update(todo)
}

func (impl TodoManagerImpl) DeleteTodo(todo t.Todo) (bool, error) {
	return impl.Store.Delete(todo.Id)
}

func main() {
	idl := barrister.MustParseIdlJson([]byte(t.IdlJsonRaw))
	store := s.NewStore()
	mgr := TodoManagerImpl{store}
	svr := t.NewJSONServer(idl, true, mgr)

	http.Handle("/v1/todos", &svr)

	fmt.Println("Starting TodoManager server on localhost:3000")
	err := http.ListenAndServe(":3000", nil)
	if err != nil {
		panic(err)
	}
}
