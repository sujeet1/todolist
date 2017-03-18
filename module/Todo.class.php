<?php

require_once 'Todo.model.php';
require_once PATH.'/libs/Slack/Slack.php';

/**
 * Class Todo  - To Manage Todo task List via Slack
 */
Class Todo {
    private $model;
    private $slack;
    private $responseUrl;
    private $user;
    private $team;
    private $channel;
    private $task;

    function __construct()
    {
        $this->model = new TodoModel();
        $this->slack = new Slack();
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
            case '/assigntodo' :    //<#no> to <@user>
                $this->assignTodo();
                break;
            case '/pingtodo' :
                $this->pingTodo();
                break;
            default:
                echo Lang::$msg['UNABLE_TO_SERVER_REQUEST'];
        }
    }

    /**
     * Assign todo task to any user with given channel
     */
    function assignTodo() {
        if(preg_match('/^#([1-9][0-9]{0,})\sto\s@([a-z0-9A-Z\_\-]+)$/', trim($this->task), $matches, PREG_OFFSET_CAPTURE)) {
            $todoId = (int) $matches[1][0];
            $userName = $matches[2][0];
            $activeTodo = $this->model->getTodoAssignDetail(array(
                'id' => $todoId,
                'team_id' => $this->team['id'],
                'channel_id' => $this->channel['id'],
                'status' => 'NEW'
            ));
            //print_r($activeTodo); exit;
            if(count($activeTodo)) {
                if($activeTodo[0]['channel_name'] != 'privategroup') {
                    if(empty($activeTodo[0]['user_assigned'])) {
                        $userInfo = $this->getUserInfo($userName);
                        if(!empty($userInfo)) {
                            if(isset($userInfo['channel_ids']) && in_array($this->channel['id'], $userInfo['channel_ids'])) {
                                $this->assignTodoUser($userName, $todoId);
                                $this->sendResponse('msg', "Todo '#$todoId' assigned to '@$userName'");
                                $this->notifyAssignedUser($userName, $activeTodo[0]['task_name'], $activeTodo[0]['channel_name'], $this->user['name']);
                            } else {
                                $this->sendResponse('error', str_replace('##R1##', $userName, Lang::$msg['USER_NOT_IN_CHANNEL']));
                            }
                        } else {
                            $this->sendResponse('error', str_replace('##R1##', $userName, Lang::$msg['USER_NOT_IN_TEAM']));
                        }
                    } else {
                        $this->sendResponse('error', Lang::$msg['TODO_ALREADY_ASSIGNED']);
                    }
                } else {
                    $this->sendResponse('error', Lang::$msg['PRIVATE_GROUP_FEATURE_ERROR']);
                }
            } else {
                $this->sendResponse('error', 'TODO_id `#'.$todoId. '` '. Lang::$msg['TODO_DONT_EXIST']);
            }
        } else {
            $this->sendResponse('error', Lang::$msg['INVALID_ASSIGN_COMMEND']);
        }
    }

    /**
     * Assign Todo task to specific user
     *
     * @param $userName
     * @param $todoId
     */
    function assignTodoUser($userName, $todoId) {
        $this->model->assignUser($userName, $todoId);
    }

    /**
     * Fetch User info for given username from slack team
     *
     * @param $userName
     * @return array
     */
    function getUserInfo($userName) {
        $userList = $this->slack->getUsersList();
        $userInfo = array();
        $userFound = false;
        foreach ($userList as $index => $user) {
            //echo $user['name']; echo " - $userName\n";
            if($userName == $user['name']) {
                $userFound = true;
                $userInfo = $user;
            }
        }
        if($userFound) {
            $channelList = $this->slack->getChannelList();
            foreach ($channelList as $channel) {
                if(in_array($userInfo['id'], $channel['members'])) {
                    $userInfo['channel_ids'][] = $channel['id'];
                    $userInfo['channel_names'][] = $channel['name'];
                }
            }
        }
        return $userInfo;
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
        $todoDetail = $this->model->getTodoAssignDetail(array(
            'task_name' => $this->task,
            'team_id' => $this->team['id'],
            'channel_id' => $this->channel['id'],
            'status' => 'NEW'
        ));
        if(count($todoDetail)) {
            if(empty($todoDetail[0]['user_assigned']) || $todoDetail[0]['user_assigned'] == $this->user['name']) {
                if($this->model->markTodoDone($this->team, $this->channel, $this->task)) {
                    $this->sendResponse('msg', Lang::$msg['REMOVED_TODO'].' "'.$this->task.'"');
                }
            } else {
                $this->sendResponse('error', Lang::$msg['TODO_NOT_ASSIGNED_CANT_MARK_DONE']);
            }
        } else {
            $this->sendResponse('error', 'TODO "'.$this->task.'" '.Lang::$msg['TODO_DONT_EXIST']);
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
            $listMsg = '  *-- TODO LIST --*'."\n";
            foreach ($allTodos as $todo) {
                $userAssigned = $todo['user_assigned'] ? "\t\t -> @".$todo['user_assigned'] : '';
                $listMsg .= '`#'.$todo['id'].'` '.$todo['task_name']." $userAssigned\n";
            }
            $this->sendResponse('list', $listMsg);
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
            'attachments' => array(array(
                'text' => $message,
                'color' => '#228B22',
                'attachment_type' => 'default'
            ))
        );
        switch ($type) {
            case 'error' :
                //$respJson['response_type'] = 'ephemeral';
                $respJson['attachments'][0]['color'] = '#B22222';
                break;
            case 'msg' :
                break;
            case 'list' :
                unset($respJson['attachments']);
                $respJson['text'] = $message;
        }
        $this->slack->postJson($this->responseUrl, $respJson);
    }

    /**
     * Send message to assigned user about their task assignment
     *
     * @param $userName
     * @param $taskName
     * @param $channelName
     */
    function notifyAssignedUser($userName, $taskName, $channelName, $assignedBy) {
        $message = $assignedBy. ' has assigned you Todo task *'.$taskName.'* under channel "'.$channelName.'"';
        $this->slack->notifyUser($userName, $message);
    }

    /**
     * Ping todo command
     */
    function pingTodo() {
        echo " --Pong--  :p";
        //echo $_POST['debug'];
    }

}