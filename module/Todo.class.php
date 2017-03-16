<?php

require_once 'Todo.model.php';
require_once PATH.'/libs/Slack/Slack.post.php';

/**
 * Class Todo  - To Manage Todo task List via Slack
 */
Class Todo {
    private $model;
    private $slackResponse;
    private $responseUrl;
    private $user;
    private $team;
    private $channel;
    private $task;

    function __construct()
    {
        $this->model = new TodoModel();
        $this->slackResponse = new Slack();
    }

    /**
     * Validate incoming Request and initialize common parameters passed to be used.
     *
     * @return bool
     */
    function isValidRequest() {
        $valid = false;
        if(Http::isRequestMethod(Http::POST) && $_POST['token'] == SLACK_TOKEN) {
            $this->responseUrl = $_POST['response_url'];
            $this->task = $_POST['text'];
            $this->user = array(
                'id' => $_POST['user_id'],
                'name' => $_POST['user_name']
            );
            $this->team = array(
                'id' => $_POST['team_id'],
                'name' => $_POST['team_domain']
            );
            $this->channel = array(
                'id' => $_POST['channel_id'],
                'name' => $_POST['channel_name']
            );
            $valid = true;
        }
        return $valid;
    }

    /**
     * Server Incomming Request
     */
    function serveRequest() {
        switch ($_POST['command']) {
            case '/addtodo' :
                $this->addTodo();
                break;
            case '/marktodo' :
                $this->markTodo();
                break;
            case '/listtodos' :
                $this->listTodo();
                break;
            case '/pingtodo' :
                $this->pingTodo();
                break;
            default:
                echo Lang::$msg['UNABLE_TO_SERVER_REQUEST'];
        }
    }

    /**
     * Add todo task for given team's Channel
     */
    function addTodo() {
        if($this->model->getTodoCount($this->team, $this->channel, $this->task)) {
            $this->sendResponse('error', 'task "'.$this->task.'" '.Lang::$msg['ALREADY_IN_LIST']);
        } elseif ($this->model->getTodoCount($this->team, $this->channel) >= MAX_TODOS) {
            $this->sendResponse('error', Lang::$msg['MAX_TODO_REACHED']);
        } else {
            $this->model->addTodo($this->task, $this->team, $this->channel, $this->user);
            $this->sendResponse('msg', Lang::$msg['ADDED_TODO'].' "'.$this->task.'"');
        }
    }

    /**
     * Mark a todo task as done
     */
    function markTodo() {
        if($this->model->markTodoDone($this->team, $this->channel, $this->task)) {
            $this->sendResponse('msg', Lang::$msg['REMOVED_TODO'].' "'.$this->task.'"');
        } else {
            $this->sendResponse('error', Lang::$msg['TODO_DONT_EXIST']);
        }
    }

    /**
     * List all todo in given Team's Channel
     */
    function listTodo() {
        $allTodos = $this->model->getTodoList($this->team, $this->channel);
        if(!count($allTodos)) {
            $this->sendResponse('error', Lang::$msg['NO_TODO']);
        } else {
            $listMsg = '';
            foreach ($allTodos as $todo) {
                $listMsg .= '- #'.$todo['id'].' '.$todo['task_name']."\n";
            }
            $this->sendResponse('msg', $listMsg);
        }
    }

    /**
     * Send back command response back to Slack
     *
     * @param $type
     * @param $message
     */
    function sendResponse($type, $message) {
        $respJson = array(
            'response_type' => 'in_channel',
            'text' => $message
        );
        if($type == 'error') {
            $respJson['response_type'] = 'ephemeral';
        }
        $this->slackResponse->postJson($this->responseUrl, $respJson);
    }

    /**
     * Ping todo command
     */
    function pingTodo() {
        echo " --Pong--  :p \n";
        echo $_POST['text'];
    }

}