parallel-php/parallel-task [![Build Status](https://travis-ci.org/parallel-php/parallel-task.svg?branch=master)](https://travis-ci.org/parallel-php/parallel-task)
=============

Library for running parallel php tasks in a simple and intuitive way across multiple computers.

What can be done with it:
- Delegate tasks in order to improve application response time. For example, instead of sending an email inline, it can be sent asynchronously.
- Delegate tasks whose result is not required to be immediatly visible or for which a reponse is not needed in the current script. Examples here can range from simple things like logging up to complex application logic that can be processed with delay in order to improve high peak performance.
- Parallel processing. An algorithm that can be parallelized can benefit from this library by starting multiple workers on different machines and agregate the results after all finished.

How to use it:

- Choose a queue implementation and create an instance of it. Existing implementations: Redis and RabbitMQ. Help with more implementations is appreciated.
- Build the worker using queue and start it in a cli environment. You can start multiple workers.
```php
$workerBuilder = new ExecutorWorkerBuilder()
$worker = $workerBuilder->withQueue($queue)->buildWorker();
$worker->work($type);
```
- Build the executor using queue
```php
$executorBuilder = new ExecutorWorkerBuilder()
$executor = $executorBuilder->withQueue($queue)->buildExecutor();
```
- Define a task by implementing the `Task` interface.
- Submit task and get results
```php
$futureResult = $executor->submit($type, MyTask::class, [$param1, $param2]);
//...some other time consuming tasks
$result = $futureResult->getResult();
```

Technically, the library is splitted into three parts:
- Queue module. Handles queue implementation details. From outside, it contains a public interface `Queue` and the input/output entities from it's methods: `InputMessage`, `InputMessageIdentifier` and `OutputMessage`.
  * `putInput` receives an `InputMessage` and store it for processing
  - `submitInput` receives an `InputMessage`, store it for processing and returns and `InputMessageIdentifier` for fetching the result.
  - `run` receives a callable. It should fetch an `InputMessage`, run to callable with it as an input and stores the received `Output`.
  - `getOutput` receives an `InputMessageIdentifier` and returns the `OutputMessage` result for it. It's blocking until the result is available.

- Task module. Uses Queue module adding a layer of `Task` implementation. From the outside, it has two classes with two interfaces: `TaskScheduler` and `TaskRunner` each of them used for scheduling a task for asynchronous run and respectively running tasks asynchronously.

 The interfaces of `TaskScheduler` is:
  * `execute` receives a new Task and the inputs for it. It should be used for methods that don't have a return type.
  - `submit` receives a new Task and the inputs for it and returns a `FutureTaskResult`. Should be used for methods that returns something.

 The interfaces of `TaskRunner` is:
  * `run` runs tasks asynchronously in a loop
  - `runOnce` runs only one task asynchronously

- Executor/Worker facade. It is just a wrapper around Task module to allow easier composition and usage.

 There is a builder that helps creating `Executor` or `Worker` allowing to configure implementation for `Queue` and to change default implemention of `TaskInputMessageTransformer`, `TaskFactory`, `TaskResultMessageTransformer`.
