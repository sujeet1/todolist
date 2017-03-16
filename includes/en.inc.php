<?php

Class Lang {
    static $msg = array();
}

Class En extends Lang {
    function __construct()
    {
        Lang::$msg['UNABLE_TO_SERVER_REQUEST'] = 'Sorry! This application is unable to fulfill your request.';
        Lang::$msg['ALREADY_IN_LIST'] = 'already in un-marked Todo List.';
        Lang::$msg['ADDED_TODO'] = 'Added TODO for ';
        Lang::$msg['REMOVED_TODO'] = 'Removed TODO for ';
        Lang::$msg['NO_TODO'] = 'No TODOs';
        Lang::$msg['MAX_TODO_REACHED'] = 'Cannot add more than '.MAX_TODOS.' todos in list';
        Lang::$msg['TODO_DONT_EXIST'] = 'Given Task do not exits.';
    }
}

$lang = new En();
