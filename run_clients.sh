# regular clients
go run ./clients/regular/golang/client.go
sh -c 'cd clients/regular/node; npm start'
sh -c 'cd clients/regular/php; ./client.php'
./clients/regular/python/client.py
./clients/regular/ruby/client.rb

# batch clients
sh -c 'cd clients/batch/node; npm start'
sh -c 'cd clients/batch/php; ./batch.php'
./clients/batch/python/batch.py
./clients/batch/ruby/batch.rb
