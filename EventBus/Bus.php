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
    private $held;
    
    // Очередь текущих задач
    private $taskQueue;
    
    // Хранилище полученых событий
    private $storage;
    
    // Обработчик задач
    private $handle;
    
    // Метод обработчика задач
    private $handleMethod;

    public function __construct(Repository $repository, Storage $storage, Queue $queue)
    {
        $this->repository = $repository;
    
        $this->storage = $storage;
    
        $this->taskQueue = $queue;
    }
    
    // Добавляет новый слой событий
    public function addLayer(string $layer)
    {
        $this->layers[] = $layer;
    }
    
    // Обрабатывает принятое соботые
    public function event(Event $event string $subject, string $event, $data = null)
    {
        // Помещаем событие в хранилище
        $this->storage->register($event);
        
        // Проверяем отложенные задачи
        $this->checkHeld();
        
        // Получаем участников для события
        $members = $this->getAccessMembers($event->subject());
        
        // Подготавливаем задачи
        $this->prepareTasks($members, $event->subject(), $event->eventName());
        
        // Перадаем задачи в обработчик
        $this->runTasks();
    }
    
    // Устанавливает обработчик задач
    public function handle($handle, string $method)
    {
        $this->handle = $handle;
        $this->handleMethod = $method;
        $this->handle->setQueue($this->taskQueue)
    }
    
    // Возвращает участников из допустимых слоёв
    private function getAccessMembers(string $subject)
    {
        // Получаем имя слоя по владельцу события
        $layer = $this->repository->getLayerByName($subject);
        
        // Получаем ключ слоя
        $num = array_search($layer, $this->layers);
        
        // Получаем массив слоёв доступных для события
        $layers = array_slice($this->layers, 0, $num);
        
        // Возвращаем участников из массива слоев
        return $this->repository->getMembersByLayers($layers);
    }
    
    // Подготавливает задачи для события
    private function prepareTasks(array $members, string $subject, string $event)
    {
        $task = null;
        
        // Проходим по всем слушателям
        foreach($members as $member) {
            // Получаем задачу если участник подписан на событие
            $task = $member->getTask($subject, $event);
            
            if (null !== $task) {
                // Если участник подписан на соботие то обрабатываем его
                $this->process($task);
            }
        }
    }
    
    // Обрабатывает задачу для слушателя
    private function process($task)
    {
        // Проверяем удовлетворены ли условия
        if ($this->storage->exists($task->conditions())) {

            // Помещаем задачу в очередь задач на выполнение
            $this->taskQueue->enqueue($task);
            
        } else {
            // Иначе помещаем в отложенные
            $this->held[] = $task;
        }
    }
    
    // Проверяет возможность выполнения отложенных задач
    private function checkHeld()
    {
        foreach($this->held as $key => $task) {
            // Проверяем удовлетворены ли условия
            if ($this->storage->exists($task->conditions())) {

                // Помещаем задачу в массив задач на выполнение
                $this->taskQueue->enqueue($task);
                // Удаляем задачу из отложенных
                unset($this->held[$key]);
            }
        }
    }

    // Передает задачи обработчику
    private function runTasks()
    {
        call_user_func($this->handle, $this->handleMethod);
    }
}
