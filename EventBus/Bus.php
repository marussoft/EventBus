<?php 

declare(strict_types=1);

namespace Marussia\Components\EventBus;

class Bus
{
    private $repository;
    private $handle;
    private $handleMethod;
    private $layers;
    private $held;
    private $taskQueue;
    private $storage;

    public function __construct()
    {
        $this->storage = new Storage;
        $this->repository = new Repository;
        $this->taskQueue = new Queue;
    }
    
    // Добавляет новый слой событий
    public function addLayer(string $layer)
    {
        $this->layers[] = $layer;
    }
    
    // Устанавливает обработчик событий
    public function handle($handle, $method)
    {
        $this->handle = $handle;
        $this->handleMethod = $method;
    }
    
    // Обрабатывает принятое соботые
    public function event(string $subject, string $event, $data = null)
    {
        // Кладем событие в хранилище
        $this->storage->register($subject, $event, $data);
        
        // Проверяем отложенные задачи
        $this->checkHeld();
        
        // Получаем участников для события
        $members = $this->getEventMembers($subject);
        
        // Подготавливаем задачи
        $this->prepareTasks($members, $subject, $event);
        
        // Перадаем задачи в обработчик
        $this->transferTasks();
    }
    
    // Возвращает участников попадающих под событие
    private function getEventMembers(string $subject)
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
            
            if ($task === null) {
                // Если участник подписан на соботие то обрабатываем его
                $this->process($task);
            }
        }
    }
    
    // Обрабатывает задачу для слушателя
    private function process($task)
    {
        
        // Проверяем удовлетворены ли условия
        if ($this->storage->exists($task->conditions)) {

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
        foreach($this->held as $task) {
            // Проверяем удовлетворены ли условия
            if ($this->storage->exists($task->conditions)) {

                // Кладем задачу в массив задач на выполнение
                $this->taskQueue->enqueue($task);
            }
        }
    }
    
    // Передает массив задач обработчику
    private function transferTasks()
    {
        call_user_func_array($this->handle, $this->handleMethod, $this->taskQueue);
    }

}
