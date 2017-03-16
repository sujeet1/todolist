<?php

/**
 * Class TodoModel
 */
Class TodoModel {
    private $dbObj;

    function __construct()
    {
        global $objDB;
        $this->dbObj = $objDB;
        $this->dbObj->tableName = 'todo_list';
    }

    /**
     * Get All todo task for given Team and Channel
     *
     * @param $team
     * @param $channel
     * @param string $task
     * @return mixed
     */
    function getTodoList($team, $channel, $task = '') {
        $search = array(
            'team_id' => $team['id'],
            'channel_id' => $channel['id'],
            'status' => 'NEW'
        );
        if(!empty($task)) {
            $search['task_name'] = $task;
        }
        $this->dbObj->select(array('id','task_name'), $search, array('id' => 'ASC'));
        return $this->dbObj->fetchAllMultiRecords()[0];
    }

    /**
     * Get Number of TODOs in given Team, Channel.
     * If todo task is passed as parameter it check if it already exists.
     *
     * @param $team
     * @param $channel
     * @param string $task
     * @return mixed
     */
    function getTodoCount($team, $channel, $task = '') {
        $search = array(
            'team_id' => $team['id'],
            'channel_id' => $channel['id'],
            'status' => 'NEW'
        );
        if(!empty($task)) {
            $search['task_name'] = $task;
        }
        $this->dbObj->select('COUNT(id) as total', $search);
        return $this->dbObj->fetchAllMultiRecords()[0][0]['total'];
    }

    /**
     * Add Todo task to given Team, Channel
     *
     * @param $task
     * @param $team
     * @param $channel
     * @param $user
     */
    function addTodo($task, $team, $channel, $user) {
        $this->dbObj->insert(array(
            'task_name' => $task,
            'team_id' => $team['id'],
            'team_name' => $team['name'],
            'channel_id' => $channel['id'],
            'channel_name' => $channel['name'],
            'user_id' => $user['id'],
            'user_name' => $user['name']
        ));
    }

    function markTodoDone($team, $channel, $task) {
        $this->dbObj->update(array('status'=>'DONE'),
            array(
                'task_name' => $task,
                'team_id' => $team['id'],
                'channel_id' => $channel['id'],
                'status' => 'NEW'
        ));
        return $this->dbObj->affectedRows();
    }

    function getTodos($fields, $filter)
    {
        $this->dbObj->select($fields, $filter, 'id');
        $allEntities = $this->dbObj->fetchAllMultiRecords()[0];

        return $allEntities;
    }
}