<?php

// 1= debug mode 0= production mode
define('DEBUG', 1);
define('PATH', dirname(dirname(__FILE__)));
define('EXTRA_PATH', '');
define('HTTP_URL', 'http://' . @$_SERVER['SERVER_NAME']. EXTRA_PATH);
define('HTTPS_URL', 'http://' . @$_SERVER['SERVER_NAME']. EXTRA_PATH);

/*
	RDBMS PARAMETERS DEFINED HERE
*/
define('DB_HOST', 'localhost');			// database host   (or ip like 192.168.0.1)
define('DB_USERNAME', 'root'); 			// Database user
define('DB_PASSWORD', 'tolexo');		// Database Password
define('DB_DATABASE', 'todo'); 			// Database name

define('IST_TIME_ADJUSTMENT', '+5 hours +30 minutes');

define('SLACK_INCOMMING_WEBHOOK2', 'xx');
/*
 *   Slack configurations
 */
define('SLACK_TOKEN', 'xx');			// Slack Token for Verification   (or ip like 192.168.0.1)
define('SLACK_INCOMMING_WEBHOOK', 'xx');

define('SLACK_WEB_API_URL', 'xx');
define('SLACK_URL_USER_LIST_METHOD', 'users.list');
define('SLACK_URL_CHANNEL_LIST_METHOD', 'channels.list');
define('SLACK_OAUTH_TOKEN', 'xx');

define('MAX_TODOS', 200);