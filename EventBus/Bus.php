<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Bus
{
    // Репозиторий участников шины событий
    private $repository;
    
    // Массив слоёв
    private $layers;
    
    // Массив отложенных задач
    private $held = [];
    
    // Очередь текущих задач
    private $taskQueue;
    
    // Хранилище полученых событий
    private $storage;
    
    // Обработчик задач
    private $handler;

    public function __construct(Repository $repository, Storage $storage)
    {
        $this->repository = $repository;
    
        $this->storage = $storage;
    
        $this->taskQueue = new \SplQueue;
        $this->taskQueue->setIteratorMode(\SplQueue::IT_MODE_DELETE);
        
        $this->run = false;
    }
    
    // Добавляет новый слой событий
    public function addLayer(string $layer) : void
    {
        $this->layers[] = $layer;
    }
    
    // Обрабатывает принятое соботые
    public function event(Event $event) : void
    {
        // Помещаем событие в хранилище
        $this->storage->register($event);
        
        // Проверяем отложенные задачи
        $this->checkHeld();
        
        // Получаем участников для события
        $members = $this->getAccessMembers($event->subject());
        
        // Подготавливаем задачи
        $this->prepareTasks($event, $members);
        
        if (!$this->run) {
            $this->run();
        }
    }
    
    // Устанавливает обработчик задач
    public function setHandler($handler) : void
    {
        $this->handler = $handler;
    }
    
    // Возвращает участников из допустимых слоёв
    private function getAccessMembers(string $subject) : array
    {
        // Получаем имя слоя по владельцу события
        $layer = $this->repository->getMemberLayer($subject);

        // Получаем ключ слоя
        $num = array_search($layer, $this->layers);
        
        // Получаем массив слоёв доступных для события
        $layers = array_slice($this->layers, 0, $num + 1);
        
        // Возвращаем участников из массива слоев
        return $this->repository->getMembersByLayers($layers);
    }
    
    // Подготавливает задачи для события
    private function prepareTasks(Event $event, array $members) : void
    {
        $task = null;
        
        // Проходим по всем слушателям
        foreach($members as $member) {
            // Получаем задачу если участник подписан на событие
            $task = $member->getTask($event->subject(), $event->eventName());
            
            if (null !== $task) {
                // Если участник подписан на соботие то обрабатываем его
                $this->process($task, $event);
            }
        }
    }
    
    // Обрабатывает задачу для слушателя
    private function process($task, $event) : void
    {
        $task->setData($event->eventData());
    
        // Проверяем выполнены ли условия
        if ($this->storage->exists($task->conditions())) {

            // Помещаем задачу в очередь задач на выполнение
            $this->taskQueue->enqueue($task);
            
        } else {
            // Иначе помещаем в отложенные
            $this->held[] = $task;
        }
    }
    
    // Проверяет возможность выполнения отложенных задач
    private function checkHeld() : void
    {
        foreach($this->held as $key => $task) {
            // Проверяем выполнены ли условия
            if ($this->storage->exists($task->conditions())) {

                // Помещаем задачу в массив задач на выполнение
                $this->taskQueue->enqueue($task);
                // Удаляем задачу из отложенных
                unset($this->held[$key]);
            }
        }
    }

    // Запускает очередь задач
    private function run() : void
    {
        $this->run = true;

        foreach ($this->iterate() as $task) {
            $this->handler->run($task);
        }
        
        $this->run = false;
    }
    
    // Итерирует задачи в очереди
    private function iterate() : \Traversable
    {
        while(!$this->taskQueue->isEmpty()) {
            yield $this->taskQueue->pop();
        }
    }
}
