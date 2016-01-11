parallel-php/parallel-task [![Build Status](https://travis-ci.org/parallel-php/parallel-task.svg?branch=master)](https://travis-ci.org/parallel-php/parallel-task)
=============

Library for running parallel php tasks in a simple and intuitive way across multiple computers.

How to use it:

- Choose a queue implementation and create an instance of it. Existing implementations: Redis and RabbitMQ. Help with more implementations is appreciated.
- Build the worker using queue and start it in a cli environment. You can start multiple workers.
```php
$worker = Worker::usingQueue($queue);
$worker->work($type);
```
- Build the executor using queue
```php
$executor = Executor::usingQueue($queue);
```
- Define a task by implementing the ```Task``` interface.
- Submit task and get results
```php
$futureResult = $executor->submit($type, MyTask::class, [$param1, $param2]);
//...some other time consuming tasks
$result = $futureResult->getResult();
```

Technically, ... 
