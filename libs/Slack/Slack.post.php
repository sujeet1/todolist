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
        echo json_encode($jsonArr); return;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,            $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($jsonArr));
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));

        curl_exec ($ch);
    }

}