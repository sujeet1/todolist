<?php

Class Lang {
    static $msg = array();
}

Class En extends Lang {
    function __construct()
    {
        Lang::$msg['UNABLE_TO_SERVER_REQUEST'] = 'Sorry!! Application is unable to fulfill your request.';
        Lang::$msg['ALREADY_IN_LIST'] = 'already in active Todo List.';
        Lang::$msg['ADDED_TODO'] = 'Added TODO for ';
        Lang::$msg['REMOVED_TODO'] = 'Removed TODO for ';
        Lang::$msg['NO_TODO'] = 'No TODOs';
        Lang::$msg['MAX_TODO_REACHED'] = 'Cannot add more than '.MAX_TODOS.' Todos';
        Lang::$msg['TODO_DONT_EXIST'] = ' do not exits in active todos.';
        Lang::$msg['PRIVATE_GROUP_FEATURE_ERROR'] = 'This command is not available for private channel.';
        Lang::$msg['INVALID_ASSIGN_COMMEND'] = 'Invalid todo assign command. Correct Format "#{num} to @{name}"';
        Lang::$msg['USER_NOT_IN_CHANNEL'] = 'User "##R1##" not in this channel';
        Lang::$msg['USER_NOT_IN_TEAM'] = 'User "##R1##" not in team';
        Lang::$msg['TODO_ALREADY_ASSIGNED'] = 'Todo task already assigned';
        Lang::$msg['TODO_NOT_ASSIGNED_CANT_MARK_DONE'] = 'You can\'t mark task assigned to other user';
    }
}

$lang = new En();
