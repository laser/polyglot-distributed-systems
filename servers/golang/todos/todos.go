package todos

import (
	"fmt"
	"reflect"
	"github.com/coopernurse/barrister-go"
)

const BarristerVersion string = "0.1.6"
const BarristerChecksum string = "fbe35a4d0e3eb82e9859f49c5b1b848c"
const BarristerDateGenerated int64 = 1399055794683000000

type TodoProperties struct {
	Title	string	`json:"title"`
	Completed	bool	`json:"completed"`
}

type Todo struct {
	TodoProperties
	Id	int64	`json:"id"`
}


type TodoManager interface {
	ReadTodos() ([]Todo, error)
	CreateTodo(todo TodoProperties) (Todo, error)
	UpdateTodo(todo Todo) (Todo, error)
	DeleteTodo(todo Todo) (bool, error)
}

func NewTodoManagerProxy(c barrister.Client) TodoManager { return TodoManagerProxy{c, barrister.MustParseIdlJson([]byte(IdlJsonRaw))} }

type TodoManagerProxy struct {
	client barrister.Client
	idl    *barrister.Idl
}

func (_p TodoManagerProxy) ReadTodos() ([]Todo, error) {
	_res, _err := _p.client.Call("TodoManager.readTodos")
	if _err == nil {
		_retType := _p.idl.Method("TodoManager.readTodos").Returns
		_res, _err = barrister.Convert(_p.idl, &_retType, reflect.TypeOf([]Todo{}), _res, "")
	}
	if _err == nil {
		_cast, _ok := _res.([]Todo)
		if !_ok {
			_t := reflect.TypeOf(_res)
			_msg := fmt.Sprintf("TodoManager.readTodos returned invalid type: %v", _t)
			return []Todo{}, &barrister.JsonRpcError{Code: -32000, Message: _msg}
		}
		return _cast, nil
	}
	return []Todo{}, _err
}

func (_p TodoManagerProxy) CreateTodo(todo TodoProperties) (Todo, error) {
	_res, _err := _p.client.Call("TodoManager.createTodo", todo)
	if _err == nil {
		_retType := _p.idl.Method("TodoManager.createTodo").Returns
		_res, _err = barrister.Convert(_p.idl, &_retType, reflect.TypeOf(Todo{}), _res, "")
	}
	if _err == nil {
		_cast, _ok := _res.(Todo)
		if !_ok {
			_t := reflect.TypeOf(_res)
			_msg := fmt.Sprintf("TodoManager.createTodo returned invalid type: %v", _t)
			return Todo{}, &barrister.JsonRpcError{Code: -32000, Message: _msg}
		}
		return _cast, nil
	}
	return Todo{}, _err
}

func (_p TodoManagerProxy) UpdateTodo(todo Todo) (Todo, error) {
	_res, _err := _p.client.Call("TodoManager.updateTodo", todo)
	if _err == nil {
		_retType := _p.idl.Method("TodoManager.updateTodo").Returns
		_res, _err = barrister.Convert(_p.idl, &_retType, reflect.TypeOf(Todo{}), _res, "")
	}
	if _err == nil {
		_cast, _ok := _res.(Todo)
		if !_ok {
			_t := reflect.TypeOf(_res)
			_msg := fmt.Sprintf("TodoManager.updateTodo returned invalid type: %v", _t)
			return Todo{}, &barrister.JsonRpcError{Code: -32000, Message: _msg}
		}
		return _cast, nil
	}
	return Todo{}, _err
}

func (_p TodoManagerProxy) DeleteTodo(todo Todo) (bool, error) {
	_res, _err := _p.client.Call("TodoManager.deleteTodo", todo)
	if _err == nil {
		_retType := _p.idl.Method("TodoManager.deleteTodo").Returns
		_res, _err = barrister.Convert(_p.idl, &_retType, reflect.TypeOf(false), _res, "")
	}
	if _err == nil {
		_cast, _ok := _res.(bool)
		if !_ok {
			_t := reflect.TypeOf(_res)
			_msg := fmt.Sprintf("TodoManager.deleteTodo returned invalid type: %v", _t)
			return false, &barrister.JsonRpcError{Code: -32000, Message: _msg}
		}
		return _cast, nil
	}
	return false, _err
}

func NewJSONServer(idl *barrister.Idl, forceASCII bool, todomanager TodoManager) barrister.Server {
	return NewServer(idl, &barrister.JsonSerializer{forceASCII}, todomanager)
}

func NewServer(idl *barrister.Idl, ser barrister.Serializer, todomanager TodoManager) barrister.Server {
	_svr := barrister.NewServer(idl, ser)
	_svr.AddHandler("TodoManager", todomanager)
	return _svr
}

var IdlJsonRaw = `[
    {
        "type": "struct",
        "name": "TodoProperties",
        "comment": "",
        "value": "",
        "extends": "",
        "fields": [
            {
                "name": "title",
                "type": "string",
                "optional": false,
                "is_array": false,
                "comment": ""
            },
            {
                "name": "completed",
                "type": "bool",
                "optional": false,
                "is_array": false,
                "comment": ""
            }
        ],
        "values": null,
        "functions": null,
        "barrister_version": "",
        "date_generated": 0,
        "checksum": ""
    },
    {
        "type": "struct",
        "name": "Todo",
        "comment": "",
        "value": "",
        "extends": "TodoProperties",
        "fields": [
            {
                "name": "id",
                "type": "int",
                "optional": false,
                "is_array": false,
                "comment": ""
            }
        ],
        "values": null,
        "functions": null,
        "barrister_version": "",
        "date_generated": 0,
        "checksum": ""
    },
    {
        "type": "interface",
        "name": "TodoManager",
        "comment": "",
        "value": "",
        "extends": "",
        "fields": null,
        "values": null,
        "functions": [
            {
                "name": "readTodos",
                "comment": "returns all Todos",
                "params": [],
                "returns": {
                    "name": "",
                    "type": "Todo",
                    "optional": false,
                    "is_array": true,
                    "comment": ""
                }
            },
            {
                "name": "createTodo",
                "comment": "creates new Todo and returns it with an id",
                "params": [
                    {
                        "name": "todo",
                        "type": "TodoProperties",
                        "optional": false,
                        "is_array": false,
                        "comment": ""
                    }
                ],
                "returns": {
                    "name": "",
                    "type": "Todo",
                    "optional": false,
                    "is_array": false,
                    "comment": ""
                }
            },
            {
                "name": "updateTodo",
                "comment": "updates Todo and returns it",
                "params": [
                    {
                        "name": "todo",
                        "type": "Todo",
                        "optional": false,
                        "is_array": false,
                        "comment": ""
                    }
                ],
                "returns": {
                    "name": "",
                    "type": "Todo",
                    "optional": false,
                    "is_array": false,
                    "comment": ""
                }
            },
            {
                "name": "deleteTodo",
                "comment": "deletes Todo and returns true",
                "params": [
                    {
                        "name": "todo",
                        "type": "Todo",
                        "optional": false,
                        "is_array": false,
                        "comment": ""
                    }
                ],
                "returns": {
                    "name": "",
                    "type": "bool",
                    "optional": false,
                    "is_array": false,
                    "comment": ""
                }
            }
        ],
        "barrister_version": "",
        "date_generated": 0,
        "checksum": ""
    },
    {
        "type": "meta",
        "name": "",
        "comment": "",
        "value": "",
        "extends": "",
        "fields": null,
        "values": null,
        "functions": null,
        "barrister_version": "0.1.6",
        "date_generated": 1399055794683,
        "checksum": "fbe35a4d0e3eb82e9859f49c5b1b848c"
    }
]`
