package main

import (
	t "./todos"
	"fmt"
	"github.com/coopernurse/barrister-go"
	"net/http"
)

type TodoManagerImpl struct {
	todos   map[int64]t.Todo
	next_id int64
}

func (impl TodoManagerImpl) ReadTodos() ([]t.Todo, error) {
	v := make([]t.Todo, 0, len(impl.todos))

	for _, value := range impl.todos {
		v = append(v, value)
	}

	return v, nil
}

func (impl TodoManagerImpl) CreateTodo(properties t.TodoProperties) (t.Todo, error) {
	todo := t.Todo{properties, 1}
	impl.todos[todo.Id] = todo

	return todo, nil
}

func (impl TodoManagerImpl) UpdateTodo(todo t.Todo) (t.Todo, error) {
	for cached_id := range impl.todos {
		if todo.Id == cached_id {
			impl.todos[cached_id] = todo
		}
	}

	return todo, nil
}

func (impl TodoManagerImpl) DeleteTodo(todo t.Todo) (bool, error) {
	for cached_id := range impl.todos {
		if todo.Id == cached_id {
			delete(impl.todos, todo.Id)
		}
	}

	return true, nil
}

func main() {
	idl := barrister.MustParseIdlJson([]byte(t.IdlJsonRaw))
	mgr := TodoManagerImpl{make(map[int64]t.Todo), 0}
	svr := t.NewJSONServer(idl, true, mgr)

	http.Handle("/v1/todos", &svr)

	fmt.Println("Starting TodoManager server on localhost:3000")
	err := http.ListenAndServe(":3000", nil)
	if err != nil {
		panic(err)
	}
}
