class Store

  def initialize
    @cache = {}
    @next_id = 0
  end

  def save(data)
    @next_id += 1
    todo = {
      'id' => @next_id,
      'title' => data['title'],
      'completed' => data['completed']
    }

    @cache[@next_id] = todo

    todo
  end

  def get_all
    @cache.values
  end

  def update(id, data)
    todo = {
      'id' => id,
      'title' => @data['title'],
      'completed' => @data['completed']
    }

    @cache[id] = todo

    todo
  end

  def delete(id)
    !!@cache.delete(id)
  end

end
