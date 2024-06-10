<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Model\TaskTable;
use Application\Model\Task;

class TaskController extends AbstractActionController
{
    private $table;

    public function __construct(TaskTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel([
            'tasks' => $this->table->fetchAll(),
        ]);
    }

    public function addAction()
    {
        if ($this->getRequest()->isPost()) {
            $task = new Task();
            $data = $this->params()->fromPost();
            $data['is_completed'] = isset($data['is_completed']) ? 1 : 0; // Перетворення значення чекбоксу
            $task->exchangeArray($data);
            $this->table->saveTask($task);
            return $this->redirect()->toRoute('task');
        }

        return new ViewModel();
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (0 === $id) {
            return $this->redirect()->toRoute('task', ['action' => 'add']);
        }

        try {
            $task = $this->table->getTask($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('task', ['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data['is_completed'] = isset($data['is_completed']) ? 1 : 0; // Перетворення значення чекбоксу
            $task->exchangeArray($data);
            $this->table->saveTask($task);
            return $this->redirect()->toRoute('task');
        }

        return new ViewModel([
            'task' => $task,
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('task');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deleteTask($id);
            }

            return $this->redirect()->toRoute('task');
        }

        return [
            'id' => $id,
            'task' => $this->table->getTask($id),
        ];
    }
}
