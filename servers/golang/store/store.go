package store

import t "../todos"

type Store struct {
  todos map[int64]t.Todo
  next_id int64
}

func NewStore() *Store {
  return &Store{make(map[int64]t.Todo),0}
}

func (s Store) GetAll() ([]t.Todo, error) {
  v := make([]t.Todo, 0, len(s.todos))

  for  _, value := range s.todos {
    v = append(v, value)
  }

  return v, nil
}

func (s Store) Save(properties t.TodoProperties) (t.Todo, error) {
  todo := t.Todo{properties,1}
  s.todos[todo.Id] = todo
  return todo, nil
}

func (s Store) Update(todo t.Todo) (t.Todo, error) {
  for cached_id := range s.todos {
    if todo.Id == cached_id {
      s.todos[cached_id] = todo
    }
  }

  return todo, nil
}

func (s Store) Delete(id int64) (bool, error) {
  for cached_id := range s.todos {
    if id == cached_id {
      delete(s.todos,id)
    }
  }

  return true, nil
}
