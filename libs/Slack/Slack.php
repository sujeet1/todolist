<?php

/**
 * Handle Slack Push messages
 *
 * Class Slack
 */
Class Slack {

    /**
     * Push Message to Slack
     *
     * @param $url
     * @param $jsonArr
     */
    function postJson($url, $jsonArr) {
        if(isset($_POST['debug']) && $_POST['debug'] == 'YES') {
            //echo str_replace("\\`", '`',json_encode($jsonArr));
            echo json_encode($jsonArr);
            return;
        }

        $this->cURLRequest($url, json_encode($jsonArr));
    }

    function getUsersList() {
        $strData = file_get_contents(SLACK_WEB_API_URL.SLACK_URL_USER_LIST_METHOD.'?token='.SLACK_OAUTH_TOKEN.'&exclude_archived=1');
        return json_decode($strData, true)['members'];
    }

    function getChannelList() {
        $strData = file_get_contents(SLACK_WEB_API_URL.SLACK_URL_CHANNEL_LIST_METHOD.'?token='.SLACK_OAUTH_TOKEN.'&exclude_archived=1');
        return json_decode($strData, true)['channels'];
    }

    function notifyUser($user, $msg) {
        $jsonArr = array(
            'text' => $msg,
            'channel' => '@'.$user,
            'link_name' => 1,
            'username' => 'TodoList',
            'icon_emoji'=> ':memo:'
        );
        $this->cURLRequest(SLACK_INCOMMING_WEBHOOK, json_encode($jsonArr));
    }

    function cURLRequest($url, $json) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

        return curl_exec ($ch);
    }
}