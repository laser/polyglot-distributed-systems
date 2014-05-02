class BaseRpcException(Exception):
  def __init__(self, value):
    self.value = value

  def __str__(self):
    return repr(self.value)

class UserDataInvalid(BaseRpcException):
  pass

class RecordNotFound(BaseRpcException):
  pass

class MaxTodosExceeded(BaseRpcException):
  pass

class Store(object):

  def __init__(self):
    self.cache = {}
    self.next_id = 0

  def save(self, data):
    self.next_id = self.next_id + 1

    todo = {
      'id' : self.next_id,
      'title' : data['title'],
      'completed' : data['completed']
    }

    self.cache[self.next_id] = todo

    return todo

  def get_all(self):
    return self.cache.values()

  def update(self, id, data):
    if id not in self['cache']:
      raise RecordNotFound("No record found with id" + id)

    todo = {
      'id' : id,
      'title' : data['title'],
      'completed' : data['completed']
    }

    self['cache'][id] = todo

    return todo

  def delete(self, id):
    return (not not self.cache.pop(id))
