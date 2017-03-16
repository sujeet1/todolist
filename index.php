<?php
require_once('includes/global.inc.php');
require_once('module/Todo.class.php');
require_once('libs/Http/Http.class.php');

$todo = new Todo();

if(!empty($_POST) && $todo->isValidRequest()) {
    $todo->serveRequest();
} else {
    echo Lang::$msg['UNABLE_TO_SERVER_REQUEST'];
}